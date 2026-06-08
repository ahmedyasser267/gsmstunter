/**
 * Global UI helpers: toasts, busy buttons, a11y announcements.
 */
(function (global) {
  function escapeHtml(str) {
    if (str == null) return '';
    var div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
  }

  function prefersReducedMotion() {
    return global.matchMedia && global.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  function announce(message) {
    var live = document.getElementById('gsm-aria-live');
    if (!live) {
      live = document.createElement('div');
      live.id = 'gsm-aria-live';
      live.setAttribute('role', 'status');
      live.setAttribute('aria-live', 'polite');
      live.className = 'visually-hidden';
      live.style.cssText = 'position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0';
      document.body.appendChild(live);
    }
    live.textContent = '';
    global.setTimeout(function () {
      live.textContent = message;
    }, 50);
  }

  function ensureToastContainer() {
    var container = document.querySelector('.toast-container');
    if (container) return container;
    container = document.createElement('div');
    container.className = 'toast-container';
    container.setAttribute('aria-live', 'polite');
    container.setAttribute('aria-relevant', 'additions');
    document.body.appendChild(container);
    return container;
  }

  function showToast(type, title, message) {
    type = type || 'success';
    var container = ensureToastContainer();

    var iconMap = {
      success: 'fas fa-check-circle',
      error: 'fas fa-exclamation-circle',
      warning: 'fas fa-exclamation-triangle',
      info: 'fas fa-info-circle'
    };

    var colorMap = {
      success: 'var(--color-success)',
      error: 'var(--color-error)',
      warning: 'var(--color-warning)',
      info: 'var(--color-info)'
    };

    var toast = document.createElement('div');
    toast.className = 'toast toast--' + type + ' gsm-toast-enter';
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

    var icon = document.createElement('i');
    icon.className = (iconMap[type] || iconMap.info) + ' toast__icon';
    icon.style.color = colorMap[type] || colorMap.info;

    var content = document.createElement('div');
    content.className = 'toast__content';

    var titleEl = document.createElement('div');
    titleEl.className = 'toast__title';
    titleEl.innerHTML = escapeHtml(title || '');

    content.appendChild(titleEl);

    if (message) {
      var msgEl = document.createElement('div');
      msgEl.className = 'toast__message';
      msgEl.innerHTML = escapeHtml(message);
      content.appendChild(msgEl);
    }

    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'toast__close';
    closeBtn.setAttribute('aria-label', 'Sluiten');
    closeBtn.innerHTML = '<i class="fas fa-times" aria-hidden="true"></i>';
    closeBtn.addEventListener('click', function () {
      toast.remove();
    });

    toast.appendChild(icon);
    toast.appendChild(content);
    toast.appendChild(closeBtn);
    container.appendChild(toast);

    var digest = (title || '') + (message ? '. ' + message : '');
    if (digest.trim()) announce(digest.trim());

    var ttl = type === 'error' ? 6500 : 4200;
    global.setTimeout(function () {
      if (!toast.parentNode) return;
      if (prefersReducedMotion()) {
        toast.remove();
        return;
      }
      toast.classList.add('gsm-toast-leave');
      global.setTimeout(function () {
        toast.remove();
      }, 280);
    }, ttl);
  }

  /**
   * Toggle loading state on primary buttons (checkout, forms).
   */
  function setBusy(button, busy, opts) {
    if (!button) return;
    opts = opts || {};
    var loadingClass = opts.loadingClass || 'is-loading';
    if (!button.dataset._gsmBusyHtml && busy) {
      button.dataset._gsmBusyHtml = button.innerHTML;
    }
    button.disabled = !!busy;
    button.classList.toggle(loadingClass, !!busy);
    button.setAttribute('aria-busy', busy ? 'true' : 'false');
    if (busy && opts.label) {
      button.innerHTML =
        '<span class="gsm-spinner gsm-spinner--btn" aria-hidden="true"></span> ' +
        escapeHtml(opts.label);
    } else if (!busy && button.dataset._gsmBusyHtml) {
      button.innerHTML = button.dataset._gsmBusyHtml;
      delete button.dataset._gsmBusyHtml;
    }
  }

  global.GsmUI = {
    escapeHtml: escapeHtml,
    showToast: showToast,
    setBusy: setBusy,
    announce: announce,
    prefersReducedMotion: prefersReducedMotion
  };

  global.showToast = showToast;
})(typeof window !== 'undefined' ? window : globalThis);
