/**
 * Admin Customers — list, search, detail drawer
 */
(function () {
  'use strict';

  const $ = (id) => document.getElementById(id);

  function applyFilters() {
    const q = ($('customerSearch')?.value || '').toLowerCase().trim();
    const list = (window.__customers || []).filter((c) => {
      if (!q) return true;
      const hay = [c.full_name, c.email, c.phone].join(' ').toLowerCase();
      return hay.includes(q);
    });
    renderCustomers(list);
  }

  function renderCustomers(list) {
    const body = $('customersBody');
    if (!body) return;
    body.innerHTML = list.length
      ? list.map((cu) => `
        <tr data-clickable data-customer-id="${cu.id}">
          <td><div class="customer-cell"><div class="customer-avatar">${(cu.full_name || '?')[0].toUpperCase()}</div><div class="cell-stack"><strong>${cu.full_name || '—'}</strong><span>${cu.email || ''}</span></div></div></td>
          <td>${cu.phone || '—'}</td>
          <td><strong>${cu.orders_count || 0}</strong></td>
          <td style="color:var(--primary);font-weight:600">€${Number(cu.lifetime_value || 0).toFixed(2)}</td>
          <td style="color:var(--text-muted);font-size:.78rem">${(cu.created_at || '').slice(0, 10)}</td>
          <td><div class="row-actions"><button class="btn secondary xs" data-view-customer="${cu.id}"><i class="fas fa-eye"></i> View</button></div></td>
        </tr>`).join('')
      : '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-user-plus"></i><p>No customers yet.</p></div></td></tr>';

    body.querySelectorAll('[data-view-customer]').forEach((btn) => {
      btn.addEventListener('click', (e) => { e.stopPropagation(); openCustomerDetail(Number(btn.dataset.viewCustomer)); });
    });
    body.querySelectorAll('[data-customer-id]').forEach((row) => {
      row.addEventListener('click', (e) => {
        if (e.target.closest('button')) return;
        openCustomerDetail(Number(row.dataset.customerId));
      });
    });
    if ($('customerCount')) $('customerCount').textContent = list.length + ' customer' + (list.length !== 1 ? 's' : '');
  }

  function openCustomerDetail(id) {
    const cu = (window.__customers || []).find((x) => Number(x.id) === Number(id));
    if (!cu) return;

    $('customerDrawerTitle').textContent = cu.full_name || 'Customer';
    $('customerDrawerSubtitle').textContent = cu.email || '';
    $('customerDrawerBody').innerHTML = `
      <div class="review-grid">
        <div class="review-card"><div class="review-card__label">Contact</div><div class="review-card__value">
          <strong>${cu.full_name || '—'}</strong><br>
          <a href="mailto:${cu.email}" style="color:var(--primary)">${cu.email || '—'}</a><br>
          ${cu.phone || '—'}
        </div></div>
        <div class="review-card"><div class="review-card__label">Activity</div><div class="review-card__value">
          Orders: <strong>${cu.orders_count || 0}</strong><br>
          Lifetime value: <strong>€${Number(cu.lifetime_value || 0).toFixed(2)}</strong><br>
          Joined: ${(cu.created_at || '').slice(0, 10)}
        </div></div>
      </div>`;
    $('customerDrawerOverlay')?.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeDrawer() {
    $('customerDrawerOverlay')?.classList.remove('open');
    document.body.style.overflow = '';
  }

  window.renderCustomers = renderCustomers;
  window.applyCustomerFilters = applyFilters;

  function initCustomersAdmin() {
    $('customerSearch')?.addEventListener('input', applyFilters);
    $('customerDrawerOverlay')?.querySelector('[data-close-drawer]')?.addEventListener('click', closeDrawer);
    $('customerDrawerOverlay')?.addEventListener('click', (e) => { if (e.target.id === 'customerDrawerOverlay') closeDrawer(); });
  }

  window.initCustomersAdmin = initCustomersAdmin;
})();
