/**
 * Shared application state (loaded before script.js).
 */
(function () {
  var stored = localStorage.getItem('gsmstunter-lang');
  if (!stored || ['nl', 'de', 'fr'].indexOf(stored) === -1) {
    localStorage.setItem('gsmstunter-lang', 'nl');
  }

  window.AppState = {
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
})();
