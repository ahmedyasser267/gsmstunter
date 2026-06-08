/**
 * Admin Products — list, wizard create, tabbed edit
 */
(function () {
  'use strict';

  const COND_OPTIONS = ['as_new', 'excellent', 'good', 'fair', 'refurbished'];
  const STOR_OPTIONS = ['', '32GB', '64GB', '128GB', '256GB', '512GB', '1TB', '2TB', '4TB'];
  const WIZARD_STEPS = ['basic', 'pricing', 'images', 'specs', 'review'];
  const EDIT_TABS = ['general', 'pricing', 'inventory', 'images', 'variants', 'seo'];

  let drawerMode = 'create'; // create | edit
  let wizardStep = 0;
  let editTab = 'general';
  let seoLang = 'nl';
  let filteredList = [];

  const $ = (id) => document.getElementById(id);
  const el = {
    overlay: $('productDrawerOverlay'),
    drawerTitle: $('productDrawerTitle'),
    drawerSubtitle: $('productDrawerSubtitle'),
    wizardNav: $('productWizardSteps'),
    editTabs: $('productEditTabs'),
    wizardPanels: $('productWizardPanels'),
    editPanels: $('productEditPanels'),
    footerLeft: $('productDrawerFooterLeft'),
    footerRight: $('productDrawerFooterRight'),
    productsBody: $('productsBody'),
    productCount: $('productCount'),
    productSearch: $('productSearch'),
    filterCategory: $('productFilterCategory'),
    filterBrand: $('productFilterBrand'),
    filterVisibility: $('productFilterVisibility'),
    filterStock: $('productFilterStock'),
    dropzone: $('productDropzone'),
    imageFileInput: $('productImageFile'),
    imagePreviewGrid: $('productImagePreviewGrid'),
    variantRows: $('productVariantRows'),
  };

  /* ─── Category options ─── */
  function populateCategories(items) {
    const opts = (items || []).map(
      (c) => `<option value="${c.id}">#${c.id} — ${c.name_nl || c.category_key}</option>`
    );
    const html = '<option value="">All categories</option>' + opts.join('');
    if (el.filterCategory) el.filterCategory.innerHTML = html;

    ['pf-categoryId', 'pf-edit-categoryId'].forEach((id) => {
      const sel = $(id);
      if (sel) sel.innerHTML = '<option value="">— Select category —</option>' + opts.join('');
    });
  }

  function populateBrandFilter(products) {
    if (!el.filterBrand) return;
    const brands = [...new Set((products || []).map((p) => p.brand).filter(Boolean))].sort();
    el.filterBrand.innerHTML =
      '<option value="">All brands</option>' +
      brands.map((b) => `<option value="${b}">${b}</option>`).join('');
  }

  /* ─── Field accessors ─── */
  function field(id) {
    return $(id);
  }

  function val(id) {
    const f = field(id);
    return f ? f.value : '';
  }

  function setVal(id, v) {
    const f = field(id);
    if (f) f.value = v ?? '';
  }

  function numVal(id) {
    const v = val(id);
    return v === '' ? null : Number(v);
  }

  /* ─── Drawer open/close ─── */
  function openDrawer(mode) {
    drawerMode = mode;
    wizardStep = 0;
    editTab = 'general';
    el.overlay?.classList.add('open');
    document.body.style.overflow = 'hidden';
    updateDrawerChrome();
    if (mode === 'create') showWizardStep(0);
    else showEditTab('general');
  }

  function closeDrawer() {
    el.overlay?.classList.remove('open');
    document.body.style.overflow = '';
  }

  function updateDrawerChrome() {
    const isCreate = drawerMode === 'create';
    if (el.wizardNav) el.wizardNav.style.display = isCreate ? '' : 'none';
    if (el.editTabs) el.editTabs.style.display = isCreate ? 'none' : '';
    if (el.wizardPanels) el.wizardPanels.style.display = isCreate ? '' : 'none';
    if (el.editPanels) el.editPanels.style.display = isCreate ? 'none' : '';
    if (el.drawerTitle) el.drawerTitle.textContent = isCreate ? 'Add Product' : 'Edit Product';
    renderFooter();
  }

  /* ─── Wizard ─── */
  function showWizardStep(step) {
    wizardStep = Math.max(0, Math.min(WIZARD_STEPS.length - 1, step));
    el.wizardNav?.querySelectorAll('.wizard-step').forEach((s, i) => {
      s.classList.toggle('active', i === wizardStep);
      s.classList.toggle('done', i < wizardStep);
    });
    el.wizardPanels?.querySelectorAll('.wizard-panel').forEach((p, i) => {
      p.classList.toggle('active', i === wizardStep);
    });
    if (wizardStep === WIZARD_STEPS.length - 1) renderReviewSummary();
    renderFooter();
  }

  function showEditTab(tab) {
    editTab = tab;
    el.editTabs?.querySelectorAll('.edit-tab').forEach((t) => {
      t.classList.toggle('active', t.dataset.tab === tab);
    });
    el.editPanels?.querySelectorAll('.edit-panel').forEach((p) => {
      p.classList.toggle('active', p.dataset.panel === tab);
    });
    renderFooter();
  }

  function renderFooter() {
    if (!el.footerLeft || !el.footerRight) return;
    el.footerLeft.innerHTML = '';
    el.footerRight.innerHTML = '';

    if (drawerMode === 'create') {
      if (wizardStep > 0) {
        el.footerLeft.innerHTML =
          '<button type="button" class="btn ghost" id="pfWizardBack"><i class="fas fa-arrow-left"></i> Back</button>';
        $('pfWizardBack')?.addEventListener('click', () => showWizardStep(wizardStep - 1));
      }
      if (wizardStep < WIZARD_STEPS.length - 1) {
        el.footerRight.innerHTML =
          '<button type="button" class="btn primary" id="pfWizardNext">Next <i class="fas fa-arrow-right"></i></button>';
        $('pfWizardNext')?.addEventListener('click', () => {
          if (!validateWizardStep(wizardStep)) return;
          showWizardStep(wizardStep + 1);
        });
      } else {
        el.footerRight.innerHTML =
          '<button type="button" class="btn primary" id="pfPublish"><i class="fas fa-check"></i> Publish Product</button>';
        $('pfPublish')?.addEventListener('click', () => saveProduct('create'));
      }
    } else {
      el.footerLeft.innerHTML =
        '<button type="button" class="btn danger sm" id="pfDelete"><i class="fas fa-trash"></i> Delete</button>';
      el.footerRight.innerHTML =
        '<button type="button" class="btn primary" id="pfSave"><i class="fas fa-floppy-disk"></i> Save Changes</button>';
      $('pfDelete')?.addEventListener('click', deleteProduct);
      $('pfSave')?.addEventListener('click', () => saveProduct('update'));
    }
  }

  function validateWizardStep(step) {
    if (step === 0) {
      if (!val('pf-nameNl').trim()) {
        toast('Product name is required', 'error');
        field('pf-nameNl')?.focus();
        return false;
      }
      if (!val('pf-sku').trim()) {
        toast('SKU is required', 'error');
        field('pf-sku')?.focus();
        return false;
      }
    }
    if (step === 1) {
      if (val('pf-price') === '' || Number(val('pf-price')) < 0) {
        toast('Enter a valid price', 'error');
        field('pf-price')?.focus();
        return false;
      }
    }
    return true;
  }

  /* ─── Variants ─── */
  function variantCondSel(v) {
    return `<select class="v-cond">${COND_OPTIONS.map((c) => `<option value="${c}"${c === v ? ' selected' : ''}>${c}</option>`).join('')}</select>`;
  }
  function variantStorSel(v) {
    return `<select class="v-stor">${STOR_OPTIONS.map((s) => `<option value="${s}"${s === v ? ' selected' : ''}>${s || '(any)'}</option>`).join('')}</select>`;
  }

  function activeVariantTbody() {
    return drawerMode === 'edit'
      ? document.getElementById('productVariantRowsEdit') || el.variantRows
      : el.variantRows;
  }

  function addVariantRow(cond = '', stor = '', price = '', stock = 0) {
    const tbody = activeVariantTbody();
    if (!tbody) return;
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${variantCondSel(cond)}</td>
      <td>${variantStorSel(stor)}</td>
      <td><div style="display:flex;align-items:center;gap:4px"><span style="color:var(--text-muted)">€</span><input type="number" class="v-price" min="0" step="0.01" value="${price}" style="width:90px"></div></td>
      <td><input type="number" class="v-stock" min="0" value="${stock}" style="width:70px"></td>
      <td><button type="button" class="btn danger xs v-remove"><i class="fas fa-trash"></i></button></td>`;
    tr.querySelector('.v-remove')?.addEventListener('click', () => tr.remove());
    tbody.appendChild(tr);
  }

  function clearVariantRows() {
    if (el.variantRows) el.variantRows.innerHTML = '';
    const editRows = document.getElementById('productVariantRowsEdit');
    if (editRows) editRows.innerHTML = '';
  }

  function getVariantsPayload() {
    const tbody = activeVariantTbody();
    if (!tbody) return [];
    return Array.from(tbody.querySelectorAll('tr'))
      .map((tr) => ({
        condition_key: tr.querySelector('.v-cond')?.value || '',
        storage_label: tr.querySelector('.v-stor')?.value || '',
        price: parseFloat(tr.querySelector('.v-price')?.value || 0) || 0,
        stock_qty: parseInt(tr.querySelector('.v-stock')?.value || 0, 10) || 0,
      }))
      .filter((v) => v.condition_key);
  }

  async function loadVariants(productId) {
    clearVariantRows();
    if (!productId) return;
    const res = await fetch(`../api/admin/products.php?variants=1&id=${productId}`)
      .then((x) => x.json())
      .catch(() => null);
    (res?.variants || []).forEach((v) => {
      addVariantRow(v.condition_key, v.storage_label, v.price, v.stock_qty);
    });
    const editTbody = document.getElementById('productVariantRowsEdit');
    if (editTbody && el.variantRows) {
      editTbody.innerHTML = el.variantRows.innerHTML;
      editTbody.querySelectorAll('.v-remove').forEach((btn) => {
        btn.addEventListener('click', () => btn.closest('tr')?.remove());
      });
    }
  }

  /* ─── Payload & form ─── */
  function getProductPayload() {
    if (drawerMode === 'edit') syncFieldsFromEdit();
    const prefix = drawerMode === 'edit' ? 'pf-edit-' : 'pf-';
    const g = (name) => val(prefix + name);
    const discount = numVal(prefix + 'discount');
    const price = Number(g('price') || 0);
    let oldPrice = null;
    if (discount != null && discount > 0 && price > 0) {
      oldPrice = Math.round((price / (1 - discount / 100)) * 100) / 100;
    } else {
      const op = g('oldPrice');
      oldPrice = op ? Number(op) : null;
    }

    return {
      id: Number(g('pid') || 0),
      sku: g('sku'),
      product_type: g('ptype') || 'smartphone',
      category_id: g('categoryId') ? Number(g('categoryId')) : null,
      brand: g('brand'),
      model: g('model'),
      storage_label: g('storageLabel'),
      ram_gb: numVal(prefix + 'ramGb'),
      camera_mp: numVal(prefix + 'cameraMp'),
      battery_mah: numVal(prefix + 'batteryMah'),
      screen_size_in: numVal(prefix + 'screenSizeIn'),
      chipset: g('chipset'),
      color: g('color'),
      condition_key: g('conditionKey'),
      price,
      dynamic_adjust_percent: numVal(prefix + 'dynamicAdjustPercent') ?? 0,
      old_price: oldPrice,
      stock_qty: Number(g('stockQty') || 0),
      sort_order: Number(g('sortOrder') || 0),
      image_url: g('imageUrl'),
      is_visible: Number(g('visibleFlag') || 1),
      name_nl: g('nameNl'),
      name_de: g('nameDe'),
      name_fr: g('nameFr'),
      short_nl: g('shortNl'),
      short_de: g('shortDe'),
      short_fr: g('shortFr'),
      long_nl: g('longNl'),
      long_de: g('longDe'),
      long_fr: g('longFr'),
      variants: getVariantsPayload(),
    };
  }

  function syncFieldsToEdit() {
    const map = [
      'pid', 'sku', 'ptype', 'categoryId', 'brand', 'model', 'storageLabel',
      'ramGb', 'cameraMp', 'batteryMah', 'screenSizeIn', 'chipset', 'color',
      'conditionKey', 'price', 'dynamicAdjustPercent', 'oldPrice', 'stockQty',
      'sortOrder', 'imageUrl', 'visibleFlag', 'nameNl', 'nameDe', 'nameFr',
      'shortNl', 'shortDe', 'shortFr', 'longNl', 'longDe', 'longFr', 'discount',
    ];
    map.forEach((k) => {
      const src = $('pf-' + k);
      const dst = $('pf-edit-' + k);
      if (src && dst) dst.value = src.value;
    });
  }

  function syncFieldsFromEdit() {
    const map = [
      'pid', 'sku', 'ptype', 'categoryId', 'brand', 'model', 'storageLabel',
      'ramGb', 'cameraMp', 'batteryMah', 'screenSizeIn', 'chipset', 'color',
      'conditionKey', 'price', 'dynamicAdjustPercent', 'oldPrice', 'stockQty',
      'sortOrder', 'imageUrl', 'visibleFlag', 'nameNl', 'nameDe', 'nameFr',
      'shortNl', 'shortDe', 'shortFr', 'longNl', 'longDe', 'longFr', 'discount',
    ];
    map.forEach((k) => {
      const src = $('pf-edit-' + k);
      const dst = $('pf-' + k);
      if (src && dst) dst.value = src.value;
    });
    if (el.variantRows && document.getElementById('productVariantRowsEdit')) {
      el.variantRows.innerHTML = document.getElementById('productVariantRowsEdit').innerHTML;
    }
  }

  function fillForm(p) {
    if (!p) return;
    const fields = {
      'pf-pid': p.id || '',
      'pf-edit-pid': p.id || '',
      'pf-sku': p.sku || '',
      'pf-edit-sku': p.sku || '',
      'pf-ptype': p.product_type || 'smartphone',
      'pf-edit-ptype': p.product_type || 'smartphone',
      'pf-categoryId': p.category_id || '',
      'pf-edit-categoryId': p.category_id || '',
      'pf-brand': p.brand || '',
      'pf-edit-brand': p.brand || '',
      'pf-model': p.model || '',
      'pf-edit-model': p.model || '',
      'pf-storageLabel': p.storage_label || '',
      'pf-edit-storageLabel': p.storage_label || '',
      'pf-ramGb': p.ram_gb ?? '',
      'pf-edit-ramGb': p.ram_gb ?? '',
      'pf-cameraMp': p.camera_mp ?? '',
      'pf-edit-cameraMp': p.camera_mp ?? '',
      'pf-batteryMah': p.battery_mah ?? '',
      'pf-edit-batteryMah': p.battery_mah ?? '',
      'pf-screenSizeIn': p.screen_size_in ?? '',
      'pf-edit-screenSizeIn': p.screen_size_in ?? '',
      'pf-chipset': p.chipset || '',
      'pf-edit-chipset': p.chipset || '',
      'pf-color': p.color || '',
      'pf-edit-color': p.color || '',
      'pf-conditionKey': p.condition_key || '',
      'pf-edit-conditionKey': p.condition_key || '',
      'pf-price': p.price ?? '',
      'pf-edit-price': p.price ?? '',
      'pf-dynamicAdjustPercent': p.dynamic_adjust_percent ?? 0,
      'pf-edit-dynamicAdjustPercent': p.dynamic_adjust_percent ?? 0,
      'pf-oldPrice': p.old_price ?? '',
      'pf-edit-oldPrice': p.old_price ?? '',
      'pf-stockQty': p.stock_qty ?? 0,
      'pf-edit-stockQty': p.stock_qty ?? 0,
      'pf-sortOrder': p.sort_order ?? 0,
      'pf-edit-sortOrder': p.sort_order ?? 0,
      'pf-imageUrl': p.image_url || '',
      'pf-edit-imageUrl': p.image_url || '',
      'pf-visibleFlag': p.is_visible ? '1' : '0',
      'pf-edit-visibleFlag': p.is_visible ? '1' : '0',
      'pf-nameNl': p.name_nl || '',
      'pf-edit-nameNl': p.name_nl || '',
      'pf-nameDe': p.name_de || '',
      'pf-edit-nameDe': p.name_de || '',
      'pf-nameFr': p.name_fr || '',
      'pf-edit-nameFr': p.name_fr || '',
      'pf-shortNl': p.short_nl || '',
      'pf-edit-shortNl': p.short_nl || '',
      'pf-shortDe': p.short_de || '',
      'pf-edit-shortDe': p.short_de || '',
      'pf-shortFr': p.short_fr || '',
      'pf-edit-shortFr': p.short_fr || '',
      'pf-longNl': p.long_nl || '',
      'pf-edit-longNl': p.long_nl || '',
      'pf-longDe': p.long_de || '',
      'pf-edit-longDe': p.long_de || '',
      'pf-longFr': p.long_fr || '',
      'pf-edit-longFr': p.long_fr || '',
    };
    Object.entries(fields).forEach(([id, v]) => setVal(id, v));

    if (p.old_price && p.price && Number(p.old_price) > Number(p.price)) {
      const disc = Math.round((1 - Number(p.price) / Number(p.old_price)) * 100);
      setVal('pf-discount', disc);
      setVal('pf-edit-discount', disc);
    } else {
      setVal('pf-discount', '');
      setVal('pf-edit-discount', '');
    }

    updateImagePreview(p.image_url || '');
    const urlInput = $('pf-imageUrlInput');
    const editUrlInput = $('pf-edit-imageUrlInput');
    if (urlInput) urlInput.value = p.image_url || '';
    if (editUrlInput) editUrlInput.value = p.image_url || '';
    if (el.drawerSubtitle) {
      el.drawerSubtitle.textContent = p.name_nl ? `#${p.id} · ${p.sku || ''}` : 'Fill in the details below';
    }
    loadVariants(p.id || 0);
  }

  function clearForm() {
    document.querySelectorAll('#productDrawerPanels input:not([type=file]), #productDrawerPanels select, #productDrawerPanels textarea, #productEditPanels input:not([type=file]), #productEditPanels select, #productEditPanels textarea').forEach((f) => {
      if (f.type === 'hidden') f.value = '';
      else if (f.tagName === 'SELECT') f.selectedIndex = 0;
      else f.value = '';
    });
    setVal('pf-ptype', 'smartphone');
    setVal('pf-edit-ptype', 'smartphone');
    setVal('pf-visibleFlag', '1');
    setVal('pf-edit-visibleFlag', '1');
    updateImagePreview('');
    clearVariantRows();
    if (el.drawerSubtitle) el.drawerSubtitle.textContent = 'Fill in the details below';
  }

  function updateImagePreview(url) {
    if (!el.imagePreviewGrid) return;
    el.imagePreviewGrid.innerHTML = url
      ? `<div class="image-preview-item">
          <img src="${url}" alt="Product">
          <button type="button" class="image-preview-item__remove" data-clear-image><i class="fas fa-xmark"></i></button>
        </div>`
      : '';
    el.imagePreviewGrid.querySelector('[data-clear-image]')?.addEventListener('click', () => {
      setVal('pf-imageUrl', '');
      setVal('pf-edit-imageUrl', '');
      updateImagePreview('');
    });
  }

  function renderReviewSummary() {
    const box = $('pfReviewSummary');
    if (!box) return;
    const p = getProductPayload();
    const cat = (window.__categories || []).find((c) => Number(c.id) === Number(p.category_id));
    box.innerHTML = `
      <div class="review-grid">
        <div class="review-card"><div class="review-card__label">Basic</div><div class="review-card__value"><strong>${p.name_nl || '—'}</strong><br>SKU: ${p.sku || '—'}<br>Brand: ${p.brand || '—'}<br>Category: ${cat?.name_nl || '—'}</div></div>
        <div class="review-card"><div class="review-card__label">Pricing & Stock</div><div class="review-card__value">Price: <strong>€${Number(p.price).toFixed(2)}</strong><br>Stock: ${p.stock_qty}<br>Visible: ${p.is_visible ? 'Yes' : 'No'}</div></div>
        <div class="review-card"><div class="review-card__label">Image</div><div class="review-card__value">${p.image_url ? `<img src="${p.image_url}" style="max-width:80px;border-radius:8px;border:1px solid var(--border)">` : 'No image'}</div></div>
        <div class="review-card"><div class="review-card__label">Variants</div><div class="review-card__value">${p.variants.length} variant row(s)</div></div>
      </div>`;
  }

  /* ─── CRUD ─── */
  async function saveProduct(action) {
    if (action === 'create' && !validateWizardStep(0)) return;
    const msg = action === 'create' ? 'Add this product to the catalog?' : 'Save changes to this product?';
    if (!(await confirmAction(msg))) return;

    const payload = getProductPayload();
    payload.action = action;
    const res = await fetch('../api/admin/products.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    }).then((x) => x.json());

    if (res.ok) {
      toast(action === 'create' ? 'Product published successfully' : 'Product updated', 'success');
      closeDrawer();
      if (typeof loadAll === 'function') loadAll();
    } else {
      toast(res.error || 'Save failed', 'error');
    }
  }

  async function deleteProduct() {
    const id = Number(val('pf-edit-pid') || val('pf-pid') || 0);
    if (!id) return;
    if (!(await confirmAction(`Permanently delete product #${id}? This cannot be undone.`, true))) return;
    const res = await fetch('../api/admin/products.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', id }),
    }).then((x) => x.json());
    if (res.ok) {
      toast('Product deleted', 'success');
      closeDrawer();
      if (typeof loadAll === 'function') loadAll();
    } else toast(res.error || 'Delete failed', 'error');
  }

  /* ─── Image upload ─── */
  async function uploadImage(file) {
    if (!file || !file.type.startsWith('image/')) {
      toast('Please select a valid image', 'error');
      return;
    }
    const fd = new FormData();
    fd.append('image', file);
    const res = await fetch('../api/admin/upload_product_image.php', { method: 'POST', body: fd }).then((x) =>
      x.json()
    );
    if (!res.ok) {
      toast(res.error || 'Upload failed', 'error');
      return;
    }
    setVal('pf-imageUrl', res.url);
    setVal('pf-edit-imageUrl', res.url);
    updateImagePreview(res.url);
    toast('Image uploaded', 'success');
  }

  function setupDropzone() {
    const zones = [el.dropzone, document.getElementById('productDropzoneEdit')].filter(Boolean);
    zones.forEach((zone) => {
      zone.addEventListener('click', () => el.imageFileInput?.click());
    });
    el.imageFileInput?.addEventListener('change', (e) => {
      const f = e.target.files?.[0];
      if (f) uploadImage(f);
      e.target.value = '';
    });
    zones.forEach((zone) => {
      ['dragenter', 'dragover'].forEach((ev) => {
        zone.addEventListener(ev, (e) => {
          e.preventDefault();
          zone.classList.add('dragover');
        });
      });
      ['dragleave', 'drop'].forEach((ev) => {
        zone.addEventListener(ev, (e) => {
          e.preventDefault();
          zone.classList.remove('dragover');
          if (ev === 'drop' && e.dataTransfer?.files?.[0]) uploadImage(e.dataTransfer.files[0]);
        });
      });
    });

    $('pf-edit-imageUrlInput')?.addEventListener('input', function () {
      setVal('pf-imageUrl', this.value);
      setVal('pf-edit-imageUrl', this.value);
      setVal('pf-imageUrlInput', this.value);
      updateImagePreview(this.value);
    });
  }

  /* ─── List & filters ─── */
  function applyFilters() {
    const q = (el.productSearch?.value || '').toLowerCase().trim();
    const cat = el.filterCategory?.value || '';
    const brand = el.filterBrand?.value || '';
    const vis = el.filterVisibility?.value || '';
    const stock = el.filterStock?.value || '';

    filteredList = (window.__products || []).filter((x) => {
      if (cat && String(x.category_id) !== cat) return false;
      if (brand && x.brand !== brand) return false;
      if (vis === '1' && !x.is_visible) return false;
      if (vis === '0' && x.is_visible) return false;
      if (stock === 'in' && !(Number(x.stock_qty) > 0)) return false;
      if (stock === 'out' && Number(x.stock_qty) > 0) return false;
      if (stock === 'low' && (Number(x.stock_qty) > 5 || Number(x.stock_qty) <= 0)) return false;
      if (q) {
        const hay = [
          x.name_nl, x.name_de, x.name_fr, x.sku, x.brand, x.model, x.product_type, x.color,
        ]
          .join(' ')
          .toLowerCase();
        if (!hay.includes(q)) return false;
      }
      return true;
    });
    renderProducts(filteredList);
  }

  window.applyProductFilters = applyFilters;

  function renderProducts(list) {
    if (!el.productsBody) return;
    el.productsBody.innerHTML = list.length
      ? list
          .map(
            (x) => `
      <tr data-product-id="${x.id}">
        <td style="font-size:.78rem;color:var(--text-muted)">#${x.id}</td>
        <td>${x.image_url ? `<img class="table-thumb" src="${x.image_url}" alt="">` : `<div class="table-placeholder"><i class="fas fa-image"></i></div>`}</td>
        <td><code style="font-size:.76rem;background:var(--bg);padding:2px 6px;border-radius:4px">${x.sku}</code></td>
        <td><div class="product-name-cell"><strong>${x.name_nl || '—'}</strong><span>${x.model || x.product_type || ''}</span></div></td>
        <td>${x.brand || '—'}</td>
        <td><strong>€${Number(x.price || 0).toFixed(2)}</strong></td>
        <td>${x.stock_qty ?? '—'}</td>
        <td><span class="pill ${x.is_visible ? 'yes' : 'no'}">${x.is_visible ? 'Live' : 'Hidden'}</span></td>
        <td><div class="product-row-actions"><button class="btn secondary xs" data-edit="${x.id}"><i class="fas fa-pen"></i> Edit</button></div></td>
      </tr>`
          )
          .join('')
      : '<tr><td colspan="9"><div class="empty-state"><i class="fas fa-mobile-screen"></i><p>No products found.</p></div></td></tr>';

    el.productsBody.querySelectorAll('[data-edit]').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        openEditProduct(Number(btn.dataset.edit));
      });
    });
    el.productsBody.querySelectorAll('tr[data-product-id]').forEach((row) => {
      row.addEventListener('click', (e) => {
        if (e.target.closest('button')) return;
        openEditProduct(Number(row.dataset.productId));
      });
    });
    if (el.productCount) el.productCount.textContent = list.length + ' item' + (list.length !== 1 ? 's' : '');
  }

  window.openCreateProduct = function () {
    clearForm();
    openDrawer('create');
  };

  window.openEditProduct = function (id) {
    const p = (window.__products || []).find((x) => Number(x.id) === Number(id));
    if (!p) return;
    clearForm();
    fillForm(p);
    syncFieldsToEdit();
    drawerMode = 'edit';
    openDrawer('edit');
  };

  window.renderProducts = renderProducts;
  window.populateProductCategories = populateCategories;
  window.populateProductBrandFilter = populateBrandFilter;

  function initProductAdmin() {
    $('btnAddProduct')?.addEventListener('click', openCreateProduct);
    el.overlay?.querySelector('[data-close-drawer]')?.addEventListener('click', closeDrawer);
    el.overlay?.addEventListener('click', (e) => {
      if (e.target === el.overlay) closeDrawer();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && el.overlay?.classList.contains('open')) closeDrawer();
    });

    el.productSearch?.addEventListener('input', applyFilters);
    [el.filterCategory, el.filterBrand, el.filterVisibility, el.filterStock].forEach((f) => {
      f?.addEventListener('change', applyFilters);
    });

    el.wizardNav?.querySelectorAll('.wizard-step').forEach((s, i) => {
      s.addEventListener('click', () => {
        if (drawerMode !== 'create') return;
        if (i <= wizardStep) showWizardStep(i);
      });
    });

    el.editTabs?.querySelectorAll('.edit-tab').forEach((t) => {
      t.addEventListener('click', () => showEditTab(t.dataset.tab));
    });

    document.querySelectorAll('.seo-lang-tab').forEach((t) => {
      t.addEventListener('click', () => {
        seoLang = t.dataset.lang;
        document.querySelectorAll('.seo-lang-tab').forEach((x) => x.classList.toggle('active', x.dataset.lang === seoLang));
        document.querySelectorAll('.seo-lang-panel').forEach((p) =>
          p.classList.toggle('active', p.dataset.lang === seoLang)
        );
      });
    });

    $('pfAddVariant')?.addEventListener('click', () => addVariantRow());
    $('pfEditAddVariant')?.addEventListener('click', () => {
      drawerMode = 'edit';
      addVariantRow();
    });

    $('pf-imageUrlInput')?.addEventListener('input', function () {
      setVal('pf-imageUrl', this.value);
      setVal('pf-edit-imageUrl', this.value);
      const editIn = $('pf-edit-imageUrlInput');
      if (editIn) editIn.value = this.value;
      updateImagePreview(this.value);
    });

    setupDropzone();
    renderFooter();
  }

  window.initProductAdmin = initProductAdmin;
})();
