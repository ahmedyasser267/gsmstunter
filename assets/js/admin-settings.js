/**
 * Admin Settings — Pricing & Layout tabs
 */
(function () {
  'use strict';

  const $ = (id) => document.getElementById(id);

  function initSettingsTabs(containerId, panelPrefix) {
    const container = $(containerId);
    if (!container) return;
    container.querySelectorAll('.settings-tab').forEach((tab) => {
      tab.addEventListener('click', () => {
        container.querySelectorAll('.settings-tab').forEach((t) => t.classList.toggle('active', t === tab));
        document.querySelectorAll(`[data-settings-group="${panelPrefix}"]`).forEach((p) => {
          p.classList.toggle('active', p.dataset.panel === tab.dataset.panel);
        });
      });
    });
  }

  function initPricingAdmin() {
    initSettingsTabs('pricingSettingsTabs', 'pricing');

    $('saveCalc')?.addEventListener('click', async () => {
      if (!(await confirmAction('Save sell pricing settings?'))) return;
      const res = await fetch('../api/admin/settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          section: 'calculation',
          min_price: Number($('minPrice')?.value || 30),
          global_reduction_percent: Number($('globalReductionPercent')?.value || 0),
          rounding_rule: $('rounding')?.value || 'nearest_5',
          currency: $('currency')?.value || 'EUR',
        }),
      }).then((x) => x.json());
      if (res.ok) toast('Sell settings saved', 'success');
      else toast(res.error || 'Save failed', 'error');
    });

    $('saveTrade')?.addEventListener('click', async () => {
      if (!(await confirmAction('Save trade settings?'))) return;
      const res = await fetch('../api/admin/settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          section: 'trade',
          trade_bonus_percent: Number($('tradeBonusPercent')?.value || 0),
          exchange_bonus_value: Number($('exchangeBonusValue')?.value || 0),
          min_trade_price: Number($('minTradePrice')?.value || 20),
        }),
      }).then((x) => x.json());
      if (res.ok) toast('Trade settings saved', 'success');
      else toast(res.error || 'Save failed', 'error');
    });
  }

  function initLayoutAdmin() {
    initSettingsTabs('layoutSettingsTabs', 'layout');

    $('saveViewSettings')?.addEventListener('click', async () => {
      if (!(await confirmAction('Save storefront view settings?'))) return;
      const res = await fetch('../api/admin/view_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          default_view_mode: $('defaultViewMode')?.value || 'grid',
          items_per_page: Number($('itemsPerPage')?.value || 12),
          show_filters: Number($('showFilters')?.value || 1),
          show_sort: Number($('showSort')?.value || 1),
        }),
      }).then((x) => x.json());
      if (res.ok) toast('View settings saved', 'success');
      else toast(res.error || 'Save failed', 'error');
    });
  }

  window.renderSections = function (items) {
    const body = $('sectionsBody');
    if (!body) return;
    body.innerHTML = (items || []).map((x) => `
      <tr>
        <td><code style="font-size:.76rem;background:var(--bg);padding:2px 7px;border-radius:5px">${x.section_key}</code></td>
        <td><input value="${x.label}" data-lbl="${x.section_key}" style="max-width:220px;font-size:.82rem;padding:7px 10px;border:1.5px solid var(--border);border-radius:var(--radius-sm)"></td>
        <td>
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
            <input type="checkbox" data-vis="${x.section_key}" ${x.is_visible ? 'checked' : ''}>
            <span class="pill ${x.is_visible ? 'yes' : 'no'}">${x.is_visible ? 'Visible' : 'Hidden'}</span>
          </label>
        </td>
        <td><button class="btn primary xs" onclick="saveSection('${x.section_key}')"><i class="fas fa-floppy-disk"></i> Save</button></td>
      </tr>`).join('');
  };

  window.initSettingsAdmin = function () {
    initPricingAdmin();
    initLayoutAdmin();
  };
})();
