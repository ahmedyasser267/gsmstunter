/**
 * Admin Categories — list + drawer wizard/edit
 */
(function () {
  'use strict';

  let drawerMode = 'create';
  let wizardStep = 0;
  let editTab = 'general';

  const $ = (id) => document.getElementById(id);

  function openDrawer(mode) {
    drawerMode = mode;
    wizardStep = 0;
    editTab = 'general';
    $('categoryDrawerOverlay')?.classList.add('open');
    document.body.style.overflow = 'hidden';
    updateChrome();
    mode === 'create' ? showWizard(0) : showEditTab('general');
  }

  function closeDrawer() {
    $('categoryDrawerOverlay')?.classList.remove('open');
    document.body.style.overflow = '';
  }

  function updateChrome() {
    const isCreate = drawerMode === 'create';
    $('categoryWizardSteps').style.display = isCreate ? '' : 'none';
    $('categoryEditTabs').style.display = isCreate ? 'none' : '';
    $('categoryWizardPanels').style.display = isCreate ? '' : 'none';
    $('categoryEditPanels').style.display = isCreate ? 'none' : '';
    $('categoryDrawerTitle').textContent = isCreate ? 'Add Category' : 'Edit Category';
    renderFooter();
  }

  function showWizard(step) {
    wizardStep = Math.max(0, Math.min(3, step));
    $('categoryWizardSteps')?.querySelectorAll('.wizard-step').forEach((s, i) => {
      s.classList.toggle('active', i === wizardStep);
      s.classList.toggle('done', i < wizardStep);
    });
    $('categoryWizardPanels')?.querySelectorAll('.wizard-panel').forEach((p, i) => {
      p.classList.toggle('active', i === wizardStep);
    });
    if (wizardStep === 3) renderReview();
    renderFooter();
  }

  function showEditTab(tab) {
    editTab = tab;
    $('categoryEditTabs')?.querySelectorAll('.edit-tab').forEach((t) => {
      t.classList.toggle('active', t.dataset.tab === tab);
    });
    $('categoryEditPanels')?.querySelectorAll('.edit-panel').forEach((p) => {
      p.classList.toggle('active', p.dataset.panel === tab);
    });
    renderFooter();
  }

  function renderFooter() {
    const left = $('categoryDrawerFooterLeft');
    const right = $('categoryDrawerFooterRight');
    if (!left || !right) return;
    left.innerHTML = '';
    right.innerHTML = '';

    if (drawerMode === 'create') {
      if (wizardStep > 0) {
        left.innerHTML = '<button type="button" class="btn ghost" id="catWizardBack"><i class="fas fa-arrow-left"></i> Back</button>';
        $('catWizardBack')?.addEventListener('click', () => showWizard(wizardStep - 1));
      }
      if (wizardStep < 3) {
        right.innerHTML = '<button type="button" class="btn primary" id="catWizardNext">Next <i class="fas fa-arrow-right"></i></button>';
        $('catWizardNext')?.addEventListener('click', () => {
          if (wizardStep === 0 && !$('cf-key').value.trim()) {
            toast('Category key is required', 'error');
            return;
          }
          showWizard(wizardStep + 1);
        });
      } else {
        right.innerHTML = '<button type="button" class="btn primary" id="catPublish"><i class="fas fa-check"></i> Create Category</button>';
        $('catPublish')?.addEventListener('click', () => saveCategory('create'));
      }
    } else {
      left.innerHTML = '<button type="button" class="btn danger sm" id="catDelete"><i class="fas fa-trash"></i> Delete</button>';
      right.innerHTML = '<button type="button" class="btn primary" id="catSave"><i class="fas fa-floppy-disk"></i> Save</button>';
      $('catDelete')?.addEventListener('click', deleteCategory);
      $('catSave')?.addEventListener('click', () => saveCategory('update'));
    }
  }

  function prefix() {
    return drawerMode === 'edit' ? 'cf-edit-' : 'cf-';
  }

  function payload() {
    const p = prefix();
    return {
      id: Number($(p + 'id')?.value || 0),
      category_key: $(p + 'key')?.value || '',
      parent_id: $(p + 'parentId')?.value ? Number($(p + 'parentId').value) : null,
      icon: $(p + 'icon')?.value || '',
      image_url: $(p + 'image')?.value || '',
      sort_order: Number($(p + 'sort')?.value || 0),
      is_visible: Number($(p + 'visible')?.value || 1),
      name_nl: $(p + 'nameNl')?.value || '',
      name_de: $(p + 'nameDe')?.value || '',
      name_fr: $(p + 'nameFr')?.value || '',
      desc_nl: '', desc_de: '', desc_fr: '',
    };
  }

  function fillForm(c) {
    if (!c) return;
    ['id', 'key', 'parentId', 'icon', 'image', 'sort', 'nameNl', 'nameDe', 'nameFr'].forEach((f) => {
      const map = { id: c.id, key: c.category_key, parentId: c.parent_id, icon: c.icon, image: c.image_url, sort: c.sort_order, nameNl: c.name_nl, nameDe: c.name_de, nameFr: c.name_fr };
      if ($('cf-' + f)) $('cf-' + f).value = map[f] ?? '';
      if ($('cf-edit-' + f)) $('cf-edit-' + f).value = map[f] ?? '';
    });
    ['cf-visible', 'cf-edit-visible'].forEach((id) => {
      const el = $(id);
      if (el) el.value = c.is_visible ? '1' : '0';
    });
    updateThumb(c.image_url || '');
    $('categoryDrawerSubtitle').textContent = c.name_nl ? `#${c.id} · ${c.category_key}` : '';
  }

  function clearForm() {
    document.querySelectorAll('#categoryDrawerPanels input, #categoryDrawerPanels select, #categoryEditPanels input, #categoryEditPanels select').forEach((f) => {
      f.value = f.tagName === 'SELECT' ? f.options[0]?.value || '' : '';
    });
    ['cf-visible', 'cf-edit-visible'].forEach((id) => { if ($(id)) $(id).value = '1'; });
    updateThumb('');
    $('categoryDrawerSubtitle').textContent = 'Organize your product catalog';
  }

  function updateThumb(url) {
    const grid = $('categoryImagePreview');
    if (!grid) return;
    grid.innerHTML = url
      ? `<div class="image-preview-item"><img src="${url}" alt=""><button type="button" class="image-preview-item__remove" id="catClearImage"><i class="fas fa-xmark"></i></button></div>`
      : '';
    $('catClearImage')?.addEventListener('click', () => {
      $('cf-image').value = $('cf-edit-image').value = $('cf-imageUrlInput').value = $('cf-edit-imageUrlInput').value = '';
      updateThumb('');
    });
  }

  function renderReview() {
    const p = payload();
    $('catReviewSummary').innerHTML = `
      <div class="review-grid">
        <div class="review-card"><div class="review-card__label">Basic</div><div class="review-card__value"><strong>${p.category_key}</strong><br>Sort: ${p.sort_order}<br>${p.is_visible ? 'Visible' : 'Hidden'}</div></div>
        <div class="review-card"><div class="review-card__label">Names</div><div class="review-card__value">NL: ${p.name_nl || '—'}<br>DE: ${p.name_de || '—'}<br>FR: ${p.name_fr || '—'}</div></div>
      </div>`;
  }

  async function uploadImage(file) {
    const fd = new FormData();
    fd.append('image', file);
    const res = await fetch('../api/admin/upload_category_image.php', { method: 'POST', body: fd }).then((x) => x.json());
    if (!res.ok) { toast(res.error || 'Upload failed', 'error'); return; }
    $('cf-image').value = $('cf-edit-image').value = res.url;
    updateThumb(res.url);
    toast('Image uploaded', 'success');
  }

  async function saveCategory(action) {
    if (!(await confirmAction(action === 'create' ? 'Create this category?' : 'Save category changes?'))) return;
    const data = payload();
    data.action = action;
    const res = await fetch('../api/admin/categories.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) }).then((x) => x.json());
    if (res.ok) {
      toast(action === 'create' ? 'Category created' : 'Category updated', 'success');
      closeDrawer();
      loadAll();
    } else toast(res.error || 'Failed', 'error');
  }

  async function deleteCategory() {
    const id = Number($('cf-edit-id')?.value || 0);
    if (!id) return;
    if (!(await confirmAction(`Delete category #${id}?`, true))) return;
    const res = await fetch('../api/admin/categories.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'delete', id }) }).then((x) => x.json());
    if (res.ok) { toast('Category deleted', 'success'); closeDrawer(); loadAll(); }
    else toast(res.error || 'Failed', 'error');
  }

  function applyFilters() {
    const q = ($('categorySearch')?.value || '').toLowerCase().trim();
    const vis = $('categoryFilterVisibility')?.value || '';
    const list = (window.__categories || []).filter((c) => {
      if (vis === '1' && !c.is_visible) return false;
      if (vis === '0' && c.is_visible) return false;
      if (q) {
        const hay = [c.category_key, c.name_nl, c.name_de, c.name_fr].join(' ').toLowerCase();
        if (!hay.includes(q)) return false;
      }
      return true;
    });
    renderCategories(list);
  }

  function renderCategories(list) {
    const body = $('categoriesBody');
    if (!body) return;
    body.innerHTML = list.length
      ? list.map((x) => `
        <tr data-clickable data-category-id="${x.id}">
          <td style="color:var(--text-muted);font-size:.78rem">#${x.id}</td>
          <td>${x.image_url ? `<img class="table-thumb" src="${x.image_url}" alt="">` : `<div class="table-placeholder"><i class="fas fa-image"></i></div>`}</td>
          <td><code style="font-size:.76rem;background:var(--bg);padding:2px 6px;border-radius:4px">${x.category_key}</code></td>
          <td><div class="cell-stack"><strong>${x.name_nl || '—'}</strong><span>${x.icon || ''}</span></div></td>
          <td><span class="pill ${x.is_visible ? 'yes' : 'no'}">${x.is_visible ? 'Live' : 'Hidden'}</span></td>
          <td><div class="row-actions"><button class="btn secondary xs" data-edit-cat="${x.id}"><i class="fas fa-pen"></i> Edit</button></div></td>
        </tr>`).join('')
      : '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-tags"></i><p>No categories found.</p></div></td></tr>';

    body.querySelectorAll('[data-edit-cat]').forEach((btn) => {
      btn.addEventListener('click', (e) => { e.stopPropagation(); openEditCategory(Number(btn.dataset.editCat)); });
    });
    body.querySelectorAll('[data-category-id]').forEach((row) => {
      row.addEventListener('click', (e) => {
        if (e.target.closest('button')) return;
        openEditCategory(Number(row.dataset.categoryId));
      });
    });
    if ($('categoryCount')) $('categoryCount').textContent = list.length + ' item' + (list.length !== 1 ? 's' : '');
  }

  window.openCreateCategory = function () { clearForm(); openDrawer('create'); };
  window.openEditCategory = function (id) {
    const c = (window.__categories || []).find((x) => Number(x.id) === Number(id));
    if (!c) return;
    clearForm();
    fillForm(c);
    drawerMode = 'edit';
    openDrawer('edit');
  };
  window.renderCategories = renderCategories;
  window.applyCategoryFilters = applyFilters;

  function initCategoriesAdmin() {
    $('btnAddCategory')?.addEventListener('click', openCreateCategory);
    $('categoryDrawerOverlay')?.querySelector('[data-close-drawer]')?.addEventListener('click', closeDrawer);
    $('categoryDrawerOverlay')?.addEventListener('click', (e) => { if (e.target.id === 'categoryDrawerOverlay') closeDrawer(); });
    $('categorySearch')?.addEventListener('input', applyFilters);
    $('categoryFilterVisibility')?.addEventListener('change', applyFilters);

    const zone = $('categoryDropzone');
    const fileInput = $('categoryImageFile');
    zone?.addEventListener('click', () => fileInput?.click());
    fileInput?.addEventListener('change', (e) => { if (e.target.files?.[0]) uploadImage(e.target.files[0]); e.target.value = ''; });
    ['dragenter', 'dragover'].forEach((ev) => zone?.addEventListener(ev, (e) => { e.preventDefault(); zone.classList.add('dragover'); }));
    ['dragleave', 'drop'].forEach((ev) => zone?.addEventListener(ev, (e) => {
      e.preventDefault(); zone?.classList.remove('dragover');
      if (ev === 'drop' && e.dataTransfer?.files?.[0]) uploadImage(e.dataTransfer.files[0]);
    }));

    ['cf-imageUrlInput', 'cf-edit-imageUrlInput'].forEach((id) => {
      $(id)?.addEventListener('input', function () {
        $('cf-image').value = $('cf-edit-image').value = this.value;
        updateThumb(this.value);
      });
    });

    $('categoryEditTabs')?.querySelectorAll('.edit-tab').forEach((t) => {
      t.addEventListener('click', () => showEditTab(t.dataset.tab));
    });
    $('categoryWizardSteps')?.querySelectorAll('.wizard-step').forEach((s, i) => {
      s.addEventListener('click', () => { if (drawerMode === 'create' && i <= wizardStep) showWizard(i); });
    });
  }

  window.initCategoriesAdmin = initCategoriesAdmin;
})();
