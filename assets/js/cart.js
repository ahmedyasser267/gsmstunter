/**
 * Cart & wishlist (requires: state.js, ui.js, auth.js, script.js for translate()).
 */
(function (global) {
  function translate(key) {
    if (typeof global.translate === 'function') return global.translate(key);
    return key;
  }

  function showToast(type, title, msg) {
    if (typeof global.showToast === 'function') global.showToast(type, title, msg);
  }

  function addToCart(product) {
    if (!global.isCustomerLoggedIn()) {
      showToast('warning', translate('login-required-title'), translate('login-required-body'));
      global.setTimeout(function () {
        global.location.href = 'login.html';
      }, 650);
      return;
    }
    var qty = product.quantity || 1;
    var existing = global.AppState.cart.find(function (item) {
      return (
        item.id === product.id &&
        item.condition === product.condition &&
        item.storage === product.storage
      );
    });
    if (existing) {
      existing.quantity = (existing.quantity || 1) + qty;
    } else {
      global.AppState.cart.push(Object.assign({}, product, { quantity: qty }));
    }
    saveCart();
    updateCartCount();
    showToast('success', translate('added-to-cart'), product.name || '');
  }

  function removeFromCart(index) {
    global.AppState.cart.splice(index, 1);
    saveCart();
    updateCartCount();
    showToast('info', translate('removed-from-cart'), '');
  }

  function updateCartQuantity(index, quantity) {
    if (quantity <= 0) {
      removeFromCart(index);
      return;
    }
    global.AppState.cart[index].quantity = quantity;
    saveCart();
    updateCartCount();
  }

  function saveCart() {
    localStorage.setItem('gsmstunter-cart', JSON.stringify(global.AppState.cart));
  }

  function getCartTotal() {
    return global.AppState.cart.reduce(function (sum, item) {
      return sum + item.price * (item.quantity || 1);
    }, 0);
  }

  function getCartItemCount() {
    return global.AppState.cart.reduce(function (sum, item) {
      return sum + (item.quantity || 1);
    }, 0);
  }

  function updateCartCount() {
    document.querySelectorAll('.cart-count').forEach(function (el) {
      var count = getCartItemCount();
      el.textContent = count;
      el.style.display = count > 0 ? 'flex' : 'none';
    });
  }

  function toggleWishlist(productId) {
    var idx = global.AppState.wishlist.indexOf(productId);
    if (idx > -1) {
      global.AppState.wishlist.splice(idx, 1);
    } else {
      global.AppState.wishlist.push(productId);
    }
    localStorage.setItem('gsmstunter-wishlist', JSON.stringify(global.AppState.wishlist));
    updateWishlistButtons();
  }

  function updateWishlistButtons() {
    document.querySelectorAll('[data-wishlist-id]').forEach(function (btn) {
      var id = btn.dataset.wishlistId;
      var isWished = global.AppState.wishlist.includes(id);
      btn.classList.toggle('active', isWished);
      var icon = btn.querySelector('i');
      if (icon) {
        icon.className = isWished ? 'fas fa-heart' : 'far fa-heart';
      }
    });
  }

  function renderCart() {
    var cartItems = document.querySelector('.cart-items');
    var summaryEl = document.querySelector('.cart-summary');
    if (!cartItems) return;

    if (global.AppState.cart.length === 0) {
      cartItems.innerHTML =
        '<div class="gsm-empty gsm-empty--cart">' +
        '<div class="gsm-empty__icon" aria-hidden="true"><i class="fas fa-shopping-bag"></i></div>' +
        '<h3 class="gsm-empty__title" data-i18n="cart-empty">' +
        translate('cart-empty') +
        '</h3>' +
        '<p class="gsm-empty__text">' +
        translate('cart-empty-hint') +
        '</p>' +
        '<a href="products.html" class="btn btn--primary gsm-empty__cta" data-i18n="cart-continue">' +
        translate('cart-continue') +
        '</a>' +
        '</div>';
      if (summaryEl) summaryEl.hidden = true;
      var hint = document.querySelector('.cart-empty-hint');
      if (hint) hint.hidden = true;
      return;
    }

    if (summaryEl) summaryEl.hidden = false;
    var hintEl = document.querySelector('.cart-empty-hint');
    if (hintEl) hintEl.hidden = false;

    cartItems.innerHTML = global.AppState.cart
      .map(function (item, index) {
        var qty = item.quantity || 1;
        var safeName = global.GsmUI ? global.GsmUI.escapeHtml(item.name || '') : String(item.name || '').replace(/</g, '');
        var imgSrc = item.image ? String(item.image).replace(/"/g, '&quot;') : '';
        return (
          '<div class="cart-item">' +
          '<div class="cart-item__image">' +
          (item.image
            ? '<img src="' +
              imgSrc +
              '" alt="' +
              safeName +
              '" loading="lazy">'
            : '<div class="cart-item__placeholder"><i class="fas fa-mobile-alt"></i></div>') +
          '</div>' +
          '<div class="cart-item__details">' +
          '<h3 class="cart-item__title">' +
          safeName +
          '</h3>' +
          '<p class="cart-item__specs">' +
          (item.condition || '') +
          ' · ' +
          (item.storage || '') +
          '</p>' +
          '<div class="cart-item__actions">' +
          '<div class="quantity-selector">' +
          '<button type="button" class="quantity-selector__btn" aria-label="-1" onclick="updateCartQuantity(' +
          index +
          ', ' +
          (qty - 1) +
          ')">−</button>' +
          '<span class="quantity-selector__value">' +
          qty +
          '</span>' +
          '<button type="button" class="quantity-selector__btn" aria-label="+1" onclick="updateCartQuantity(' +
          index +
          ', ' +
          (qty + 1) +
          ')">+</button>' +
          '</div>' +
          '<button type="button" class="cart-item__remove" onclick="removeFromCart(' +
          index +
          '); renderCart();" aria-label="' +
          translate('cart-remove-line') +
          '">' +
          '<i class="fas fa-trash-alt" aria-hidden="true"></i>' +
          '</button>' +
          '</div>' +
          '</div>' +
          '<div class="cart-item__price">€' +
          (item.price * qty).toFixed(2) +
          '</div>' +
          '</div>'
        );
      })
      .join('');

    if (summaryEl) {
      var subtotal = getCartTotal();
      var shipping = 0;
      var tax = subtotal * 0.21;
      var total = subtotal + shipping;

      var subtotalEl = document.getElementById('cartSubtotal');
      var shippingEl = document.getElementById('cartShipping');
      var taxEl = document.getElementById('cartTax');
      var totalEl = document.getElementById('cartTotal');
      if (subtotalEl) subtotalEl.textContent = '€' + subtotal.toFixed(2);
      if (shippingEl)
        shippingEl.textContent =
          shipping === 0 ? translate('cart-shipping-free') : '€' + shipping.toFixed(2);
      if (taxEl) taxEl.textContent = '€' + tax.toFixed(2);
      if (totalEl) totalEl.textContent = '€' + total.toFixed(2);
    }
  }

  function initCartPage() {
    if (!document.querySelector('.cart-layout')) return;
    if (!global.isCustomerLoggedIn()) {
      localStorage.removeItem('gsmstunter-cart');
      global.location.replace('login.html');
      return;
    }
    renderCart();
    syncCartSnapshot();
  }

  async function syncCartSnapshot() {
    try {
      var emailInput = document.getElementById('checkout-email');
      var email = emailInput ? emailInput.value.trim() : '';
      var res = await fetch('api/public/cart_snapshot.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          customer_email: email || null,
          items: global.AppState.cart || [],
          currency: 'EUR'
        })
      });
      if (!res.ok) return;
      var body = await res.json().catch(function () {
        return null;
      });
      if (!body || !body.ok) return;
    } catch (e) {
      console.warn('cart snapshot failed', e);
    }
  }

  global.addToCart = addToCart;
  global.removeFromCart = removeFromCart;
  global.updateCartQuantity = updateCartQuantity;
  global.saveCart = saveCart;
  global.getCartTotal = getCartTotal;
  global.getCartItemCount = getCartItemCount;
  global.updateCartCount = updateCartCount;
  global.toggleWishlist = toggleWishlist;
  global.updateWishlistButtons = updateWishlistButtons;
  global.renderCart = renderCart;
  global.initCartPage = initCartPage;
  global.syncCartSnapshot = syncCartSnapshot;

  global.GsmCart = {
    addToCart: addToCart,
    removeFromCart: removeFromCart,
    updateQuantity: updateCartQuantity,
    renderCart: renderCart,
    initCartPage: initCartPage,
    syncCartSnapshot: syncCartSnapshot
  };
})(typeof window !== 'undefined' ? window : globalThis);
