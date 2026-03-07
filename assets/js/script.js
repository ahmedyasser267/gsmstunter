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

/* ── App State ── */
(function() {
  var stored = localStorage.getItem('gsmstunter-lang');
  if (!stored || ['nl','de','fr'].indexOf(stored) === -1) {
    localStorage.setItem('gsmstunter-lang', 'nl');
  }
})();

const AppState = {
  language: localStorage.getItem('gsmstunter-lang') || 'nl',
  cart: JSON.parse(localStorage.getItem('gsmstunter-cart') || '[]'),
  wishlist: JSON.parse(localStorage.getItem('gsmstunter-wishlist') || '[]'),
  viewMode: 'grid',
  currentPage: 1,
  filters: {
    brand: [],
    condition: [],
    priceMin: 0,
    priceMax: 2000,
    storage: [],
    color: [],
    os: []
  }
};

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

/* ── Cart Management ── */
function addToCart(product) {
  const qty = product.quantity || 1;
  const existing = AppState.cart.find(item => item.id === product.id && item.condition === product.condition && item.storage === product.storage);
  if (existing) {
    existing.quantity = (existing.quantity || 1) + qty;
  } else {
    AppState.cart.push({ ...product, quantity: qty });
  }
  saveCart();
  updateCartCount();
  showToast('success', translate('added-to-cart'), product.name);
}

function removeFromCart(index) {
  AppState.cart.splice(index, 1);
  saveCart();
  updateCartCount();
  showToast('info', translate('removed-from-cart'));
}

function updateCartQuantity(index, quantity) {
  if (quantity <= 0) {
    removeFromCart(index);
    return;
  }
  AppState.cart[index].quantity = quantity;
  saveCart();
  updateCartCount();
}

function saveCart() {
  localStorage.setItem('gsmstunter-cart', JSON.stringify(AppState.cart));
}

function getCartTotal() {
  return AppState.cart.reduce((sum, item) => sum + (item.price * (item.quantity || 1)), 0);
}

function getCartItemCount() {
  return AppState.cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
}

function updateCartCount() {
  document.querySelectorAll('.cart-count').forEach(el => {
    const count = getCartItemCount();
    el.textContent = count;
    el.style.display = count > 0 ? 'flex' : 'none';
  });
}

/* ── Wishlist ── */
function toggleWishlist(productId) {
  const index = AppState.wishlist.indexOf(productId);
  if (index > -1) {
    AppState.wishlist.splice(index, 1);
  } else {
    AppState.wishlist.push(productId);
  }
  localStorage.setItem('gsmstunter-wishlist', JSON.stringify(AppState.wishlist));
  updateWishlistButtons();
}

function updateWishlistButtons() {
  document.querySelectorAll('[data-wishlist-id]').forEach(btn => {
    const id = btn.dataset.wishlistId;
    const isWished = AppState.wishlist.includes(id);
    btn.classList.toggle('active', isWished);
    const icon = btn.querySelector('i');
    if (icon) {
      icon.className = isWished ? 'fas fa-heart' : 'far fa-heart';
    }
  });
}

/* ── Toast Notifications ── */
function showToast(type = 'success', title, message = '') {
  const container = document.querySelector('.toast-container') || createToastContainer();

  const iconMap = {
    success: 'fas fa-check-circle',
    error: 'fas fa-exclamation-circle',
    warning: 'fas fa-exclamation-triangle',
    info: 'fas fa-info-circle'
  };

  const colorMap = {
    success: 'var(--color-success)',
    error: 'var(--color-error)',
    warning: 'var(--color-warning)',
    info: 'var(--color-info)'
  };

  const toast = document.createElement('div');
  toast.className = `toast toast--${type}`;
  toast.innerHTML = `
    <i class="${iconMap[type]} toast__icon" style="color: ${colorMap[type]}"></i>
    <div class="toast__content">
      <div class="toast__title">${title}</div>
      ${message ? `<div class="toast__message">${message}</div>` : ''}
    </div>
    <button class="toast__close" onclick="this.closest('.toast').remove()">
      <i class="fas fa-times"></i>
    </button>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

function createToastContainer() {
  const container = document.createElement('div');
  container.className = 'toast-container';
  document.body.appendChild(container);
  return container;
}

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

/* ── Search Autocomplete ── */
function initSearch() {
  const input = document.querySelector('.search-bar__input');
  const dropdown = document.querySelector('.search-autocomplete');
  if (!input || !dropdown) return;

  const products = [
    { name: 'iPhone 15 Pro Max', category: 'Smartphones', img: 'https://images.unsplash.com/photo-1591337676887-a217a6c8d2f4?w=80&h=80&fit=crop' },
    { name: 'iPhone 14 Pro', category: 'Smartphones', img: 'https://images.unsplash.com/photo-1580910051074-3eb694886f8b?w=80&h=80&fit=crop' },
    { name: 'Samsung Galaxy S24', category: 'Smartphones', img: 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=80&h=80&fit=crop' },
    { name: 'MacBook Pro M3', category: 'Laptops', img: 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=80&h=80&fit=crop' },
    { name: 'iPad Pro 12.9"', category: 'Tablets', img: 'https://images.unsplash.com/photo-1585790050230-5dd28404ccb9?w=80&h=80&fit=crop' },
    { name: 'Apple Watch Series 9', category: 'Smartwatches', img: 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=80&h=80&fit=crop' },
    { name: 'AirPods Pro', category: 'Headphones', img: 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=80&h=80&fit=crop' }
  ];

  input.addEventListener('input', () => {
    const query = input.value.toLowerCase().trim();
    if (query.length < 2) {
      dropdown.classList.remove('active');
      return;
    }

    const matches = products.filter(p =>
      p.name.toLowerCase().includes(query) || p.category.toLowerCase().includes(query)
    ).slice(0, 5);

    if (matches.length === 0) {
      dropdown.classList.remove('active');
      return;
    }

    dropdown.innerHTML = matches.map(p => `
      <a href="products.html" class="search-autocomplete__item">
        <img src="${p.img}" alt="${p.name}" loading="lazy">
        <div>
          <div style="font-weight: 500; font-size: 0.875rem;">${p.name}</div>
          <div style="font-size: 0.75rem; color: var(--color-text-muted);">${p.category}</div>
        </div>
      </a>
    `).join('');

    dropdown.classList.add('active');
  });

  input.addEventListener('focus', () => {
    if (input.value.length >= 2) dropdown.classList.add('active');
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.search-bar')) {
      dropdown.classList.remove('active');
    }
  });
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
    cb.addEventListener('change', applyFilters);
  });

  if (sortSelect) {
    sortSelect.addEventListener('change', applyFilters);
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
      if (!filterValues.includes(cardValue)) {
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
  const priceEl = document.querySelector('.product-info__price, .product-detail__price');
  const conditionOpt = document.querySelector('.condition-option input:checked');
  const storageOpt = document.querySelector('.storage-option input:checked');
  const qtyEl = document.querySelector('.quantity-selector__value');
  const price = priceEl ? parseInt(priceEl.textContent.replace(/[^\d]/g, '')) : 899;
  const condition = conditionOpt ? conditionOpt.closest('.condition-option').querySelector('.condition-option__text')?.textContent || 'Als nieuw' : 'Als nieuw';
  const storage = storageOpt ? (storageOpt.value === '1024' ? '1TB' : storageOpt.value + 'GB') : '128GB';
  const quantity = qtyEl ? parseInt(qtyEl.textContent) || 1 : 1;
  addToCart({
    id: 'iphone15pro',
    name: 'iPhone 15 Pro',
    price,
    image: 'https://images.unsplash.com/photo-1591337676887-a217a6c8d2f4?w=200&h=200&fit=crop',
    condition,
    storage,
    quantity
  });
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

/* ── Cart Page ── */
function initCartPage() {
  if (!document.querySelector('.cart-layout')) return;
  renderCart();
}

function renderCart() {
  const cartItems = document.querySelector('.cart-items');
  const summaryEl = document.querySelector('.cart-summary');
  if (!cartItems) return;

  if (AppState.cart.length === 0) {
    cartItems.innerHTML = `
      <div style="text-align: center; padding: 4rem 2rem;">
        <i class="fas fa-shopping-cart" style="font-size: 3rem; color: var(--color-text-muted); margin-bottom: 1rem;"></i>
        <h3 data-i18n="cart-empty">${translate('cart-empty')}</h3>
        <a href="products.html" class="btn btn--primary" style="margin-top: 1rem;" data-i18n="cart-continue">${translate('cart-continue')}</a>
      </div>
    `;
    return;
  }

  cartItems.innerHTML = AppState.cart.map((item, index) => `
    <div class="cart-item">
      <div class="cart-item__image">
        <img src="${item.image}" alt="${item.name}" loading="lazy">
      </div>
      <div class="cart-item__details">
        <h3 class="cart-item__title">${item.name}</h3>
        <p class="cart-item__specs">${item.condition || ''} · ${item.storage || ''}</p>
        <div class="cart-item__actions">
          <div class="quantity-selector">
            <button class="quantity-selector__btn" onclick="updateCartQuantity(${index}, ${(item.quantity || 1) - 1})">−</button>
            <span class="quantity-selector__value">${item.quantity || 1}</span>
            <button class="quantity-selector__btn" onclick="updateCartQuantity(${index}, ${(item.quantity || 1) + 1})">+</button>
          </div>
          <button class="cart-item__remove" onclick="removeFromCart(${index}); renderCart();">
            <i class="fas fa-trash-alt"></i>
          </button>
        </div>
      </div>
      <div class="cart-item__price">€${(item.price * (item.quantity || 1)).toFixed(2)}</div>
    </div>
  `).join('');

  if (summaryEl) {
    const subtotal = getCartTotal();
    const shipping = subtotal >= 50 ? 0 : 4.95;
    const tax = subtotal * 0.21;
    const total = subtotal + shipping;

    summaryEl.querySelector('.summary-subtotal').textContent = '€' + subtotal.toFixed(2);
    summaryEl.querySelector('.summary-shipping').textContent = shipping === 0 ? translate('cart-shipping-free') : '€' + shipping.toFixed(2);
    summaryEl.querySelector('.summary-tax').textContent = '€' + tax.toFixed(2);
    summaryEl.querySelector('.summary-total').textContent = '€' + total.toFixed(2);
  }
}

/* ── Checkout ── */
function initCheckout() {
  const form = document.querySelector('.checkout-form');
  if (!form) return;

  let step = 1;
  const totalSteps = 4;

  window.checkoutNext = function () {
    if (validateCheckoutStep(step)) {
      if (step < totalSteps) {
        step++;
        updateCheckoutStep(step);
      }
    }
  };

  window.checkoutBack = function () {
    if (step > 1) {
      step--;
      updateCheckoutStep(step);
    }
  };

  function updateCheckoutStep(s) {
    form.querySelectorAll('.checkout-section').forEach((section, i) => {
      section.style.display = i === s - 1 ? 'block' : 'none';
    });

    document.querySelectorAll('.checkout-step').forEach((el, i) => {
      el.classList.remove('active', 'completed');
      if (i + 1 === s) el.classList.add('active');
      else if (i + 1 < s) el.classList.add('completed');
    });
  }

  function validateCheckoutStep(s) {
    const section = form.querySelectorAll('.checkout-section')[s - 1];
    if (!section) return true;

    const required = section.querySelectorAll('input[required]');
    let valid = true;

    required.forEach(input => {
      if (!input.value.trim()) {
        input.classList.add('error');
        valid = false;
      } else {
        input.classList.remove('error');
      }
    });

    return valid;
  }
}

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
  initSmoothScroll();
  initLazyLoading();
  initCounterAnimations();
  initFormValidation();
  initNewsletter();

  initProductFilters();
  initViewToggle();
  initFilterToggles();
  initProductGallery();
  initConditionSelector();
  initStorageSelector();
  initQuantitySelectors();
  initSellWizard();
  initTradeCalculator();
  initCartPage();
  initCheckout();
});
