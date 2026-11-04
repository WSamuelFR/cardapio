# Documentação de Infraestrutura Docker e Deploy — Aky Lanches (Cardápio Digital)

Este documento descreve detalhadamente a arquitetura de containerização, integração de banco de dados, proxy reverso Nginx com SSL, alterações no código da aplicação Laravel e comandos operacionais.

---

## 1. Visão Geral da Arquitetura

A aplicação opera em ambiente Docker conteinerizado, integrada à infraestrutura existente no servidor:

```
[ Usuário / Navegador ]
          │ (HTTPS: 443 / HTTP: 80)
          ▼
   ┌───────────────────────────────────────────────┐
   │ infra-nginx (Nginx 1.27 - Proxy Reverso Host)  │
   │ - Terminação SSL (Let's Encrypt)              │
   │ - Desafio ACME webroot                        │
   │ - Redirecionamento 80 -> 443                  │
   └───────┬───────────────────────────────────────┘
           │ (Rede Docker: infra-net)
           ▼ (Porta interna 8080)
   ┌───────────────────────────────────────────────┐
   │ cardapio-app (PHP 8.4 FPM + Nginx interno)     │
   │ - Laravel 13 Framework                        │
   │ - Assets compilados (Vite / Vue 3 / Bootstrap)│
   │ - Volume persistente para uploads de fotos    │
   └───────┬───────────────────────────────────────┘
           │ (Rede Docker: infra-net)
           ▼ (Porta 3306)
   ┌───────────────────────────────────────────────┐
   │ infra-mysql (MySQL 8.0)                       │
   │ - Database: cardapio_db                       │
   └───────────────────────────────────────────────┘
```

---

## 2. O que é Cada Componente

| Componente | Nome / Localização | Função |
| :--- | :--- | :--- |
| **Aplicação Web** | Container `cardapio-app` | Roda a aplicação Laravel 13 com PHP 8.4 e Nginx interno de alta performance (`serversideup/php:8.4-fpm-nginx`). |
| **Banco de Dados** | Container `infra-mysql` | Servidor MySQL 8.0 compartilhado da infraestrutura, onde foi provisionado o banco `cardapio_db`. |
| **Rede Interna** | `infra-net` (bridge externa) | Rede Docker isolada que permite comunicação por nome de serviço (`cardapio-app`, `infra-mysql`, `infra-redis`, `infra-nginx`) sem expor portas de banco para a internet. |
| **Proxy Reverso** | Container `infra-nginx` | Recebe as requisições públicas nas portas 80 e 443, cuida da criptografia TLS/HTTPS e repassa internamente para o `cardapio-app`. |
| **Certificados SSL** | `/srv/infra/nginx/certs` | Armazena os certificados emitidos pelo Let's Encrypt para o domínio `akylanches.freedevs.tech`. |
| **Desafio ACME** | `/srv/infra/nginx/conf.d/acme` | Diretório webroot onde o Certbot grava tokens temporários para validação de domínio. |
| **Volume de Armazenamento** | `cardapio_storage` | Volume persistente Docker mapeado para `/var/www/html/storage/app/public` garantindo que imagens enviadas pelos administradores não sejam perdidas ao atualizar o container. |

---

## 3. Arquivos Criados e Suas Funções

### 3.1. `Dockerfile` (Multi-stage Build)
* **Local:** `cardapio/laravel/Dockerfile`
* **Estágio 1 (`frontend`):** Utiliza imagem leve `node:20-alpine`, executa `npm ci` e `npm run build` (compilando os componentes Vue 3, Bootstrap 5 e Tailwind/CSS via Vite para `public/build`).
* **Estágio 2 (`runtime`):** Utiliza a imagem `serversideup/php:8.4-fpm-nginx`, projetada e endurecida para Laravel:
  * Instala dependências do Composer (`composer install --no-dev --optimize-autoloader --no-scripts`).
  * Copia os artefatos compilados do frontend do Estágio 1.
  * Habilita automações integradas (`AUTORUN_ENABLED=true`, `AUTORUN_LARAVEL_MIGRATION=true`, `AUTORUN_LARAVEL_MIGRATION_SEED=true`, `AUTORUN_LARAVEL_STORAGE_LINK=true`).
  * Ativa o OPcache para alta performance em produção (`PHP_OPCACHE_ENABLE=1`).

### 3.2. `docker-compose.yml`
* **Locais:** `cardapio/docker-compose.yml` e `cardapio/laravel/docker-compose.yml`
* Define o serviço `cardapio-app`, carregando variáveis de `.env`.
* Conecta o container à rede `infra-net`.
* Mapeia a porta de fallback local `8008:8080` no host.
* Mapeia o volume de dados `cardapio_storage`.

### 3.3. Configuração do Nginx Host
* **Local:** `/srv/infra/nginx/conf.d/cardapio.conf`
* **Redirecionamento HTTP:** Escuta na porta 80 e redireciona todo o tráfego para `https://$host$request_uri`, exceto `/.well-known/acme-challenge/` (para renovação automática do Certbot).
* **Servidor HTTPS:** Escuta na porta 443 com SSL ativado:
  * Certificados: `/etc/nginx/certs/live/akylanches.freedevs.tech/fullchain.pem` e `privkey.pem`.
  * Resolver dinâmico do Docker: `resolver 127.0.0.11 valid=30s;` (evita que o Nginx quebre caso o container da aplicação seja reiniciado).
  * Encaminhamento correto dos cabeçalhos: `X-Forwarded-Proto https`, `X-Forwarded-Port 443`, `X-Forwarded-Host $host`.

---

## 4. Alterações no Código do Laravel e Justificativa Técnica

Ao ativar o HTTPS com terminação SSL no Nginx, a conexão entre o `infra-nginx` e o `cardapio-app` ocorre internamente via HTTP (na porta 8080). Para que o Laravel entenda que o cliente final está em HTTPS e evite o erro de **Mixed Content** (bloqueio de scripts e CSS no navegador), foram feitas duas alterações cruciais:

### 4.1. `bootstrap/app.php` — Confiança nos Proxies Reversos

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->trustProxies(at: '*'); // <-- ADICIONADO
    $middleware->validateCsrfTokens(except: [
        'api/*',
    ]);
})
```

* **Por que foi necessário:** Por padrão, o Laravel ignora os cabeçalhos `X-Forwarded-*` por motivos de segurança contra spoofing de IP/Protocolo. Ao definir `trustProxies(at: '*')`, instruímos o framework a confiar nos cabeçalhos enviados pelo nosso proxy reverso (`infra-nginx`), reconhecendo que `$request->isSecure()` é verdadeiro.

### 4.2. `app/Providers/AppServiceProvider.php` — Forçamento do Esquema HTTPS

```php
use Illuminate\Support\Facades\URL;

public function boot(): void
{
    if (config('app.env') === 'production' || str_starts_with(config('app.url'), 'https://')) {
        URL::forceScheme('https'); // <-- ADICIONADO
    }
}
```

* **Por que foi necessário:** Garante que qualquer link gerado internamente pelo framework — incluindo rotas de redirecionamento, URLs da API e tags de assets injetadas pela diretiva `@vite(['...'])` — sejam gerados estritamente com o protocolo `https://`. Isso impede que o navegador bloqueie os arquivos JavaScript do Vue por carregamento inseguro.

---

## 5. Comandos Operacionais Úteis

### Gerenciamento dos Containers

* **Iniciar / Reconstruir a aplicação:**
  ```bash
  cd /home/jadson/projetos/cardapio
  docker compose up -d --build
  ```

* **Verificar status dos containers:**
  ```bash
  docker ps | grep -E 'cardapio|mysql|nginx'
  ```

* **Visualizar logs em tempo real da aplicação:**
  ```bash
  docker logs cardapio-app -f
  ```

* **Reiniciar apenas a aplicação:**
  ```bash
  docker restart cardapio-app
  ```

* **Parar a aplicação:**
  ```bash
  docker compose down
  ```

---

### Execução de Comandos Artisan no Container

Como o PHP não precisa estar instalado no host, qualquer comando do Laravel é executado diretamente dentro do container:

* **Limpar / Recriar caches:**
  ```bash
  docker exec -it cardapio-app php artisan optimize:clear
  docker exec -it cardapio-app php artisan optimize
  ```

* **Executar novas migrations:**
  ```bash
  docker exec -it cardapio-app php artisan migrate --force
  ```

* **Rodar seeders manualmente:**
  ```bash
  docker exec -it cardapio-app php artisan db:seed --force
  ```

* **Abrir Tinker:**
  ```bash
  docker exec -it cardapio-app php artisan tinker
  ```

---

### Gestão do Nginx e Certificados SSL

* **Testar sintaxe das configurações do Nginx:**
  ```bash
  docker exec -it infra-nginx nginx -t
  ```

* **Recarregar o Nginx sem derrubar conexões ativas:**
  ```bash
  docker exec -it infra-nginx nginx -s reload
  ```

* **Renovação de Certificados SSL (Certbot):**
  O Certbot renova automaticamente via cron/timer do sistema. Para testar a renovação:
  ```bash
  sudo certbot renew --dry-run
  ```
