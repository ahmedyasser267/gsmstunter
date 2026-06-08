/* ============================================
   GSMSTUNTER - Refurbished Electronics Marketplace
   Complete JavaScript - Production Ready
   ============================================ */

/* ── Translations (loaded from lang/*.js files) ── */
/*  - assets/js/lang/nl.js (Nederlands - Hoofdtaal)
    - assets/js/lang/de.js (Deutsch - Deutschland)
    - assets/js/lang/fr.js (Français de Belgique)
*/
const translations = {
  nl: (typeof LANG_NL !== 'undefined') ? LANG_NL : {},
  de: (typeof LANG_DE !== 'undefined') ? LANG_DE : {},
  fr: (typeof LANG_FR !== 'undefined') ? LANG_FR : {}
};

/** Global cart/search state — populated by assets/js/state.js */
var AppState = window.AppState;

/* ── Language Switcher ── */
function setLanguage(lang) {
  AppState.language = lang;
  localStorage.setItem('gsmstunter-lang', lang);
  applyTranslations();
  updateLangSwitcher();
}

function applyTranslations() {
  const lang = AppState.language;
  const t = translations[lang];
  if (!t) return;

  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (t[key]) {
      el.textContent = t[key];
    }
  });

  document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
    const key = el.getAttribute('data-i18n-placeholder');
    if (t[key]) {
      el.placeholder = t[key];
    }
  });

  document.querySelectorAll('[data-i18n-title]').forEach(el => {
    const key = el.getAttribute('data-i18n-title');
    if (t[key]) {
      el.title = t[key];
    }
  });

  document.querySelectorAll('[data-i18n-aria-label]').forEach(el => {
    const key = el.getAttribute('data-i18n-aria-label');
    if (t[key]) {
      el.setAttribute('aria-label', t[key]);
    }
  });

  document.documentElement.lang = lang;
}

function updateLangSwitcher() {
  const activeLang = AppState.language;
  document.querySelectorAll('.lang-dropdown__item').forEach(item => {
    item.classList.toggle('active', item.dataset.lang === activeLang);
  });

  const switcher = document.querySelector('.lang-switcher');
  if (switcher) {
    const flagImg = switcher.querySelector('.lang-switcher__flag');
    const textEl = switcher.querySelector('.lang-switcher__text');
    if (flagImg) {
      const flagMap = {
        nl: { src: 'https://flagcdn.com/w40/nl.png', alt: 'Nederlands' },
        de: { src: 'https://flagcdn.com/w40/de.png', alt: 'Deutsch' },
        fr: { src: 'https://flagcdn.com/w40/be.png', alt: 'Français (BE)' }
      };
      const flag = flagMap[activeLang] || flagMap.nl;
      flagImg.src = flag.src;
      flagImg.alt = flag.alt;
    }
    if (textEl) {
      textEl.textContent = activeLang.toUpperCase();
    }
  }
}

/* Cart, wishlist & toasts: assets/js/cart.js + ui.js */

/* ── Helper: Translate ── */
function translate(key) {
  return translations[AppState.language]?.[key] || key;
}

/* ── Header Scroll Effect ── */
function initStickyHeader() {
  const header = document.querySelector('.header');
  if (!header) return;

  window.addEventListener('scroll', () => {
    header.classList.toggle('scrolled', window.scrollY > 10);
  }, { passive: true });
}

/* ── Mobile Menu ── */
function initMobileMenu() {
  const toggle = document.querySelector('.mobile-menu-toggle');
  const menu = document.querySelector('.mobile-menu');
  const close = document.querySelector('.mobile-menu__close');
  const overlay = document.querySelector('.mobile-menu__overlay');

  if (!toggle || !menu) return;

  toggle.addEventListener('click', () => {
    menu.classList.add('active');
    document.body.style.overflow = 'hidden';
  });

  const closeMenu = () => {
    menu.classList.remove('active');
    document.body.style.overflow = '';
  };

  if (close) close.addEventListener('click', closeMenu);
  if (overlay) overlay.addEventListener('click', closeMenu);
}

/* ── Language Dropdown ── */
function initLangDropdown() {
  const switcher = document.querySelector('.lang-switcher');
  const dropdown = document.querySelector('.lang-dropdown');
  if (!switcher || !dropdown) return;

  switcher.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.classList.toggle('active');
  });

  dropdown.querySelectorAll('.lang-dropdown__item').forEach(item => {
    item.addEventListener('click', () => {
      setLanguage(item.dataset.lang);
      dropdown.classList.remove('active');
    });
  });

  document.addEventListener('click', () => {
    dropdown.classList.remove('active');
  });
}

/* ── Search Autocomplete + Grid Filter ── */
function initSearch() {
  const input = document.querySelector('.search-bar__input');
  const dropdown = document.querySelector('.search-autocomplete');
  if (!input || !dropdown) return;

  const fallbackProducts = [
    { name: 'iPhone 15 Pro Max', category: 'Smartphones', img: 'https://images.unsplash.com/photo-1591337676887-a217a6c8d2f4?w=80&h=80&fit=crop' },
    { name: 'iPhone 14 Pro', category: 'Smartphones', img: 'https://images.unsplash.com/photo-1580910051074-3eb694886f8b?w=80&h=80&fit=crop' },
    { name: 'Samsung Galaxy S24', category: 'Smartphones', img: 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=80&h=80&fit=crop' },
    { name: 'MacBook Pro M3', category: 'Laptops', img: 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=80&h=80&fit=crop' },
    { name: 'iPad Pro 12.9"', category: 'Tablets', img: 'https://images.unsplash.com/photo-1585790050230-5dd28404ccb9?w=80&h=80&fit=crop' },
    { name: 'Apple Watch Series 9', category: 'Smartwatches', img: 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=80&h=80&fit=crop' },
    { name: 'AirPods Pro', category: 'Headphones', img: 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=80&h=80&fit=crop' }
  ];

  const isProductsPage = !!document.getElementById('products-grid');

  input.addEventListener('input', () => {
    const query = input.value.toLowerCase().trim();

    /* ── On products page: filter the grid live ── */
    if (isProductsPage) {
      filterProductsGrid(query);
      dropdown.classList.remove('active');
      return;
    }

    /* ── On other pages: show autocomplete ── */
    if (query.length < 2) { dropdown.classList.remove('active'); return; }

    const apiProducts = (window.__allProductsData || []).map(p => ({
      name: p.name || `${p.brand || ''} ${p.model || ''}`.trim(),
      category: p.category_key || '',
      img: p.image_url || 'https://images.unsplash.com/photo-1512054502232-10a0a035d672?w=80&h=80&fit=crop',
      sku: p.sku, id: p.id
    }));
    const pool = apiProducts.length ? apiProducts : fallbackProducts;
    const matches = pool.filter(p =>
      p.name.toLowerCase().includes(query) || (p.category || '').toLowerCase().includes(query)
    ).slice(0, 6);

    if (!matches.length) { dropdown.classList.remove('active'); return; }

    dropdown.innerHTML = matches.map(p => {
      const href = p.sku
        ? `products.html?search=${encodeURIComponent(query)}`
        : 'products.html';
      return `<a href="${href}" class="search-autocomplete__item">
        <img src="${p.img}" alt="${p.name}" loading="lazy" style="width:44px;height:44px;object-fit:cover;border-radius:6px;flex-shrink:0">
        <div>
          <div style="font-weight:500;font-size:.875rem">${p.name}</div>
          <div style="font-size:.75rem;color:var(--color-text-muted)">${p.category || 'Product'}</div>
        </div>
      </a>`;
    }).join('');
    dropdown.classList.add('active');
  });

  /* Navigate to products page on Enter */
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      const q = input.value.trim();
      if (q) {
        if (isProductsPage) { filterProductsGrid(q.toLowerCase()); }
        else { window.location.href = `products.html?search=${encodeURIComponent(q)}`; }
      }
      dropdown.classList.remove('active');
    }
  });

  input.addEventListener('focus', () => {
    if (input.value.length >= 2 && !isProductsPage) dropdown.classList.add('active');
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.search-bar')) dropdown.classList.remove('active');
  });
}

/* ── Products page grid search filter ── */
function initProductsPageSearch() {
  /* Pick up ?search= from URL on load */
  const params = new URLSearchParams(window.location.search);
  const q = (params.get('search') || '').trim().toLowerCase();
  const input = document.querySelector('.search-bar__input');
  if (q) {
    if (input) input.value = q;
    filterProductsGrid(q);
  }
}

function filterProductsGrid(query) {
  const grid = document.getElementById('products-grid');
  const countEl = document.querySelector('.products-toolbar__count span');
  if (!grid) return;

  const cards = grid.querySelectorAll('.product-card, article.product-card');
  let visible = 0;

  cards.forEach(card => {
    const title = (card.querySelector('.product-card__title')?.textContent || '').toLowerCase();
    const specs = (card.querySelector('.product-card__specs')?.textContent || '').toLowerCase();
    const brand = (card.dataset.brand || '').toLowerCase();
    const storage = (card.dataset.storage || '').toLowerCase();
    const color = (card.dataset.color || '').toLowerCase();

    const match = !query ||
      title.includes(query) ||
      specs.includes(query) ||
      brand.includes(query) ||
      storage.includes(query) ||
      color.includes(query);

    card.style.display = match ? '' : 'none';
    if (match) visible++;
  });

  if (countEl) countEl.textContent = visible;

  /* Show empty state if nothing matches */
  let emptyEl = grid.querySelector('.search-empty-state');
  if (visible === 0 && query) {
    if (!emptyEl) {
      emptyEl = document.createElement('div');
      emptyEl.className = 'search-empty-state';
      emptyEl.style.cssText = 'grid-column:1/-1;padding:3rem 1rem;text-align:center;color:var(--color-text-secondary);background:var(--color-bg);border-radius:var(--radius-xl);border:1px dashed var(--color-border)';
      grid.appendChild(emptyEl);
    }
    emptyEl.innerHTML = `<i class="fas fa-magnifying-glass" style="font-size:2rem;margin-bottom:.75rem;display:block;opacity:.3"></i><p style="font-size:.95rem">Geen producten gevonden voor "<strong>${escapeHtml(query)}</strong>".<br><a href="products.html" style="color:var(--color-primary);text-decoration:underline;margin-top:.5rem;display:inline-block">Alle producten tonen</a></p>`;
    emptyEl.style.display = '';
  } else if (emptyEl) {
    emptyEl.style.display = 'none';
  }
}

/* ── Scroll Animations ── */
function initScrollAnimations() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

  document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right').forEach(el => {
    observer.observe(el);
  });
}

/* ── Accordion ── */
function initAccordions() {
  document.querySelectorAll('.accordion-item__header').forEach(header => {
    header.addEventListener('click', () => {
      const item = header.closest('.accordion-item');
      const isActive = item.classList.contains('active');

      item.closest('.accordion')?.querySelectorAll('.accordion-item').forEach(sib => {
        sib.classList.remove('active');
      });

      if (!isActive) {
        item.classList.add('active');
      }
    });
  });

  document.querySelectorAll('.faq-item__question').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.faq-item');
      const isActive = item.classList.contains('active');
      const accordion = item.closest('.faq-accordion');

      accordion?.querySelectorAll('.faq-item').forEach(sib => sib.classList.remove('active'));

      if (!isActive) {
        item.classList.add('active');
      }
    });
  });
}

/* ── Tabs ── */
function initTabs() {
  document.querySelectorAll('.tabs').forEach(tabContainer => {
    const tabs = tabContainer.querySelectorAll('.tab');
    const panels = tabContainer.parentElement.querySelectorAll('.tab-panel');

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        const target = tab.dataset.tab;

        tabs.forEach(t => t.classList.remove('active'));
        panels.forEach(p => p.classList.remove('active'));

        tab.classList.add('active');
        const panel = document.getElementById(target);
        if (panel) panel.classList.add('active');
      });
    });
  });
}

/* ── Modal ── */
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.classList.remove('active');
  document.body.style.overflow = '';
}

function initModals() {
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
  });

  document.querySelectorAll('.modal__close').forEach(btn => {
    btn.addEventListener('click', () => {
      const overlay = btn.closest('.modal-overlay');
      if (overlay) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
  });
}

/* ── Product Filtering (Products Page) ── */
function initProductFilters() {
  const filterCheckboxes = document.querySelectorAll('.filter-option input[type="checkbox"]');
  const sortSelect = document.querySelector('.products-toolbar__sort select');

  filterCheckboxes.forEach(cb => {
    cb.onchange = applyFilters;
  });

  if (sortSelect) {
    sortSelect.onchange = applyFilters;
  }
}

function applyFilters() {
  const cards = document.querySelectorAll('.product-card[data-brand]');
  if (cards.length === 0) return;

  const activeFilters = {};
  document.querySelectorAll('.filter-option input:checked').forEach(cb => {
    const group = cb.dataset.filterGroup;
    if (!activeFilters[group]) activeFilters[group] = [];
    activeFilters[group].push(cb.value.toLowerCase());
  });

  let visibleCount = 0;
  cards.forEach(card => {
    let show = true;

    Object.keys(activeFilters).forEach(group => {
      const filterValues = activeFilters[group];
      if (filterValues.length === 0) return;

      const cardValue = (card.dataset[group] || '').toLowerCase();
      const cardValues = cardValue.split(',').map(v => v.trim()).filter(Boolean);
      const matches = cardValues.length > 0
        ? filterValues.some(v => cardValues.includes(v))
        : filterValues.includes(cardValue);
      if (!matches) {
        show = false;
      }
    });

    card.style.display = show ? '' : 'none';
    if (show) visibleCount++;
  });

  const countEl = document.querySelector('.products-toolbar__count span');
  if (countEl) countEl.textContent = visibleCount;
}

/* ── View Toggle (Grid/List) ── */
function initViewToggle() {
  const buttons = document.querySelectorAll('.view-toggle__btn');
  const grid = document.querySelector('.products-grid');
  if (!buttons.length || !grid) return;

  buttons.forEach(btn => {
    btn.addEventListener('click', () => {
      buttons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const view = btn.dataset.view;
      grid.classList.toggle('products-grid--list', view === 'list');
    });
  });
}

/* ── Product Detail Page ── */
function addProductDetailToCart() {
  const selected = window.__productDetailSelected || {};
  const priceEl = document.querySelector('.product-info__price, .product-detail__price');
  const conditionOpt = document.querySelector('.condition-option input:checked');
  const storageOpt = document.querySelector('.storage-option input:checked');
  const qtyEl = document.querySelector('.quantity-selector__value');
  const price = priceEl ? parseInt(priceEl.textContent.replace(/[^\d]/g, '')) : 899;
  const condition = conditionOpt ? conditionOpt.closest('.condition-option').querySelector('.condition-option__text')?.textContent || 'Als nieuw' : 'Als nieuw';
  const storage = storageOpt ? (storageOpt.value === '1024' ? '1TB' : storageOpt.value + 'GB') : '128GB';
  const quantity = qtyEl ? parseInt(qtyEl.textContent) || 1 : 1;
  addToCart({
    id: selected.id || 'product-detail',
    product_id: selected.product_id || null,
    sku: selected.sku || null,
    name: selected.name || 'Product',
    price,
    image: selected.image || 'https://images.unsplash.com/photo-1591337676887-a217a6c8d2f4?w=200&h=200&fit=crop',
    condition,
    storage,
    quantity
  });
}

async function initDynamicProductDetailPage() {
  const titleEl = document.getElementById('product-title');
  if (!titleEl) return;

  const params = new URLSearchParams(window.location.search);
  const skuParam = (params.get('sku') || '').trim();
  const idParam = (params.get('id') || '').trim();

  try {
    const lang = AppState.language || 'nl';
    const data = await fetchJsonFromCandidates([
      `api/public/products.php?lang=${encodeURIComponent(lang)}`,
      `${getProjectBasePath()}api/public/products.php?lang=${encodeURIComponent(lang)}`,
      `/api/public/products.php?lang=${encodeURIComponent(lang)}`
    ]);
    if (!data.ok || !Array.isArray(data.items)) return;

    const item = data.items.find((x) => {
      if (skuParam) return String(x.sku || '').toLowerCase() === skuParam.toLowerCase();
      return String(x.id || '') === idParam;
    }) || data.items[0];
    if (!item) return;

    const name = item.name || `${item.brand || ''} ${item.model || ''}`.trim() || 'Product';
    const price = Number(item.effective_price || item.price || 0);
    const oldPrice = Number(item.old_price || 0);
    const image = item.image_url || 'https://images.unsplash.com/photo-1512054502232-10a0a035d672?w=800&h=800&fit=crop';
    const subtitle = [item.storage_label || '', item.color || '', item.product_type || ''].filter(Boolean).join(' · ');

    titleEl.textContent = name;
    document.title = `${name} - Refurbished | GSMStunter`;
    const subtitleEl = document.querySelector('.product-detail__subtitle');
    if (subtitleEl) subtitleEl.textContent = subtitle;
    const priceEl = document.querySelector('.product-detail__price');
    if (priceEl) priceEl.textContent = `€${price.toFixed(0)}`;
    const oldPriceEl = document.querySelector('.product-detail__price-original');
    if (oldPriceEl) oldPriceEl.textContent = oldPrice > 0 ? `€${oldPrice.toFixed(0)}` : '';

    const mainImage = document.getElementById('product-main-image');
    if (mainImage) {
      mainImage.src = image;
      mainImage.alt = name;
    }
    const thumbs = document.querySelector('.product-gallery__thumbs');
    if (thumbs) {
      thumbs.innerHTML = `
        <button type="button" class="product-gallery__thumb active" aria-label="Product image" data-image="${image}">
          <img src="${image}" alt="${name}" width="80" height="80" loading="lazy">
        </button>
      `;
    }
    window.__productDetailSelected = {
      id: item.sku || `p-${item.id}`,
      product_id: item.id,
      sku: item.sku || null,
      name,
      image
    };
    initProductGallery();
  } catch (e) {
    console.warn('Dynamic product detail failed', e);
  }
}

/* ── Product Gallery (Detail Page) ── */
function initProductGallery() {
  const thumbs = document.querySelectorAll('.product-gallery__thumb');
  const mainImg = document.querySelector('.product-gallery__main img');
  if (!thumbs.length || !mainImg) return;

  thumbs.forEach(thumb => {
    thumb.addEventListener('click', () => {
      thumbs.forEach(t => t.classList.remove('active'));
      thumb.classList.add('active');
      const imgSrc = thumb.dataset.image;
      if (imgSrc) {
        mainImg.src = imgSrc;
      }
    });
  });
}

/* ── Condition Selector (Detail Page) ── */
function initConditionSelector() {
  document.querySelectorAll('.condition-option').forEach(option => {
    option.addEventListener('click', () => {
      option.closest('.condition-selector__options')
        .querySelectorAll('.condition-option')
        .forEach(o => o.classList.remove('active'));
      option.classList.add('active');

      const priceEl = document.querySelector('.product-info__price');
      if (priceEl && option.dataset.price) {
        priceEl.textContent = '€' + option.dataset.price;
      }
    });
  });
}

/* ── Storage Selector (Detail Page) ── */
function initStorageSelector() {
  document.querySelectorAll('.storage-option').forEach(option => {
    option.addEventListener('click', () => {
      option.closest('.storage-selector__options')
        .querySelectorAll('.storage-option')
        .forEach(o => o.classList.remove('active'));
      option.classList.add('active');
    });
  });
}

/* ── Quantity Selector ── */
function initQuantitySelectors() {
  document.querySelectorAll('.quantity-selector').forEach(selector => {
    const minusBtn = selector.querySelector('.quantity-selector__btn:first-child');
    const plusBtn = selector.querySelector('.quantity-selector__btn:last-child');
    const valueEl = selector.querySelector('.quantity-selector__value');

    if (!minusBtn || !plusBtn || !valueEl) return;

    minusBtn.addEventListener('click', () => {
      let val = parseInt(valueEl.textContent) || 1;
      if (val > 1) valueEl.textContent = val - 1;
    });

    plusBtn.addEventListener('click', () => {
      let val = parseInt(valueEl.textContent) || 1;
      if (val < 10) valueEl.textContent = val + 1;
    });
  });
}

/* ── Sell Wizard ── */
function initSellWizard() {
  const wizard = document.querySelector('.sell-wizard');
  if (!wizard) return;

  let currentStep = 1;
  const totalSteps = 4;

  const state = {
    device: null,
    brand: null,
    model: null,
    screen: null,
    function: null,
    cosmetic: null,
    battery: null,
    accessories: null,
    water: null,
    age: null
  };

  const quoteAmountEl = wizard.querySelector('.sell-wizard__quote-amount');
  const modelGrid = wizard.querySelector('.sell-wizard__model-grid');
  const modelOptions = modelGrid ? Array.from(modelGrid.querySelectorAll('.device-option')) : [];

  window.sellWizardNext = function () {
    if (!canGoNext()) return;
    if (currentStep < totalSteps) {
      currentStep++;
      updateWizardStep();
    }
  };

  window.sellWizardBack = function () {
    if (currentStep > 1) {
      currentStep--;
      updateWizardStep();
    }
  };

  function canGoNext() {
    if (currentStep === 1) {
      if (!state.device) {
        showToast('warning', translate('sell-device-type'));
        return false;
      }
    }

    if (currentStep === 2) {
      if (!state.brand) {
        showToast('warning', translate('sell-select-brand'));
        return false;
      }
      if (!state.model) {
        showToast('warning', translate('sell-select-model'));
        return false;
      }
    }

    if (currentStep === 3) {
      if (
        !state.screen ||
        !state.function ||
        !state.cosmetic ||
        !state.battery ||
        !state.accessories ||
        !state.water ||
        !state.age
      ) {
        showToast('warning', translate('sell-step3-title'));
        return false;
      }
    }

    return true;
  }

  function updateWizardStep() {
    wizard.querySelectorAll('.sell-wizard__panel').forEach((panel, index) => {
      panel.classList.toggle('active', index === currentStep - 1);
    });

    wizard.querySelectorAll('.sell-wizard__step').forEach((step, index) => {
      step.classList.remove('active', 'completed');
      if (index + 1 === currentStep) step.classList.add('active');
      else if (index + 1 < currentStep) step.classList.add('completed');
    });

    if (currentStep === 4) {
      updateQuote();
    }
  }

  function filterModelsByBrand() {
    if (!modelOptions.length) return;
    modelOptions.forEach(option => {
      const brand = option.dataset.brand;
      if (!state.brand || !brand || brand === state.brand) {
        option.style.display = '';
      } else {
        option.style.display = 'none';
        option.classList.remove('selected');
        if (state.model === option.dataset.model) {
          state.model = null;
        }
      }
    });
  }

  function calculateQuote() {
    if (!state.device || !state.brand) return null;

    const basePrices = {
      smartphone: { apple: 620, samsung: 520, google: 480, oneplus: 450 },
      laptop: { apple: 950, samsung: 800, google: 0, oneplus: 0 },
      tablet: { apple: 520, samsung: 430, google: 0, oneplus: 0 },
      smartwatch: { apple: 260, samsung: 210, google: 0, oneplus: 0 }
    };

    let base = basePrices[state.device]?.[state.brand] || 200;

    // Model fine‑tuning
    if (state.model) {
      if (state.model.includes('15-pro')) base += 80;
      else if (state.model.includes('15')) base += 40;
      else if (state.model.includes('14-pro')) base += 50;
      else if (state.model.includes('14')) base += 20;
      else if (state.model.includes('13')) base -= 30;
    }

    let multiplier = 1;

    // Screen condition
    switch (state.screen) {
      case 'perfect': multiplier *= 1; break;
      case 'minor': multiplier *= 0.85; break;
      case 'cracked': multiplier *= 0.45; break;
    }

    // Functional state
    switch (state.function) {
      case 'yes': multiplier *= 1; break;
      case 'minor': multiplier *= 0.8; break;
      case 'no': multiplier *= 0.4; break;
    }

    // Cosmetic damage
    switch (state.cosmetic) {
      case 'none': multiplier *= 1; break;
      case 'light': multiplier *= 0.9; break;
      case 'heavy': multiplier *= 0.7; break;
    }

    // Battery health
    switch (state.battery) {
      case 'like-new': multiplier *= 1.05; break;
      case 'normal': multiplier *= 1; break;
      case 'weak': multiplier *= 0.75; break;
    }

    // Accessories / completeness
    switch (state.accessories) {
      case 'complete': multiplier *= 1.03; break;
      case 'no-charger': multiplier *= 0.95; break;
      case 'device-only': multiplier *= 0.9; break;
    }

    // Water damage
    if (state.water === 'yes') {
      multiplier *= 0.5;
    }

    // Age of device
    switch (state.age) {
      case 'lt1': multiplier *= 1.05; break;
      case '1-2': multiplier *= 1; break;
      case '2-3': multiplier *= 0.85; break;
      case 'gt3': multiplier *= 0.7; break;
    }

    let value = base * multiplier;
    if (!isFinite(value) || value <= 0) value = 20;
    value = Math.max(15, Math.round(value / 5) * 5);
    return value;
  }

  function updateQuote() {
    const amount = calculateQuote();
    const targetEl = quoteAmountEl || wizard.querySelector('.sell-wizard__quote-amount') || wizard.querySelector('.sell-wizard__quote-amount, .sell-wizard__quote-amount');
    if (!targetEl) return;
    if (amount == null) {
      targetEl.textContent = '€—';
    } else {
      targetEl.textContent = '€' + amount;
    }
  }

  // Device selection
  const deviceGrid = wizard.querySelector('.sell-wizard__device-grid');
  if (deviceGrid) {
    deviceGrid.querySelectorAll('.device-option').forEach(option => {
      option.addEventListener('click', () => {
        deviceGrid.querySelectorAll('.device-option').forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
        state.device = option.dataset.device || null;
      });
    });
  }

  // Brand selection
  const brandGrid = wizard.querySelector('.sell-wizard__brand-grid');
  if (brandGrid) {
    brandGrid.querySelectorAll('.device-option').forEach(option => {
      option.addEventListener('click', () => {
        brandGrid.querySelectorAll('.device-option').forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
        state.brand = option.dataset.brand || null;
        filterModelsByBrand();
      });
    });
  }

  // Model selection
  if (modelGrid) {
    modelOptions.forEach(option => {
      option.addEventListener('click', () => {
        modelGrid.querySelectorAll('.device-option').forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
        state.model = option.dataset.model || null;
      });
    });
  }

  // Condition & detail radios
  ['screen', 'function', 'cosmetic', 'battery', 'accessories', 'water', 'age'].forEach(name => {
    wizard.querySelectorAll(`input[name="${name}"]`).forEach(input => {
      input.addEventListener('change', () => {
        state[name] = input.value;
      });
    });
  });

  // Allow clicking steps to jump
  wizard.querySelectorAll('.sell-wizard__step').forEach(stepEl => {
    stepEl.addEventListener('click', () => {
      const stepNum = parseInt(stepEl.dataset.step, 10);
      if (!Number.isNaN(stepNum)) {
        currentStep = stepNum;
        updateWizardStep();
      }
    });
  });

  updateWizardStep();
}

/* ── Trade Calculator ── */
function initTradeCalculator() {
  const calcBtn = document.querySelector('[data-trade-calculate]');
  if (!calcBtn) return;

  calcBtn.addEventListener('click', () => {
    const tradeValue = Math.floor(Math.random() * 300) + 100;
    const desiredPrice = 899;
    const toPay = Math.max(0, desiredPrice - tradeValue);

    const valueEl = document.querySelector('.trade-value__amount');
    const toPayEl = document.querySelector('.trade-topay__amount');

    if (valueEl) valueEl.textContent = '€' + tradeValue;
    if (toPayEl) toPayEl.textContent = '€' + toPay;

    const resultSection = document.querySelector('.trade-result');
    if (resultSection) {
      resultSection.style.display = 'block';
      resultSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
}

/* ── Account Page (real auth/data) ── */
async function initAccountPage() {
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    /* login.html has its own complete inline handler — skip here to avoid
       double-submission and conflicts. Only check if PHP session is already
       active and silently redirect in that case. */
    try {
      const auth = await fetchJsonFromCandidates([
        'api/public/account_auth.php',
        `${getProjectBasePath()}api/public/account_auth.php`,
        '/api/public/account_auth.php'
      ]);
      if (auth.ok && auth.logged_in) {
        window.location.replace('account.html');
      }
    } catch (_) {}
    return;
  }

  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    /* register.html has its own inline handler — only check live session here */
    try {
      const auth = await fetchJsonFromCandidates([
        'api/public/account_auth.php',
        `${getProjectBasePath()}api/public/account_auth.php`,
        '/api/public/account_auth.php'
      ]);
      if (auth.ok && auth.logged_in) {
        window.location.replace('account.html');
      }
    } catch (_) {}
    return;
  }

  const dashboardView = document.getElementById('accountDashboardView');
  if (!dashboardView) return;

  function setAccountTab(tab) {
    document.querySelectorAll('[data-account-tab]').forEach((el) => {
      el.classList.toggle('active', el.dataset.accountTab === tab);
    });
    document.querySelectorAll('.account-pane').forEach((pane) => {
      pane.classList.toggle('active', pane.id === `account-pane-${tab}`);
    });
  }

  document.querySelectorAll('[data-account-tab]').forEach((el) => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      const tab = el.dataset.accountTab || 'dashboard';
      setAccountTab(tab);
      if (tab === 'wishlist') renderWishlistPane();
    });
  });

  /* Read cached customer from localStorage (set at login time) */
  let cachedCustomer = null;
  try {
    cachedCustomer = JSON.parse(
      localStorage.getItem('gsmstunter-customer') ||
      sessionStorage.getItem('gsmstunter-customer') || 'null'
    );
  } catch (_) {}

  /* Build the API URL — pass customer_id as hint so session can be restored */
  const baseUrl = 'api/public/account_data.php';
  const hintParam = (cachedCustomer && cachedCustomer.id)
    ? `?customer_id=${encodeURIComponent(cachedCustomer.id)}` : '';

  try {
    const basePath = getProjectBasePath ? getProjectBasePath() : '';
    const data = await fetchJsonFromCandidates([
      baseUrl + hintParam,
      `${basePath}${baseUrl}${hintParam}`,
      `/api/public/account_data.php${hintParam}`
    ]);

    if (!data.ok || !data.logged_in) {
      /* Clear stale localStorage so we don't keep looping */
      localStorage.removeItem('gsmstunter-customer');
      sessionStorage.removeItem('gsmstunter-customer');
      window.location.replace('login.html');
      return;
    }
    dashboardView.style.display = '';
    renderAccountData(data);
    /* honour URL hash: account.html#wishlist → open that tab */
    const hashTab = (window.location.hash || '').replace('#', '').toLowerCase();
    setAccountTab(['dashboard', 'orders', 'wishlist'].includes(hashTab) ? hashTab : 'dashboard');
    if (hashTab === 'wishlist') renderWishlistPane();
  } catch {
    localStorage.removeItem('gsmstunter-customer');
    sessionStorage.removeItem('gsmstunter-customer');
    window.location.replace('login.html');
    return;
  }

  const logoutBtn = document.getElementById('accountLogoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', async (e) => {
      e.preventDefault();
      await fetch('api/public/account_auth.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'logout'})
      });
      localStorage.removeItem('gsmstunter-customer');
      sessionStorage.removeItem('gsmstunter-customer');
      window.location.replace('login.html');
    });
  }
}

function renderAccountData(data) {
  const profile = data.profile || {};
  const orders = Array.isArray(data.orders) ? data.orders : [];
  const stats = data.stats || {};

  const name = profile.full_name || 'Customer';
  const initials = name.trim().split(/\s+/).slice(0, 2).map(x => x.charAt(0).toUpperCase()).join('') || 'U';
  const avatar = document.getElementById('accAvatar');
  const accName = document.getElementById('accName');
  const accEmail = document.getElementById('accEmail');
  const accWelcome = document.getElementById('accWelcome');
  if (avatar) avatar.textContent = initials;
  if (accName) accName.textContent = name;
  if (accEmail) accEmail.textContent = profile.email || '-';
  if (accWelcome) accWelcome.textContent = `Welkom terug, ${name.split(' ')[0]}!`;

  const totalOrders = document.getElementById('accTotalOrders');
  const totalSpent = document.getElementById('accTotalSpent');
  const wishlistCount = document.getElementById('accWishlistCount');
  if (totalOrders) totalOrders.textContent = String(stats.total_orders || 0);
  if (totalSpent) totalSpent.textContent = `€${Number(stats.total_spent || 0).toFixed(0)}`;
  if (wishlistCount) wishlistCount.textContent = String(stats.wishlist_count || 0);

  const body = document.getElementById('accountOrdersBody');
  if (body) {
    if (orders.length === 0) {
      body.innerHTML = `<tr><td colspan="5">Nog geen bestellingen geplaatst.</td></tr>`;
    } else {
      body.innerHTML = orders.map((o) => {
    const items = (o.items || []).map(i => `${i.product_name} x${i.quantity}`).join(', ');
    return `
      <tr>
        <td>${o.order_reference}</td>
        <td>${items || '-'}</td>
        <td>${o.created_at || ''}</td>
        <td><span class="badge badge--${o.status==='delivered'?'success':o.status==='cancelled'?'error':o.status==='shipped'?'info':'warning'}">${o.status || 'new'}</span></td>
        <td>€${Number(o.total_amount || 0).toFixed(2)}</td>
      </tr>
    `;
      }).join('');
    }
  }

  /* wishlist count badge */
  const wlCountBadge = document.getElementById('wlCountBadge');
  const wlCount = (Array.isArray(AppState.wishlist) ? AppState.wishlist : []).length;
  if (wishlistCount) wishlistCount.textContent = String(stats.wishlist_count || wlCount || 0);
  if (wlCountBadge) wlCountBadge.textContent = wlCount ? `(${wlCount})` : '';
}

/* ── Wishlist pane renderer (fetches real product data) ── */
async function renderWishlistPane() {
  const wishlistBody = document.getElementById('accountWishlistBody');
  if (!wishlistBody) return;

  const wishlist = Array.isArray(AppState.wishlist) ? AppState.wishlist : [];

  if (!wishlist.length) {
    wishlistBody.innerHTML = `
      <div style="grid-column:1/-1;text-align:center;padding:40px 20px;border:2px dashed #e5e7eb;border-radius:16px;background:#f9fafb">
        <i class="fas fa-heart" style="font-size:2.4rem;color:#e5e7eb;margin-bottom:12px;display:block"></i>
        <p style="font-size:1rem;font-weight:700;color:#374151;margin-bottom:6px">Je wishlist is leeg</p>
        <p style="font-size:.85rem;color:#6b7280;margin-bottom:14px">Voeg producten toe via het ♡-icoon op de productenpagina.</p>
        <a href="products.html" style="display:inline-block;background:#0d7c66;color:#fff;padding:10px 22px;border-radius:10px;font-weight:700;font-size:.85rem;text-decoration:none">Bekijk producten</a>
      </div>`;
    return;
  }

  /* Show loading */
  wishlistBody.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:28px;color:#9ca3af"><i class="fas fa-spinner fa-spin" style="font-size:1.4rem;margin-bottom:8px;display:block"></i>Producten laden…</div>`;

  /* Fetch all products and match */
  let allProducts = [];
  try {
    const basePath = (typeof getProjectBasePath === 'function') ? getProjectBasePath() : '';
    const candidates = [
      'api/public/products.php?lang=nl',
      `${basePath}api/public/products.php?lang=nl`,
      '/holanda-project/api/public/products.php?lang=nl'
    ];
    for (const url of candidates) {
      try {
        const r = await fetch(url);
        if (r.ok) { const d = await r.json(); allProducts = d.items || []; break; }
      } catch (_) {}
    }
  } catch (_) {}

  /* Map: sku → product, and p-{id} → product */
  const productMap = {};
  allProducts.forEach(p => {
    if (p.sku) productMap[p.sku] = p;
    productMap[`p-${p.id}`] = p;
  });

  const cards = wishlist.map((wid) => {
    const p = productMap[wid];
    if (!p) {
      return `
        <article class="wl-card">
          <div class="wl-card__img"><i class="fas fa-image wl-card__img-placeholder"></i></div>
          <div class="wl-card__body">
            <div class="wl-card__brand">—</div>
            <div class="wl-card__name">${wid}</div>
            <div class="wl-card__price">—</div>
          </div>
          <div class="wl-card__actions">
            <button class="wl-card__btn-remove" onclick="toggleWishlist('${wid}');renderWishlistPane()"><i class="fas fa-heart-crack"></i> Verwijderen</button>
          </div>
        </article>`;
    }
    const name = p.name || p.name_nl || `${p.brand} ${p.model}`;
    const price = p.price ? `€${Number(p.price).toFixed(2)}` : '—';
    const img = p.image_url
      ? `<img src="${p.image_url}" alt="${name}" loading="lazy">`
      : `<i class="fas fa-mobile-screen wl-card__img-placeholder"></i>`;
    const badge = p.condition_key ? `<span class="wl-card__badge">${p.condition_key.replace('_',' ')}</span>` : '';
    const storage = p.storage_label ? `<div class="wl-card__storage"><i class="fas fa-memory" style="font-size:.7rem"></i> ${p.storage_label}</div>` : '';
    const pid = p.sku || `p-${p.id}`;
    return `
      <article class="wl-card">
        <div class="wl-card__img">${img}${badge}</div>
        <div class="wl-card__body">
          <div class="wl-card__brand">${p.brand || ''}</div>
          <div class="wl-card__name">${name}</div>
          ${storage}
          <div class="wl-card__price">${price}</div>
        </div>
        <div class="wl-card__actions">
          <button class="wl-card__btn-cart" onclick="addToCart(${JSON.stringify(p)})"><i class="fas fa-cart-plus"></i> In winkelwagen</button>
          <button class="wl-card__btn-remove" title="Verwijder uit wishlist" onclick="toggleWishlist('${pid}');renderWishlistPane()"><i class="fas fa-heart-crack"></i></button>
        </div>
      </article>`;
  });

  wishlistBody.innerHTML = cards.join('');

  /* update count badge */
  const wlCountBadge = document.getElementById('wlCountBadge');
  if (wlCountBadge) wlCountBadge.textContent = `(${wishlist.length})`;
}

/* Cart page & checkout wizard: assets/js/cart.js + checkout-flow.js */

/* ── Form Validation ── */
function initFormValidation() {
  document.querySelectorAll('input[required]').forEach(input => {
    input.addEventListener('blur', () => {
      if (!input.value.trim()) {
        input.classList.add('error');
      } else {
        input.classList.remove('error');
      }
    });

    input.addEventListener('input', () => {
      input.classList.remove('error');
    });
  });
}

/* ── Newsletter Form ── */
function initNewsletter() {
  const form = document.querySelector('.newsletter__form');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const email = form.querySelector('input[type="email"]');
    if (email && email.value.trim()) {
      const msgs = {
        nl: 'Bedankt voor je aanmelding!',
        de: 'Danke für Ihre Anmeldung!',
        fr: 'Merci pour votre inscription !'
      };
      showToast('success', msgs[AppState.language] || msgs.nl, email.value);
      email.value = '';
    }
  });
}

/* ── Lazy Loading Images ── */
function initLazyLoading() {
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          if (img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
          }
          observer.unobserve(img);
        }
      });
    }, { rootMargin: '100px' });

    document.querySelectorAll('img[data-src]').forEach(img => observer.observe(img));
  }
}

/* ── Counter Animation ── */
function initCounterAnimations() {
  const counters = document.querySelectorAll('[data-counter]');
  if (!counters.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const target = parseInt(el.dataset.counter);
        const suffix = el.dataset.counterSuffix || '';
        const prefix = el.dataset.counterPrefix || '';
        const duration = 2000;
        const start = Date.now();

        const tick = () => {
          const elapsed = Date.now() - start;
          const progress = Math.min(elapsed / duration, 1);
          const eased = 1 - Math.pow(1 - progress, 3);
          const current = Math.floor(target * eased);
          el.textContent = prefix + current.toLocaleString() + suffix;

          if (progress < 1) requestAnimationFrame(tick);
        };

        tick();
        observer.unobserve(el);
      }
    });
  }, { threshold: 0.5 });

  counters.forEach(el => observer.observe(el));
}

/* ── Smooth Scroll for Anchor Links ── */
function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', (e) => {
      const target = document.querySelector(link.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
}

/* ── Filter Group Toggle ── */
function initFilterToggles() {
  document.querySelectorAll('.filter-group__header').forEach(header => {
    header.addEventListener('click', () => {
      const body = header.nextElementSibling;
      const icon = header.querySelector('.filter-group__toggle');
      if (body) {
        const isOpen = body.style.maxHeight && body.style.maxHeight !== '0px';
        body.style.maxHeight = isOpen ? '0px' : body.scrollHeight + 'px';
        if (icon) icon.classList.toggle('collapsed', isOpen);
      }
    });
  });
}

/* ── Mega Menu ── */
function initMegaMenu() {
  const triggers = document.querySelectorAll('[data-mega-menu]');

  triggers.forEach(trigger => {
    const menuId = trigger.dataset.megaMenu;
    const menu = document.getElementById(menuId);
    if (!menu) return;

    trigger.addEventListener('mouseenter', () => {
      menu.classList.add('active');
    });

    trigger.addEventListener('mouseleave', (e) => {
      if (!menu.contains(e.relatedTarget)) {
        menu.classList.remove('active');
      }
    });

    menu.addEventListener('mouseleave', () => {
      menu.classList.remove('active');
    });
  });
}

/* ── Dynamic Home Categories (from admin) ── */
async function initDynamicHomeCategories() {
  const grid = document.getElementById('homeCategoriesGrid');
  if (!grid) return;
  grid.innerHTML = '';

  try {
    const lang = AppState.language || 'nl';
    const data = await fetchJsonFromCandidates([
      `api/public/categories.php?lang=${encodeURIComponent(lang)}`,
      `${getProjectBasePath()}api/public/categories.php?lang=${encodeURIComponent(lang)}`,
      `/api/public/categories.php?lang=${encodeURIComponent(lang)}`
    ]);
    if (!data.ok || !Array.isArray(data.items) || data.items.length === 0) return;

    const fallbackImg = 'https://images.unsplash.com/photo-1591337676887-a217a6c8d2f4?w=200&h=200&fit=crop&q=80';
    const ctaText = translate('category-cta');
    const offerLabel = ctaText && ctaText !== 'category-cta' ? ctaText : 'Bekijk aanbod';

    // Deduplicate category rows by key and ensure accessories is present.
    const dedupedByKey = new Map();
    data.items.forEach(cat => {
      const key = String(cat.category_key || '').trim().toLowerCase();
      if (!key || dedupedByKey.has(key)) return;
      dedupedByKey.set(key, cat);
    });

    if (!dedupedByKey.has('accessories')) {
      dedupedByKey.set('accessories', {
        category_key: 'accessories',
        name: translate('cat-accessories'),
        image_url: fallbackImg
      });
    }

    const categoryNameByKey = {
      smartphones: translate('cat-smartphones'),
      laptops: translate('cat-laptops'),
      tablets: translate('cat-tablets'),
      smartwatches: translate('cat-smartwatches'),
      headphones: translate('cat-headphones'),
      accessories: translate('cat-accessories')
    };

    const categories = Array.from(dedupedByKey.values());
    grid.innerHTML = categories.map((cat, idx) => {
      const key = (cat.category_key || 'category').toLowerCase();
      const translatedName = categoryNameByKey[key];
      const name = cat.name || (translatedName && translatedName !== `cat-${key}` ? translatedName : '') || key || 'Category';
      const href = `products.html?category=${encodeURIComponent(key)}`;
      const img = cat.image_url || fallbackImg;
      return `
        <a href="${href}" class="category-card fade-in visible" style="transition-delay: ${Math.min(idx * 0.05, 0.25)}s;">
          <img class="category-card__image" src="${img}" alt="" aria-hidden="true" loading="lazy" onerror="this.style.display='none'">
          <h3 class="category-card__name">${name}</h3>
          <span class="category-card__count">${offerLabel}</span>
        </a>
      `;
    }).join('');
  } catch (err) {
    console.warn('Dynamic categories failed:', err);
    grid.setAttribute('data-dynamic-error', 'categories-api-failed');
    grid.innerHTML = '';
  }
}

async function initDynamicHomeFeaturedProducts() {
  const featuredSection = document.querySelector('[data-section-key="home.featured_products"]');
  const grid = featuredSection ? featuredSection.querySelector('.products-grid') : null;
  if (!grid) return;

  grid.innerHTML = `
    <div class="product-empty-state" style="grid-column:1/-1;padding:1.5rem;border:1px dashed var(--color-border);border-radius:var(--radius-xl);text-align:center;color:var(--color-text-secondary);background:var(--color-bg);">
      <i class="fas fa-spinner fa-spin" style="margin-inline-end:.5rem;"></i> Loading featured products...
    </div>
  `;

  try {
    const lang = AppState.language || 'nl';
    const data = await fetchJsonFromCandidates([
      `api/public/products.php?lang=${encodeURIComponent(lang)}`,
      `${getProjectBasePath()}api/public/products.php?lang=${encodeURIComponent(lang)}`,
      `/api/public/products.php?lang=${encodeURIComponent(lang)}`
    ]);
    if (!data.ok || !Array.isArray(data.items) || data.items.length === 0) {
      grid.innerHTML = '';
      return;
    }

    const rows = data.items.slice(0, 4);
    grid.innerHTML = rows.map((item, idx) => {
      const name = item.name || `${item.brand || ''} ${item.model || ''}`.trim() || 'Product';
      const price = Number(item.effective_price || item.price || 0);
      const oldPrice = Number(item.old_price || 0);
      const discount = oldPrice > price && oldPrice > 0 ? Math.round((1 - price / oldPrice) * 100) : 0;
      const image = item.image_url || 'https://images.unsplash.com/photo-1512054502232-10a0a035d672?w=400&h=400&fit=crop';
      const id = item.sku || `home-p-${item.id}`;
      const specs = [
        item.storage_label || '',
        item.color || '',
        item.ram_gb ? `${item.ram_gb}GB RAM` : ''
      ].filter(Boolean).join(' · ');

      return `
        <div class="product-card fade-in visible" style="transition-delay:${Math.min(idx * 0.08, 0.24)}s;">
          <button class="product-card__wishlist" data-wishlist-id="${id}" aria-label="Add to wishlist" onclick="toggleWishlist('${id}')">
            <i class="far fa-heart"></i>
          </button>
          <a href="product-detail.html?${item.sku ? `sku=${encodeURIComponent(item.sku)}` : `id=${encodeURIComponent(item.id)}`}" class="product-card__image-wrapper">
            <img class="product-card__image" src="${image}" alt="${name}" loading="lazy">
            <div class="product-card__quick-view"><button class="btn btn--sm btn--primary">Quick view</button></div>
          </a>
          <div class="product-card__body">
            <h3 class="product-card__title">${name}</h3>
            <p class="product-card__specs">${specs}</p>
            <div class="product-card__pricing">
              <span class="product-card__price">€${price.toFixed(0)}</span>
              ${oldPrice > 0 ? `<span class="product-card__price-original">€${oldPrice.toFixed(0)}</span>` : ''}
              ${discount > 0 ? `<span class="product-card__savings">-${discount}%</span>` : ''}
            </div>
            <div class="product-card__meta">
              <span class="product-card__warranty"><i class="fas fa-shield-halved"></i><span>2 jaar garantie</span></span>
              <button class="product-card__add-cart" aria-label="Add to cart" onclick="addToCart({id:'${id}',product_id:${Number(item.id || 0)},sku:'${(item.sku || '').replace(/'/g, "\\'")}',name:'${name.replace(/'/g, "\\'")}',price:${price.toFixed(2)},image:'${image}',condition:'',storage:'${item.storage_label || ''}'})">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>
        </div>
      `;
    }).join('');
    updateWishlistButtons();
  } catch (err) {
    console.warn('Dynamic featured products failed:', err);
    grid.innerHTML = '';
  }
}

/* ── Global section visibility from admin ── */
async function initDynamicSectionVisibility() {
  const cacheKey = 'gsmstunter-sections-cache-v1';
  try {
    const data = await fetchJsonFromCandidates([
      'api/public/sections.php',
      `${getProjectBasePath()}api/public/sections.php`,
      '/api/public/sections.php'
    ]);
    if (!data.ok || !data.sections) return;
    localStorage.setItem(cacheKey, JSON.stringify(data.sections));
    document.querySelectorAll('[data-section-key]').forEach(el => {
      const key = el.getAttribute('data-section-key');
      if (Object.prototype.hasOwnProperty.call(data.sections, key) && !data.sections[key]) {
        el.style.display = 'none';
      }
    });
  } catch (err) {
    console.warn('Section visibility failed:', err);
    try {
      const cached = JSON.parse(localStorage.getItem(cacheKey) || '{}');
      document.querySelectorAll('[data-section-key]').forEach(el => {
        const key = el.getAttribute('data-section-key');
        if (Object.prototype.hasOwnProperty.call(cached, key) && !cached[key]) {
          el.style.display = 'none';
        }
      });
    } catch (_) {}
  }
}

/* ── Dynamic products page from admin ── */
async function initDynamicProductsPage() {
  const grid = document.getElementById('products-grid');
  if (!grid) return;
  const countEl = document.querySelector('.products-toolbar__count span');

  grid.innerHTML = `
    <div class="product-empty-state" style="grid-column:1/-1;padding:2rem;border:1px dashed var(--color-border);border-radius:var(--radius-xl);text-align:center;color:var(--color-text-secondary);background:var(--color-bg);">
      <i class="fas fa-spinner fa-spin" style="margin-inline-end:.5rem;"></i> Loading products...
    </div>
  `;
  if (countEl) countEl.textContent = '0';

  try {
    const lang = AppState.language || 'nl';
    const params = new URLSearchParams(window.location.search);
    const category = (params.get('category') || '').toLowerCase();
    const data = await fetchJsonFromCandidates([
      `api/public/products.php?lang=${encodeURIComponent(lang)}`,
      `${getProjectBasePath()}api/public/products.php?lang=${encodeURIComponent(lang)}`,
      `/api/public/products.php?lang=${encodeURIComponent(lang)}`
    ]);
    if (!data.ok || !Array.isArray(data.items) || data.items.length === 0) {
      grid.innerHTML = `
        <div class="product-empty-state" style="grid-column:1/-1;padding:2rem;border:1px solid var(--color-border);border-radius:var(--radius-xl);text-align:center;color:var(--color-text-secondary);background:var(--color-bg);">
          <i class="fas fa-box-open" style="margin-inline-end:.5rem;"></i> No products found in database.
        </div>
      `;
      return;
    }

    const rows = data.items.filter(item => {
      if (!category) return true;
      return String(item.category_key || '').toLowerCase() === category;
    });

    renderDynamicFiltersFromProducts(rows);

    if (rows.length === 0) {
      grid.innerHTML = `
        <div class="product-empty-state" style="grid-column:1/-1;padding:2rem;border:1px solid var(--color-border);border-radius:var(--radius-xl);text-align:center;color:var(--color-text-secondary);background:var(--color-bg);">
          <i class="fas fa-filter" style="margin-inline-end:.5rem;"></i> No products match this category.
        </div>
      `;
      return;
    }

    grid.innerHTML = rows.map((item, idx) => {
      const name = item.name || `${item.brand || ''} ${item.model || ''}`.trim() || 'Product';
      const desc = item.short_description || `${item.storage_label || ''} ${item.color || ''}`.trim();
      const price = Number(item.effective_price || item.price || 0);
      const oldPrice = Number(item.old_price || 0);
      const discount = oldPrice > price && oldPrice > 0 ? Math.round((1 - price / oldPrice) * 100) : 0;
      const image = item.image_url || 'https://images.unsplash.com/photo-1512054502232-10a0a035d672?w=400&h=400&fit=crop';
      const id = item.sku || `p-${item.id}`;
      const storageValue = String(item.storage_label || '').toLowerCase().replace(/gb|tb|\s/g, '');
      const specs = [
        desc || '',
        item.ram_gb ? `${item.ram_gb}GB RAM` : '',
        item.camera_mp ? `${item.camera_mp}MP` : '',
        item.battery_mah ? `${item.battery_mah}mAh` : ''
      ].filter(Boolean).join(' • ');
      return `
        <article class="product-card fade-in visible"
          data-brand="${String(item.brand || '').toLowerCase()}"
          data-condition="${String(item.condition_key || '').toLowerCase()}"
          data-storage="${storageValue}"
          data-color="${String(item.color || '').toLowerCase()}"
          data-os="${String(item.product_type || '').toLowerCase().includes('iphone') || String(item.brand || '').toLowerCase() === 'apple' ? 'ios' : 'android'}"
          data-ram="${String(item.ram_gb || '')}"
          data-camera="${String(item.camera_mp || '')}"
          data-battery="${String(item.battery_mah || '')}"
          data-screen="${String(item.screen_size_in || '')}"
          style="transition-delay:${Math.min(idx*0.03,0.25)}s;">
          <button class="product-card__wishlist" data-wishlist-id="${id}" aria-label="Add to wishlist" onclick="toggleWishlist('${id}')">
            <i class="far fa-heart"></i>
          </button>
          <a href="product-detail.html?${item.sku ? `sku=${encodeURIComponent(item.sku)}` : `id=${encodeURIComponent(item.id)}`}" class="product-card__image-wrapper">
            <img class="product-card__image" src="${image}" alt="${name}" loading="lazy">
            <div class="product-card__quick-view"><button class="btn btn--sm btn--primary">Quick view</button></div>
          </a>
          <div class="product-card__body">
            <h3 class="product-card__title">${name}</h3>
            <p class="product-card__specs">${specs}</p>
            <div class="product-card__pricing">
              <span class="product-card__price">€${price.toFixed(0)}</span>
              ${oldPrice > 0 ? `<span class="product-card__price-original">€${oldPrice.toFixed(0)}</span>` : ''}
              ${discount > 0 ? `<span class="product-card__savings">-${discount}%</span>` : ''}
            </div>
            <div class="product-card__meta">
              <span class="product-card__warranty"><i class="fas fa-shield-halved"></i><span>2 jaar garantie</span></span>
              <button class="product-card__add-cart" aria-label="Add to cart" onclick="addToCart({id:'${id}',product_id:${Number(item.id || 0)},sku:'${(item.sku || '').replace(/'/g, "\\'")}',name:'${name.replace(/'/g, "\\'")}',price:${price.toFixed(2)},image:'${image}',condition:'',storage:'${item.storage_label || ''}'})">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>
        </article>
      `;
    }).join('');

    if (countEl) countEl.textContent = rows.length;
    window.__allProductsData = rows;
    updateWishlistButtons();
    applyFilters();
    initProductsPageSearch();
  } catch (err) {
    console.warn('Dynamic products failed:', err);
    grid.innerHTML = `
      <div class="product-empty-state" style="grid-column:1/-1;padding:2rem;border:1px solid var(--color-error-light);border-radius:var(--radius-xl);text-align:center;color:var(--color-text-secondary);background:#fff;">
        <i class="fas fa-triangle-exclamation" style="margin-inline-end:.5rem;color:var(--color-error);"></i>
        Failed to load products from API.
      </div>
    `;
  }
}

function renderDynamicFiltersFromProducts(rows) {
  const groups = {
    brand: { bodyId: 'filter-brand-body', map: new Map(), label: (v) => v || '-' },
    condition: { bodyId: 'filter-condition-body', map: new Map(), label: (v) => formatConditionLabel(v) },
    storage: { bodyId: 'filter-storage-body', map: new Map(), label: (v) => normalizeStorageLabel(v) },
    color: { bodyId: 'filter-color-body', map: new Map(), label: (v) => capitalize(v) },
    os: { bodyId: 'filter-os-body', map: new Map(), label: (v) => v === 'ios' ? 'iOS' : 'Android' }
  };

  rows.forEach((item) => {
    const brand = String(item.brand || '').trim().toLowerCase();
    const condition = String(item.condition_key || '').trim().toLowerCase();
    const storage = String(item.storage_label || '').trim().toLowerCase();
    const color = String(item.color || '').trim().toLowerCase();
    const os = (String(item.product_type || '').toLowerCase().includes('iphone') || String(item.brand || '').toLowerCase() === 'apple') ? 'ios' : 'android';

    if (brand) groups.brand.map.set(brand, (groups.brand.map.get(brand) || 0) + 1);
    if (condition) groups.condition.map.set(condition, (groups.condition.map.get(condition) || 0) + 1);
    if (storage) groups.storage.map.set(storage, (groups.storage.map.get(storage) || 0) + 1);
    if (color) groups.color.map.set(color, (groups.color.map.get(color) || 0) + 1);
    groups.os.map.set(os, (groups.os.map.get(os) || 0) + 1);
  });

  Object.keys(groups).forEach((groupKey) => {
    const group = groups[groupKey];
    const body = document.getElementById(group.bodyId);
    if (!body) return;
    const entries = Array.from(group.map.entries()).sort((a, b) => b[1] - a[1]);
    if (entries.length === 0) {
      body.innerHTML = `<div class="muted">No options</div>`;
      return;
    }
    body.innerHTML = entries.map(([value, count]) => `
      <label class="filter-option">
        <input type="checkbox" data-filter-group="${groupKey}" value="${escapeHtmlAttr(value)}">
        <span>${escapeHtml(group.label(value))}</span>
        <span class="filter-option__count">(${count})</span>
      </label>
    `).join('');
  });

  initProductFilters();
}

function normalizeStorageLabel(raw) {
  const v = String(raw || '').trim().toLowerCase();
  if (!v) return '-';
  if (v.includes('tb')) return v.toUpperCase().replace(/\s+/g, '');
  const num = v.replace(/[^\d]/g, '');
  return num ? `${num}GB` : v.toUpperCase();
}

function formatConditionLabel(v) {
  const map = {
    'like-new': 'Als nieuw',
    'excellent': 'Uitstekend',
    'good': 'Goed',
    'fair': 'Redelijk'
  };
  return map[v] || capitalize(v.replace(/-/g, ' '));
}

function capitalize(v) {
  const s = String(v || '').trim();
  if (!s) return '';
  return s.charAt(0).toUpperCase() + s.slice(1);
}

function escapeHtml(s) {
  return String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function escapeHtmlAttr(s) {
  return escapeHtml(s);
}

async function initDynamicProductViewSettings() {
  const productsLayout = document.querySelector('.products-layout');
  if (!productsLayout) return;
  try {
    const data = await fetchJsonFromCandidates([
      'api/public/view_settings.php',
      `${getProjectBasePath()}api/public/view_settings.php`,
      '/api/public/view_settings.php'
    ]);
    if (!data.ok || !data.settings) return;
    const settings = data.settings;

    const filterSidebar = document.querySelector('.filters-sidebar');
    const sortWrap = document.querySelector('.products-toolbar__sort');
    const grid = document.getElementById('products-grid');
    const viewButtons = document.querySelectorAll('.view-toggle__btn');

    if (filterSidebar) {
      filterSidebar.style.display = Number(settings.show_filters) === 1 ? '' : 'none';
    }
    if (sortWrap) {
      sortWrap.style.display = Number(settings.show_sort) === 1 ? '' : 'none';
    }
    if (Number(settings.show_filters) !== 1 && productsLayout) {
      productsLayout.style.gridTemplateColumns = '1fr';
    }
    if (grid && Number(settings.items_per_page) > 0) {
      grid.dataset.pageSize = String(Number(settings.items_per_page));
    }
    if (settings.default_view_mode === 'list' && grid) {
      grid.classList.add('products-grid--list');
      viewButtons.forEach(btn => {
        btn.classList.toggle('active', btn.dataset.view === 'list');
      });
    }
  } catch (err) {
    console.warn('View settings failed:', err);
  }
}

function getProjectBasePath() {
  const path = window.location.pathname || '/';
  const parts = path.split('/').filter(Boolean);
  if (parts.length === 0) return '/';
  return '/' + parts[0] + '/';
}

async function fetchJsonFromCandidates(urls) {
  let lastError = null;
  for (const url of urls) {
    try {
      const res = await fetch(url, { credentials: 'same-origin' });
      if (!res.ok) continue;
      const text = await res.text();
      try {
        const json = JSON.parse(text);
        return json;
      } catch {
        continue;
      }
    } catch (err) {
      lastError = err;
    }
  }
  throw lastError || new Error('No API candidate responded with valid JSON');
}

/* ── Initialize All ── */
document.addEventListener('DOMContentLoaded', () => {
  applyTranslations();
  updateLangSwitcher();
  updateCartCount();
  updateWishlistButtons();

  initStickyHeader();
  initMobileMenu();
  initLangDropdown();
  initSearch();
  initScrollAnimations();
  initAccordions();
  initTabs();
  initModals();
  initMegaMenu();
  initDynamicSectionVisibility();
  initDynamicHomeCategories();
  initDynamicHomeFeaturedProducts();
  initDynamicProductViewSettings();
  initDynamicProductsPage();
  initSmoothScroll();
  initLazyLoading();
  initCounterAnimations();
  initFormValidation();
  initNewsletter();

  initProductFilters();
  initViewToggle();
  initFilterToggles();
  initProductGallery();
  initDynamicProductDetailPage();
  initConditionSelector();
  initStorageSelector();
  initQuantitySelectors();
  initSellWizard();
  initTradeCalculator();
  initCartPage();
  initAccountPage();
});
