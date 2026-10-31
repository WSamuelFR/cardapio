/**
 * Utilitários de Máscaras e Sanitização de Entradas
 * AKY Lanches
 */

/**
 * Sanitiza o nome: apenas letras e espaços, convertido para CAIXA ALTA.
 * Bloqueia números, símbolos e pontuações.
 *
 * @param {string} value
 * @returns {string}
 */
export function maskName(value) {
  if (!value) return '';
  // Remove qualquer caractere que não seja letra (\p{L}) ou espaço (\s)
  const cleaned = value.replace(/[^\p{L}\s]/gu, '');
  return cleaned.toUpperCase();
}

/**
 * Máscara dinâmica para Telefone:
 * (99) 9999-9999 ou (99) 99999-9999
 *
 * @param {string} value
 * @returns {string}
 */
export function maskPhone(value) {
  if (!value) return '';
  const digits = value.replace(/\D/g, '').slice(0, 11);

  if (digits.length === 0) return '';
  if (digits.length <= 2) {
    return `(${digits}`;
  }
  if (digits.length <= 6) {
    return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
  }
  if (digits.length <= 10) {
    return `(${digits.slice(0, 2)}) ${digits.slice(2, 6)}-${digits.slice(6)}`;
  }
  return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7, 11)}`;
}

/**
 * Máscara dinâmica para Documento (CPF ou CNPJ):
 * CPF: 999.999.999-99 (até 11 dígitos)
 * CNPJ: 99.999.999/9999-99 (até 14 dígitos)
 *
 * @param {string} value
 * @returns {string}
 */
export function maskDocument(value) {
  if (!value) return '';
  const digits = value.replace(/\D/g, '').slice(0, 14);

  if (digits.length === 0) return '';

  // CPF (até 11 dígitos)
  if (digits.length <= 11) {
    if (digits.length <= 3) return digits;
    if (digits.length <= 6) return `${digits.slice(0, 3)}.${digits.slice(3)}`;
    if (digits.length <= 9) return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6)}`;
    return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6, 9)}-${digits.slice(9)}`;
  }

  // CNPJ (12 a 14 dígitos)
  if (digits.length <= 12) {
    return `${digits.slice(0, 2)}.${digits.slice(2, 5)}.${digits.slice(5, 8)}/${digits.slice(8)}`;
  }
  return `${digits.slice(0, 2)}.${digits.slice(2, 5)}.${digits.slice(5, 8)}/${digits.slice(8, 12)}-${digits.slice(12, 14)}`;
}

/**
 * Máscara para CEP: 99999-999
 *
 * @param {string} value
 * @returns {string}
 */
export function maskCep(value) {
  if (!value) return '';
  const digits = value.replace(/\D/g, '').slice(0, 8);
  if (digits.length === 0) return '';
  if (digits.length <= 5) return digits;
  return `${digits.slice(0, 5)}-${digits.slice(5)}`;
}

/**
 * Validação Matemática Oficial do CPF (Módulo 11)
 * Verifica os dígitos verificadores e descarta CPFs repetidos
 *
 * @param {string} value
 * @returns {boolean}
 */
export function isValidCpf(value) {
  if (!value) return false;
  const digits = value.replace(/\D/g, '');

  // Deve possuir exatamente 11 dígitos
  if (digits.length !== 11) return false;

  // Rejeita sequências de dígitos repetidos (ex: 000.000.000-00, 111.111.111-11, etc.)
  if (/^(\d)\1{10}$/.test(digits)) return false;

  // Cálculo do 1º Dígito Verificador
  let sum = 0;
  for (let i = 0; i < 9; i++) {
    sum += parseInt(digits.charAt(i), 10) * (10 - i);
  }
  let rest = (sum * 10) % 11;
  let checkDigit1 = (rest === 10 || rest === 11) ? 0 : rest;

  if (checkDigit1 !== parseInt(digits.charAt(9), 10)) {
    return false;
  }

  // Cálculo do 2º Dígito Verificador
  sum = 0;
  for (let i = 0; i < 10; i++) {
    sum += parseInt(digits.charAt(i), 10) * (11 - i);
  }
  rest = (sum * 10) % 11;
  let checkDigit2 = (rest === 10 || rest === 11) ? 0 : rest;

  return checkDigit2 === parseInt(digits.charAt(10), 10);
}

/**
 * Retorna apenas os dígitos de um texto
 *
 * @param {string} value
 * @returns {string}
 */
export function onlyDigits(value) {
  if (!value) return '';
  return value.replace(/\D/g, '');
}

/**
 * Aplica máscara diretamente no elemento DOM do input e sincroniza com o estado do Vue
 * Força o input HTML a refletir o valor sanitizado imediatamente, evitando que caracteres
 * inválidos permaneçam visíveis no navegador quando o estado reativo do Vue não sofre alteração.
 *
 * @param {Event} event Evento @input
 * @param {Function} maskFn Função de formatação (maskName, maskPhone, maskDocument, maskCep)
 * @param {Function} setter Callback para atualizar o estado reativo
 * @returns {string} Valor formatado
 */
export function applyMask(event, maskFn, setter) {
  if (!event || !event.target) return '';
  const input = event.target;
  const rawValue = input.value || '';
  const maskedValue = maskFn(rawValue);

  // Força atualização imediata no elemento DOM do navegador
  input.value = maskedValue;

  // Atualiza o estado reativo do Vue
  if (typeof setter === 'function') {
    setter(maskedValue);
  }

  // Reforça após o ciclo do Vue para garantir consistência visual no navegador
  setTimeout(() => {
    if (input && input.value !== maskedValue) {
      input.value = maskedValue;
    }
  }, 0);

  return maskedValue;
}
