<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Configuração dos Tamanhos de Pizza
        SystemSetting::setSetting(
            'pizza_sizes',
            [
                ['id' => 'pequena', 'name' => 'Pequena', 'slices' => '6 Fatias', 'price' => 36.00, 'is_available' => true],
                ['id' => 'media', 'name' => 'Média (Mais Pedida)', 'slices' => '8 Fatias', 'price' => 43.00, 'is_available' => true],
                ['id' => 'grande', 'name' => 'Grande (Família)', 'slices' => '10 Fatias', 'price' => 52.00, 'is_available' => true],
            ],
            'Tamanhos, quantidade de fatias e preços oficiais das pizzas'
        );

        // 2. Configuração das Bordas Recheadas
        SystemSetting::setSetting(
            'pizza_borders',
            [
                ['id' => 'nenhuma', 'name' => 'Sem Borda Recheada', 'price' => 0.00, 'is_available' => true],
                ['id' => 'catupiry', 'name' => 'Borda Catupiry', 'price' => 8.00, 'is_available' => true],
                ['id' => 'cheddar', 'name' => 'Borda Cheddar', 'price' => 8.00, 'is_available' => true],
                ['id' => 'chocolate', 'name' => 'Borda Chocolate', 'price' => 10.00, 'is_available' => true],
            ],
            'Opções e acréscimos de bordas recheadas de pizza'
        );

        // 3. Configuração do Monte Seu Pastel
        SystemSetting::setSetting(
            'monte_pastel',
            [
                'base_price' => 15.00,
                'charge_from_ingredient' => 5,
                'max_free_ingredients' => 4,
                'extra_ingredient_price' => 3.00,
                'max_total_ingredients' => 10,
                'allow_extra_ingredients' => true,
                'ingredients_salgados' => [
                    ['name' => 'Bacon', 'is_available' => true],
                    ['name' => 'Calabresa', 'is_available' => true],
                    ['name' => 'Carne Moída', 'is_available' => true],
                    ['name' => 'Carne Seca', 'is_available' => true],
                    ['name' => 'Frango', 'is_available' => true],
                    ['name' => 'Catupiry', 'is_available' => true],
                    ['name' => 'Cheddar', 'is_available' => true],
                    ['name' => 'Queijo Coalho', 'is_available' => true],
                    ['name' => 'Queijo de Manteiga', 'is_available' => true],
                    ['name' => 'Muçarela', 'is_available' => true],
                    ['name' => 'Parmesão', 'is_available' => true],
                    ['name' => 'Presunto', 'is_available' => true],
                    ['name' => 'Azeitona', 'is_available' => true],
                    ['name' => 'Dueto', 'is_available' => true],
                    ['name' => 'Milho', 'is_available' => true],
                    ['name' => 'Ovo', 'is_available' => true],
                    ['name' => 'Tomate', 'is_available' => true],
                    ['name' => 'Cebola', 'is_available' => true],
                ],
                'ingredients_doces' => [
                    ['name' => 'Banana', 'is_available' => true],
                    ['name' => 'Canela', 'is_available' => true],
                    ['name' => 'Chocolate ao Leite', 'is_available' => true],
                    ['name' => 'Chocolate Branco', 'is_available' => true],
                    ['name' => 'Doce de Leite', 'is_available' => true],
                    ['name' => 'Goiabada', 'is_available' => true],
                    ['name' => 'Leite Condensado', 'is_available' => true],
                ],
            ],
            'Regras, limites e lista de ingredientes do Monte Seu Pastel'
        );

        // 4. WhatsApp Comercial da AKY Lanches (Recebimento de Pedidos)
        SystemSetting::setSetting(
            'store_whatsapp',
            '(83) 99999-9999',
            'WhatsApp oficial da AKY Lanches para onde os pedidos são enviados'
        );

        // 5. Dados da Empresa AKY Lanches
        SystemSetting::setSetting(
            'store_info',
            [
                'name' => 'AKY LANCHES',
                'slogan' => 'Cardápio Digital & Pedidos',
                'address' => 'Rua Principal, Centro - Teixeira - PB',
                'hours' => 'Todos os dias, das 18h00 às 23h30',
                'document' => '',
            ],
            'Dados gerais e cadastrais da lanchonete'
        );

        // 6. Regra Antifraude de Confirmação de Entrega pelo Motoboy
        SystemSetting::setSetting(
            'require_motoboy_confirmation',
            true,
            'Exigir que o entregador confirme a entrega para poder concluir o pedido'
        );
    }
}

