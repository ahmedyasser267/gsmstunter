/**
 * Admin Orders — list, filters, detail drawer
 */
(function () {
  'use strict';

  const STATUSES = ['new', 'processing', 'shipped', 'delivered', 'cancelled'];
  let currentOrderId = null;

  const $ = (id) => document.getElementById(id);

  function dr(label, val) {
    return `<div class="order-detail-row"><span class="order-detail-row__label">${label}</span><span class="order-detail-row__val">${val || '—'}</span></div>`;
  }

  function applyFilters() {
    const q = ($('orderSearch')?.value || '').toLowerCase().trim();
    const status = $('orderFilterStatus')?.value || '';
    const list = (window.__orders || []).filter((o) => {
      if (status && o.status !== status) return false;
      if (q) {
        const hay = [o.order_reference, o.full_name, o.email, o.phone, o.status].join(' ').toLowerCase();
        if (!hay.includes(q)) return false;
      }
      return true;
    });
    renderOrders(list);
  }

  function renderOrders(list) {
    const body = $('ordersBody');
    if (!body) return;
    body.innerHTML = list.length
      ? list.map((o) => `
        <tr data-clickable data-order-id="${o.id}">
          <td><span class="quote-ref">${o.order_reference}</span></td>
          <td><div class="cell-stack"><strong>${o.full_name || '—'}</strong><span>${o.phone || ''}</span></div></td>
          <td style="color:var(--text-muted)">${o.email || '—'}</td>
          <td><strong>€${Number(o.total_amount || 0).toFixed(2)}</strong></td>
          <td><span class="pill ${o.status}">${o.status}</span></td>
          <td style="color:var(--text-muted);font-size:.78rem">${(o.created_at || '').slice(0, 16)}</td>
          <td><div class="row-actions"><button class="btn secondary xs" data-view-order="${o.id}"><i class="fas fa-eye"></i> View</button></div></td>
        </tr>`).join('')
      : '<tr><td colspan="7"><div class="empty-state"><i class="fas fa-box-open"></i><p>No orders yet.</p></div></td></tr>';

    body.querySelectorAll('[data-view-order]').forEach((btn) => {
      btn.addEventListener('click', (e) => { e.stopPropagation(); viewOrderDetail(Number(btn.dataset.viewOrder)); });
    });
    body.querySelectorAll('[data-order-id]').forEach((row) => {
      row.addEventListener('click', (e) => {
        if (e.target.closest('button')) return;
        viewOrderDetail(Number(row.dataset.orderId));
      });
    });
    if ($('orderCount')) $('orderCount').textContent = list.length + ' order' + (list.length !== 1 ? 's' : '');
  }

  function openDrawer() {
    $('orderDrawerOverlay')?.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeDrawer() {
    $('orderDrawerOverlay')?.classList.remove('open');
    document.body.style.overflow = '';
    currentOrderId = null;
  }

  window.viewOrderDetail = async function (id) {
    currentOrderId = id;
    openDrawer();
    $('orderDrawerTitle').textContent = 'Loading…';
    $('orderDrawerSubtitle').textContent = '';
    $('orderDrawerBody').innerHTML = '<div style="padding:40px;text-align:center;color:var(--text-muted)"><i class="fas fa-spinner fa-spin"></i> Loading order…</div>';
    $('orderDrawerFooter').innerHTML = '';

    try {
      const res = await fetch(`../api/admin/orders.php?id=${id}`).then((x) => x.json());
      if (!res.ok) { toast(res.error || 'Failed to load order', 'error'); closeDrawer(); return; }
      const o = res.order;

      $('orderDrawerTitle').textContent = o.order_reference || '#' + o.id;
      $('orderDrawerSubtitle').textContent = 'Placed ' + (o.created_at || '');

      let addr = '—';
      try {
        const a = JSON.parse(o.shipping_address || '{}');
        const lines = [
          `${a.first_name || ''} ${a.last_name || ''}`.trim(),
          a.address || a.street || '',
          `${a.postcode || a.zip || ''} ${a.city || ''}`.trim(),
          a.country || '',
        ].filter(Boolean);
        addr = lines.join('<br>') || o.full_name || '—';
      } catch { addr = o.shipping_address || '—'; }

      const itemsHtml = (o.items || []).length
        ? `<table class="order-items-table"><thead><tr><th>Product</th><th>SKU</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead><tbody>${(o.items || []).map((it) => `
          <tr><td style="font-weight:500">${it.product_name || '—'}</td><td><code style="font-size:.72rem">${it.sku || '—'}</code></td>
          <td style="text-align:center">${it.quantity}</td><td>€${Number(it.unit_price || 0).toFixed(2)}</td>
          <td style="font-weight:600;color:var(--primary)">€${Number(it.line_total || 0).toFixed(2)}</td></tr>`).join('')}</tbody></table>`
        : '<p style="color:var(--text-muted);font-size:.85rem">No items recorded.</p>';

      const histHtml = (o.history || []).length
        ? (o.history || []).map((h) => `<div class="order-history-item"><div class="order-history-dot"></div><span class="pill ${h.status}" style="font-size:.7rem">${h.status}</span><span style="font-size:.8rem;color:var(--text-muted)">${h.created_at}</span></div>`).join('')
        : '<p style="color:var(--text-muted);font-size:.85rem">No history yet.</p>';

      $('orderDrawerBody').innerHTML = `
        <div class="order-detail-grid">
          <div class="order-detail-section">
            <div class="order-detail-section__title"><i class="fas fa-user"></i> Customer</div>
            ${dr('Name', o.full_name)}${dr('Email', o.email)}${dr('Phone', o.phone)}
          </div>
          <div class="order-detail-section">
            <div class="order-detail-section__title"><i class="fas fa-credit-card"></i> Payment</div>
            ${dr('Method', (o.payment_method || '').replace(/_/g, ' '))}${dr('Shipping', (o.shipping_method || '').replace(/_/g, ' '))}${dr('Shipping cost', '€ ' + Number(o.shipping_cost || 0).toFixed(2))}
          </div>
          <div class="order-detail-section full">
            <div class="order-detail-section__title"><i class="fas fa-location-dot"></i> Address</div>
            <div style="font-size:.875rem;line-height:1.8;color:var(--text-2)">${addr}</div>
          </div>
          <div class="order-detail-section full">
            <div class="order-detail-section__title"><i class="fas fa-list-check"></i> Items (${(o.items || []).length})</div>
            ${itemsHtml}
          </div>
          <div class="order-detail-section">
            <div class="order-detail-section__title"><i class="fas fa-receipt"></i> Totals</div>
            ${dr('Subtotal', '€ ' + Number(o.subtotal || 0).toFixed(2))}${dr('Tax', '€ ' + Number(o.tax_amount || 0).toFixed(2))}
            <div class="order-detail-row" style="font-weight:800;color:var(--primary);margin-top:8px;padding-top:8px;border-top:2px solid var(--border)"><span>TOTAL</span><span>€ ${Number(o.total_amount || 0).toFixed(2)}</span></div>
          </div>
          <div class="order-detail-section">
            <div class="order-detail-section__title"><i class="fas fa-clock-rotate-left"></i> History</div>
            ${histHtml}
          </div>
        </div>
        ${o.notes ? `<div class="review-card" style="margin-top:16px"><div class="review-card__label">Notes</div><div class="review-card__value">${o.notes}</div></div>` : ''}`;

      $('orderDrawerFooter').innerHTML = `
        <div style="display:flex;align-items:center;gap:10px;flex:1">
          <span class="pill ${o.status}">${o.status}</span>
          <select id="orderStatusSelect" class="status-select" style="max-width:160px">
            ${STATUSES.map((s) => `<option value="${s}"${o.status === s ? ' selected' : ''}>${s}</option>`).join('')}
          </select>
        </div>
        <button type="button" class="btn primary" id="orderSaveStatus"><i class="fas fa-floppy-disk"></i> Update Status</button>`;

      $('orderSaveStatus')?.addEventListener('click', () => saveOrderStatus(id));
    } catch (err) {
      toast('Error: ' + err.message, 'error');
      closeDrawer();
    }
  };

  async function saveOrderStatus(id) {
    const status = $('orderStatusSelect')?.value || 'new';
    if (!(await confirmAction(`Update order status to "${status}"?`))) return;
    await fetch('../api/admin/orders.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id, status }) });
    toast('Order status updated', 'success');
    closeDrawer();
    loadAll();
  }

  window.renderOrders = renderOrders;
  window.applyOrderFilters = applyFilters;
  window.saveOrderStatus = saveOrderStatus;

  function initOrdersAdmin() {
    $('orderSearch')?.addEventListener('input', applyFilters);
    $('orderFilterStatus')?.addEventListener('change', applyFilters);
    $('orderDrawerOverlay')?.querySelector('[data-close-drawer]')?.addEventListener('click', closeDrawer);
    $('orderDrawerOverlay')?.addEventListener('click', (e) => { if (e.target.id === 'orderDrawerOverlay') closeDrawer(); });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && $('orderDrawerOverlay')?.classList.contains('open')) closeDrawer();
    });
  }

  window.initOrdersAdmin = initOrdersAdmin;
})();
