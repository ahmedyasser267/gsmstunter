/**
 * Customer session helpers (client mirror of server session).
 */
(function (global) {
  function getStoredCustomer() {
    try {
      return JSON.parse(
        localStorage.getItem('gsmstunter-customer') ||
          sessionStorage.getItem('gsmstunter-customer') ||
          'null'
      );
    } catch (_) {
      return null;
    }
  }

  function isCustomerLoggedIn() {
    var c = getStoredCustomer();
    return !!(c && c.id && c.email);
  }

  global.getStoredCustomer = getStoredCustomer;
  global.isCustomerLoggedIn = isCustomerLoggedIn;

  global.GsmAuth = {
    getStoredCustomer: getStoredCustomer,
    isLoggedIn: isCustomerLoggedIn
  };
})(typeof window !== 'undefined' ? window : globalThis);
