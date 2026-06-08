/**
 * Checkout wizard (checkout.html). Depends on: state.js, ui.js, auth.js, script.js (translate).
 */
(function (global) {
  function tr(key) {
    return typeof global.translate === 'function' ? global.translate(key) : key;
  }

  function esc(s) {
    return global.GsmUI ? global.GsmUI.escapeHtml(String(s ?? '')) : String(s ?? '');
  }

  function readCart() {
    try {
      return JSON.parse(localStorage.getItem('gsmstunter-cart') || '[]');
    } catch (_) {
      return [];
    }
  }

  var currentStep = 1;
  var SECTIONS = ['contact', 'shipping', 'payment', 'review'];

  function getStoredCustomer() {
    return typeof global.getStoredCustomer === 'function'
      ? global.getStoredCustomer()
      : null;
  }

  /* ── Guard ── */
  var customer = getStoredCustomer();
  if (!customer || !customer.id || !customer.email) {
    localStorage.removeItem('gsmstunter-cart');
    global.location.replace('login.html');
    return;
  }

  function showSummarySkeleton(show) {
    var container = document.getElementById('checkoutSummaryItems');
    if (!container) return;
    container.classList.toggle('gsm-summary-loading', !!show);
    if (show) {
      container.innerHTML =
        '<div class="co-summary__skeleton" aria-hidden="true">' +
        '<div class="co-summary__skeleton-line"></div>' +
        '<div class="co-summary__skeleton-line"></div>' +
        '</div>';
    }
  }

  function renderSummary() {
    var cart = readCart();
    var container = document.getElementById('checkoutSummaryItems');
    if (!container) return;

    container.classList.remove('gsm-summary-loading');

    var subtotal = cart.reduce(function (s, i) {
      return s + Number(i.price || 0) * Number(i.quantity || 1);
    }, 0);
    var tax = subtotal * 0.21;
    var total = subtotal;

    var countEl = document.getElementById('cartCount');
    if (countEl) {
      countEl.textContent =
        cart.length + ' item' + (cart.length !== 1 ? 's' : '');
    }

    if (!cart.length) {
      container.innerHTML =
        '<div class="co-summary__empty"><i class="fas fa-cart-shopping"></i>Je winkelwagen is leeg</div>';
    } else {
      container.innerHTML = cart
        .map(function (item) {
          var name = esc(item.name || 'Product');
          var thumb = item.image
            ? '<img src="' +
              String(item.image).replace(/"/g, '&quot;') +
              '" alt="">'
            : '<i class="fas fa-mobile-alt"></i>';
          var metaBits = [item.storage, item.condition].filter(Boolean).join(' · ');
          var qty = Number(item.quantity || 1);
          return (
            '<div class="co-summary__item">' +
            '<div class="co-summary__item-img">' +
            thumb +
            '</div>' +
            '<div class="co-summary__item-body">' +
            '<div class="co-summary__item-name">' +
            name +
            '</div>' +
            '<div class="co-summary__item-meta">' +
            esc(metaBits) +
            '<span class="co-summary__item-qty">' +
            qty +
            '</span></div>' +
            '</div>' +
            '<div class="co-summary__item-price">€' +
            (Number(item.price || 0) * qty).toFixed(2) +
            '</div>' +
            '</div>'
          );
        })
        .join('');
    }

    var subEl = document.getElementById('summarySubtotalVal');
    var taxEl = document.getElementById('summaryTaxVal');
    var totEl = document.getElementById('summaryTotalVal');
    if (subEl) subEl.textContent = '€' + subtotal.toFixed(2);
    if (taxEl) taxEl.textContent = '€' + tax.toFixed(2);
    if (totEl) totEl.textContent = '€' + total.toFixed(2);
  }

  function updateStepUI() {
    document.querySelectorAll('.co-step').forEach(function (el, i) {
      var n = i + 1;
      el.classList.remove('active', 'done');
      if (n === currentStep) el.classList.add('active');
      else if (n < currentStep) el.classList.add('done');
    });
  }

  function validateEmail() {
    var inp = document.getElementById('checkout-email');
    var errDiv = document.getElementById('email-error');
    if (!inp) return true;
    var email = inp.value.trim();
    var valid = email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    if (!valid) {
      inp.classList.add('field-input--invalid');
      inp.setAttribute('aria-invalid', 'true');
      if (errDiv) {
        errDiv.style.display = 'flex';
        errDiv.hidden = false;
      }
      inp.focus();
      if (global.GsmUI) {
        global.GsmUI.announce(
          document.getElementById('email-error-msg')
            ? document.getElementById('email-error-msg').textContent
            : 'Ongeldig e-mailadres'
        );
      }
      return false;
    }
    inp.classList.remove('field-input--invalid');
    inp.removeAttribute('aria-invalid');
    if (errDiv) {
      errDiv.style.display = 'none';
      errDiv.hidden = true;
    }
    return true;
  }

  function clearShippingErrors() {
    var box = document.getElementById('shipping-errors');
    if (box) {
      box.innerHTML = '';
      box.hidden = true;
    }
    ['checkout-address', 'checkout-postal', 'checkout-city'].forEach(function (id) {
      var el = document.getElementById(id);
      if (el) {
        el.classList.remove('field-input--invalid');
        el.removeAttribute('aria-invalid');
      }
    });
  }

  function showShippingErrors(messages, fields) {
    var box = document.getElementById('shipping-errors');
    if (!box) return;
    box.hidden = false;
    box.innerHTML =
      '<strong>Controleer je adres</strong><ul>' +
      messages.map(function (m) {
        return '<li>' + esc(m) + '</li>';
      }).join('') +
      '</ul>';
    if (global.GsmUI) global.GsmUI.announce(messages.join('. '));
    (fields || []).forEach(function (id) {
      var el = document.getElementById(id);
      if (el) {
        el.classList.add('field-input--invalid');
        el.setAttribute('aria-invalid', 'true');
      }
    });
  }

  function validateShippingStep() {
    clearShippingErrors();
    var addr = document.getElementById('checkout-address');
    var city = document.getElementById('checkout-city');
    var postal = document.getElementById('checkout-postal');
    var fn = document.getElementById('checkout-firstname');
    var ln = document.getElementById('checkout-lastname');

    var msgs = [];
    var fields = [];

    if (fn && !fn.value.trim()) {
      msgs.push('Vul je voornaam in.');
      fields.push('checkout-firstname');
    }
    if (ln && !ln.value.trim()) {
      msgs.push('Vul je achternaam in.');
      fields.push('checkout-lastname');
    }
    if (addr && !addr.value.trim()) {
      msgs.push('Vul straat en huisnummer in.');
      fields.push('checkout-address');
    }
    if (postal && !postal.value.trim()) {
      msgs.push('Vul je postcode in.');
      fields.push('checkout-postal');
    }
    if (city && !city.value.trim()) {
      msgs.push('Vul je stad in.');
      fields.push('checkout-city');
    }

    if (msgs.length) {
      showShippingErrors(msgs, fields);
      var first = document.getElementById(fields[0]);
      if (first) first.focus();
      return false;
    }
    return true;
  }

  global.checkoutNext = function () {
    var section = SECTIONS[currentStep - 1];

    if (currentStep === 1 && !validateEmail()) return;

    if (currentStep === 2 && !validateShippingStep()) return;

    if (currentStep < 4) {
      document.getElementById('section-' + section).classList.remove('active');
      currentStep++;
      document.getElementById('section-' + SECTIONS[currentStep - 1]).classList.add('active');
      updateStepUI();
      global.scrollTo({ top: 0, behavior: global.GsmUI && global.GsmUI.prefersReducedMotion() ? 'auto' : 'smooth' });
    }

    if (currentStep === 4) populateReview();
  };

  global.checkoutPrev = function () {
    if (currentStep > 1) {
      document.getElementById('section-' + SECTIONS[currentStep - 1]).classList.remove('active');
      currentStep--;
      document.getElementById('section-' + SECTIONS[currentStep - 1]).classList.add('active');
      updateStepUI();
      global.scrollTo({ top: 0, behavior: global.GsmUI && global.GsmUI.prefersReducedMotion() ? 'auto' : 'smooth' });
    }
  };

  function populateReview() {
    var cart = readCart();
    var wrap = document.getElementById('reviewItems');
    if (!wrap) return;

    wrap.innerHTML = cart.length
      ? cart
          .map(function (item) {
            var nm = esc(item.name || 'Product');
            return (
              '<div class="co-review-item">' +
              '<span class="co-review-item__name">' +
              nm +
              ' <span style="color:var(--muted);font-size:.85em;">×' +
              Number(item.quantity || 1) +
              '</span></span>' +
              '<span class="co-review-item__price">€' +
              (
                Number(item.price || 0) * Number(item.quantity || 1)
              ).toFixed(2) +
              '</span>' +
              '</div>'
            );
          })
          .join('')
      : '<div class="co-review-item"><span style="color:var(--muted)">Geen producten</span></div>';

    var addr = [
      (
        document.getElementById('checkout-firstname').value +
        ' ' +
        document.getElementById('checkout-lastname').value
      ).trim(),
      document.getElementById('checkout-address').value,
      document.getElementById('checkout-postal').value +
        ' ' +
        document.getElementById('checkout-city').value,
      document.getElementById('checkout-country').options[
        document.getElementById('checkout-country').selectedIndex
      ].text
    ]
      .filter(Boolean)
      .join('<br>');
    var addrEl = document.getElementById('review-address-preview');
    if (addrEl) addrEl.innerHTML = addr || '—';

    var pm = document.querySelector('input[name="payment"]:checked');
    var pmNames = {
      ideal: 'iDEAL',
      card: 'Creditcard (VISA/MC)',
      paypal: 'PayPal',
      klarna: 'Klarna – achteraf betalen'
    };
    var pmEl = document.getElementById('review-payment-preview');
    if (pmEl) pmEl.textContent = pm ? pmNames[pm.value] || pm.value : '—';
  }

  global.selectPayment = function (el, val) {
    document.querySelectorAll('.payment-option').forEach(function (o) {
      o.classList.remove('selected');
    });
    el.classList.add('selected');
    el.querySelector('input[type="radio"]').checked = true;
    var cardFields = document.getElementById('card-fields');
    if (cardFields) cardFields.classList.toggle('visible', val === 'card');
  };

  global.selectShipping = function (el) {
    document.querySelectorAll('.shipping-option').forEach(function (o) {
      o.classList.remove('selected');
    });
    el.classList.add('selected');
    el.querySelector('input[type="radio"]').checked = true;
  };

  function showCheckoutBanner(msg, opts) {
    opts = opts || {};
    var el = document.getElementById('checkout-global-banner');
    if (!el) {
      el = document.createElement('div');
      el.id = 'checkout-global-banner';
      el.className = 'gsm-checkout-banner';
      el.setAttribute('role', 'alert');
      var form = document.getElementById('checkout-form');
      if (form) form.insertBefore(el, form.firstChild);
      else return;
    }
    el.hidden = false;
    el.innerHTML =
      '<i class="fas fa-circle-exclamation" style="flex-shrink:0;margin-top:2px"></i><span>' +
      esc(msg) +
      '</span>';
    if (global.GsmUI) global.GsmUI.announce(msg);
    global.setTimeout(function () {
      if (el.parentNode && !opts.sticky) el.remove();
    }, opts.ttl || 8000);
  }

  /* ── Email live feedback ── */
  var emailInp = document.getElementById('checkout-email');
  if (emailInp) {
    emailInp.addEventListener('input', function () {
      if (this.value.trim()) {
        this.classList.remove('field-input--invalid');
        var errDiv = document.getElementById('email-error');
        if (errDiv) {
          errDiv.style.display = 'none';
          errDiv.hidden = true;
        }
      }
    });
  }

  ['checkout-address', 'checkout-postal', 'checkout-city', 'checkout-firstname', 'checkout-lastname'].forEach(
    function (id) {
      var el = document.getElementById(id);
      if (el) {
        el.addEventListener('input', function () {
          clearShippingErrors();
        });
      }
    }
  );

  /* ── Submit ── */
  var checkoutForm = document.getElementById('checkout-form');
  if (checkoutForm) {
    checkoutForm.addEventListener('submit', async function (e) {
      e.preventDefault();

      if (!validateEmail()) {
        if (currentStep > 1) {
          document.getElementById('section-' + SECTIONS[currentStep - 1]).classList.remove('active');
          currentStep = 1;
          document.getElementById('section-contact').classList.add('active');
          updateStepUI();
          global.scrollTo({ top: 0, behavior: 'smooth' });
        }
        return;
      }

      var cart = readCart();
      if (!cart.length) {
        showCheckoutBanner(
          'Je winkelwagen is leeg. Voeg producten toe voor je afrekent.',
          { ttl: 10000 }
        );
        return;
      }

      var btn = document.getElementById('placeOrderBtn');
      var orderPlacedOk = false;
      if (btn) {
        btn.classList.add('loading');
        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');
      }

      var first = (document.getElementById('checkout-firstname').value || '').trim();
      var last = (document.getElementById('checkout-lastname').value || '').trim();
      var countryEl = document.getElementById('checkout-country');
      var countryText = countryEl ? countryEl.options[countryEl.selectedIndex]?.text || '' : '';

      var addressObj = {
        first_name: first,
        last_name: last,
        address: document.getElementById('checkout-address').value || '',
        postcode: document.getElementById('checkout-postal').value || '',
        city: document.getElementById('checkout-city').value || '',
        country: countryText,
        country_code: countryEl ? countryEl.value || 'nl' : 'nl'
      };

      var payload = {
        email: document.getElementById('checkout-email').value.trim(),
        phone: document.getElementById('checkout-phone').value || '',
        full_name:
          (first + ' ' + last).trim() ||
          document.getElementById('checkout-email').value.trim(),
        payment_method:
          document.querySelector('input[name="payment"]:checked')?.value || 'ideal',
        shipping_method:
          document.querySelector('input[name="shipping"]:checked')?.value || 'standard',
        shipping_address: JSON.stringify(addressObj),
        items: cart
      };

      try {
        var res = await fetch('api/public/checkout_submit.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        var data = await res.json().catch(function () {
          return {};
        });
        if (!data.ok) {
          showCheckoutBanner(
            data.error || 'Bestelling mislukt. Probeer het opnieuw.',
            { ttl: 12000 }
          );
          return;
        }

        orderPlacedOk = true;
        localStorage.removeItem('gsmstunter-cart');
        document.getElementById('section-review').classList.remove('active');
        var success = document.getElementById('section-success');
        success.style.display = 'block';
        success.classList.add('active');
        document.getElementById('successRef').textContent =
          'Bestelnummer: ' + data.order_reference;
        var successEmail = document.getElementById('successEmail');
        if (successEmail) successEmail.textContent = payload.email;
        updateStepUI();
        global.scrollTo({ top: 0, behavior: 'smooth' });

        if (typeof global.showToast === 'function') {
          global.showToast(
            'success',
            tr('checkout-success-title'),
            tr('checkout-success-body') + ' ' + String(data.order_reference || '')
          );
        }
      } catch (err) {
        showCheckoutBanner(
          'Verbindingsfout. Controleer je internet en probeer opnieuw.',
          { ttl: 12000 }
        );
      } finally {
        if (btn && !orderPlacedOk) {
          btn.classList.remove('loading');
          btn.disabled = false;
          btn.removeAttribute('aria-busy');
        }
      }
    });
  }

  /* ── Init ── */
  showSummarySkeleton(true);
  global.requestAnimationFrame(function () {
    renderSummary();
  });

  updateStepUI();

  (function prefillFromSession() {
    try {
      var u = getStoredCustomer();
      if (!u || !u.email) return;

      var emailEl = document.getElementById('checkout-email');
      if (emailEl && !emailEl.value) emailEl.value = u.email;

      if (u.name) {
        var parts = u.name.trim().split(/\s+/);
        var fnEl = document.getElementById('checkout-firstname');
        var lnEl = document.getElementById('checkout-lastname');
        if (fnEl && !fnEl.value) fnEl.value = parts[0] || '';
        if (lnEl && !lnEl.value) lnEl.value = parts.slice(1).join(' ') || '';
      }

      var guestBanner = document.getElementById('guest-banner');
      var loggedBanner = document.getElementById('logged-in-banner');
      var nameEl = document.getElementById('loggedInName');
      if (guestBanner) guestBanner.style.display = 'none';
      if (loggedBanner) loggedBanner.style.display = 'flex';
      if (nameEl) nameEl.textContent = u.name || u.email;
    } catch (_) {}
  })();
})(typeof window !== 'undefined' ? window : globalThis);
