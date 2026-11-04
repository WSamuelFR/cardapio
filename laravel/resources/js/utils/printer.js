/**
 * Utilitário de Impressão de Cupom Descritivo para a Cozinha
 * Suporta bobinas térmicas de 80mm, 58mm, A4 e formatos personalizados.
 */

export const defaultPrintSettings = {
  paper_size: '80mm',
  custom_width_mm: 80,
  font_size: '13px',
  margin_mm: 2,
  show_prices: false,
  show_customer_phone: true,
  show_delivery_address: true,
  highlight_notes: true,
  show_cut_line: true,
  header_title: 'AKY LANCHES • COZINHA',
  custom_template: `================================
        {loja}
     *** CUPOM DA COZINHA ***
================================
PEDIDO: #{codigo}
DATA/HORA: {data_hora}
TIPO: {tipo_pedido}
CLIENTE: {cliente}
{telefone}
{endereco}
--------------------------------
ITENS DA COMANDA:
{itens_detalhados}
--------------------------------
{observacoes}
{pagamento}
{troco}
{total}
================================
      {rodape}
================================`,
  footer_text: 'Bom apetite! Preparado com capricho.',
};

export function formatPrice(value) {
  const num = Number(value) || 0;
  return num.toFixed(2).replace('.', ',');
}

export function formatDateTime(isoOrDateStr) {
  if (!isoOrDateStr) {
    const now = new Date();
    return now.toLocaleDateString('pt-BR') + ' ' + now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
  }
  const date = new Date(isoOrDateStr);
  if (isNaN(date.getTime())) return String(isoOrDateStr);
  return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}

export function formatKitchenTicket(order, settings = {}, storeInfo = {}) {
  const cfg = { ...defaultPrintSettings, ...settings };
  const template = cfg.custom_template || defaultPrintSettings.custom_template;

  const storeName = (storeInfo && storeInfo.name) ? storeInfo.name.toUpperCase() : 'AKY LANCHES';
  const orderCode = order.order_number || (order.id ? String(order.id).padStart(4, '0') : '0000');
  const dateTime = formatDateTime(order.created_at);

  const isPickup = order.delivery_address && order.delivery_address.toUpperCase().includes('RETIRADA');
  const orderType = isPickup ? 'RETIRADA NO BALCÃO' : 'ENTREGA (DELIVERY)';

  const customerName = order.customer_name ? order.customer_name.toUpperCase() : 'CLIENTE';
  
  let phoneStr = '';
  if (cfg.show_customer_phone && order.customer_phone) {
    phoneStr = `TEL/WHATS: ${order.customer_phone}`;
  }

  let addressStr = '';
  if (cfg.show_delivery_address) {
    if (isPickup) {
      addressStr = 'LOCAL: RETIRADA NO ESTABELECIMENTO';
    } else {
      const addr = order.delivery_address || 'Endereço não informado';
      const comp = order.delivery_complement ? ` (${order.delivery_complement})` : '';
      addressStr = `ENDEREÇO:\n${addr}${comp}`;
    }
  }

  // Itens detalhados com quantidade, complementos, bordas, adicionais e observações individuais
  let itemsStr = '';
  if (order.items && order.items.length > 0) {
    itemsStr = order.items.map((item, idx) => {
      const qty = item.quantity || 1;
      const name = (item.product_name_snapshot || (item.product ? item.product.name : 'Item')).toUpperCase();
      const priceStr = cfg.show_prices ? ` [R$ ${formatPrice(item.subtotal || item.unit_price * qty)}]` : '';
      let line = `[${qty}x] ${name}${priceStr}`;

      // Detalhes extras do item (sabores de pizza, bordas, adicionais de pastel/sanduíche)
      const details = [];
      if (item.details) {
        if (typeof item.details === 'string') {
          details.push(item.details);
        } else if (typeof item.details === 'object') {
          if (item.details.size) details.push(`Tamanho: ${item.details.size}`);
          if (item.details.border) details.push(`Borda: ${item.details.border}`);
          if (item.details.flavors && Array.isArray(item.details.flavors)) {
            details.push(`Sabores: ${item.details.flavors.join(' / ')}`);
          }
          if (item.details.extras && Array.isArray(item.details.extras)) {
            details.push(`Adicionais: ${item.details.extras.join(', ')}`);
          }
        }
      }

      if (item.notes) {
        details.push(`OBS ITEM: ${item.notes}`);
      }

      if (details.length > 0) {
        line += '\n' + details.map(d => `   • ${d}`).join('\n');
      }

      return line;
    }).join('\n\n');
  } else {
    itemsStr = '(Nenhum item listado)';
  }

  // Observações gerais do pedido em destaque
  let notesStr = '';
  if (order.notes && String(order.notes).trim().length > 0) {
    notesStr = `********************************\nATENÇÃO - OBSERVAÇÃO DO PEDIDO:\n>>> ${order.notes.trim().toUpperCase()} <<<\n********************************`;
  }

  // Pagamento
  let paymentStr = '';
  if (order.payment_method) {
    paymentStr = `FORMA DE PAGTO: ${order.payment_method.toUpperCase()}`;
  }

  // Troco para dinheiro
  let changeStr = '';
  if (order.payment_method === 'dinheiro') {
    if (order.needs_change && order.change_for) {
      const troco = Math.max(0, Number(order.change_for) - Number(order.total || 0));
      changeStr = `TROCO P/ DINHEIRO: R$ ${formatPrice(order.change_for)}\n>>> DEVOLVER DE TROCO: R$ ${formatPrice(troco)} <<<`;
    } else {
      changeStr = 'TROCO: Não precisa de troco';
    }
  }

  // Total
  let totalStr = '';
  if (cfg.show_prices && order.total !== undefined && order.total !== null) {
    totalStr = `VALOR TOTAL: R$ ${formatPrice(order.total)}`;
  }

  const footerStr = cfg.footer_text || defaultPrintSettings.footer_text;

  // Substituição das tags inteligentes
  let rendered = template
    .replace(/{loja}/g, storeName)
    .replace(/{codigo}/g, orderCode)
    .replace(/{data_hora}/g, dateTime)
    .replace(/{tipo_pedido}/g, orderType)
    .replace(/{cliente}/g, customerName)
    .replace(/{telefone}/g, phoneStr)
    .replace(/{endereco}/g, addressStr)
    .replace(/{itens_detalhados}/g, itemsStr)
    .replace(/{observacoes}/g, notesStr)
    .replace(/{pagamento}/g, paymentStr)
    .replace(/{troco}/g, changeStr)
    .replace(/{total}/g, totalStr)
    .replace(/{rodape}/g, footerStr);

  // Limpeza de quebras de linha duplas/triplas resultantes de campos vazios
  rendered = rendered
    .split('\n')
    .filter((line, i, arr) => {
      // remove linhas em branco consecutivas
      if (line.trim() === '' && arr[i - 1] && arr[i - 1].trim() === '') {
        return false;
      }
      return true;
    })
    .join('\n')
    .trim();

  return rendered;
}

export function generateReceiptHtml(order, settings = {}, storeInfo = {}) {
  const cfg = { ...defaultPrintSettings, ...settings };
  const textContent = formatKitchenTicket(order, cfg, storeInfo);

  let paperWidth = '72mm';
  if (cfg.paper_size === '58mm') {
    paperWidth = '48mm';
  } else if (cfg.paper_size === 'a4') {
    paperWidth = '190mm';
  } else if (cfg.paper_size === 'custom' && cfg.custom_width_mm) {
    paperWidth = `${cfg.custom_width_mm}mm`;
  }

  const fontSize = cfg.font_size || '13px';
  const marginMm = (cfg.margin_mm !== undefined && cfg.margin_mm !== null) ? cfg.margin_mm : 2;

  // Escapar HTML no texto mantendo quebras de linha
  const escapedText = textContent
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');

  return `<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Comanda de Cozinha #${order.order_number || order.id || '0000'}</title>
  <style>
    @page {
      size: ${paperWidth} auto;
      margin: ${marginMm}mm;
    }
    @media print {
      html, body {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
      }
      .no-print {
        display: none !important;
      }
    }
    body {
      font-family: 'Courier New', Courier, monospace;
      font-size: ${fontSize};
      line-height: 1.32;
      font-weight: 700;
      color: #000000;
      margin: 0 auto;
      padding: ${marginMm}mm;
      max-width: ${paperWidth};
      box-sizing: border-box;
      background: #ffffff;
      white-space: pre-wrap;
      word-break: break-word;
    }
    .cut-line {
      border-top: 1px dashed #000;
      margin-top: 14px;
      padding-top: 6px;
      text-align: center;
      font-size: 10px;
      font-weight: normal;
    }
  </style>
</head>
<body>
${escapedText}
${cfg.show_cut_line ? '<div class="cut-line">✂ - - - - - CORTE AQUI - - - - - ✂</div>' : ''}
</body>
</html>`;
}

/**
 * Impressão silenciosa e discreta em iframe oculto.
 * Não abre popups bloqueáveis nem abas em branco.
 */
export function printThermalTicket(order, settings = {}, storeInfo = {}) {
  return new Promise((resolve) => {
    const htmlContent = generateReceiptHtml(order, settings, storeInfo);

    // Cria iframe invisível
    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.top = '-9999px';
    iframe.style.left = '-9999px';
    iframe.style.width = '1px';
    iframe.style.height = '1px';
    iframe.style.border = 'none';
    iframe.style.opacity = '0';
    document.body.appendChild(iframe);

    const doc = iframe.contentWindow || iframe.contentDocument;
    const iframeDoc = doc.document || doc;

    iframeDoc.open();
    iframeDoc.write(htmlContent);
    iframeDoc.close();

    const cleanup = () => {
      setTimeout(() => {
        if (iframe.parentNode) {
          iframe.parentNode.removeChild(iframe);
        }
        resolve();
      }, 1000);
    };

    // Espera fontes e estilos carregarem no iframe antes de chamar a impressão
    setTimeout(() => {
      try {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
      } catch (e) {
        console.error('Falha ao acionar impressão:', e);
      } finally {
        cleanup();
      }
    }, 250);
  });
}

