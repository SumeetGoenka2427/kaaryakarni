/* ==========================================================================
   Khandelwal Samaj Membership Portal — ADMIN shared behavior
   Sidebar toggle, modal dialog, confirm-before-destructive, toasts, tabs.
   Depends on shared/js/store.js and shared/js/session.js being loaded first.
   ========================================================================== */
(function () {
  'use strict';

  function initSidebar() {
    var toggle = document.querySelector('[data-sidebar-toggle]');
    var sidebar = document.querySelector('[data-admin-sidebar]');
    var scrim = document.querySelector('[data-admin-scrim]');
    if (!toggle || !sidebar) return;
    function close() { sidebar.classList.remove('is-open'); if (scrim) scrim.classList.remove('is-open'); }
    function open() { sidebar.classList.add('is-open'); if (scrim) scrim.classList.add('is-open'); }
    toggle.addEventListener('click', function () {
      sidebar.classList.contains('is-open') ? close() : open();
    });
    if (scrim) scrim.addEventListener('click', close);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
  }

  /* ---------- Toasts ---------- */
  function ensureToastStack() {
    var stack = document.querySelector('.toast-stack');
    if (!stack) {
      stack = document.createElement('div');
      stack.className = 'toast-stack';
      document.body.appendChild(stack);
    }
    return stack;
  }
  function toast(message, type) {
    var stack = ensureToastStack();
    var el = document.createElement('div');
    el.className = 'toast' + (type ? ' toast-' + type : '');
    el.setAttribute('role', 'status');
    el.textContent = message;
    stack.appendChild(el);
    setTimeout(function () { el.remove(); }, 3200);
  }
  window.KKToast = toast;

  /* ---------- Modal ---------- */
  var modalOverlay = null;
  function ensureModal() {
    if (modalOverlay) return modalOverlay;
    modalOverlay = document.createElement('div');
    modalOverlay.className = 'admin-modal-overlay';
    modalOverlay.innerHTML =
      '<div class="admin-modal" role="dialog" aria-modal="true">' +
      '  <div class="admin-modal-head"><h3 data-modal-title></h3><button type="button" class="admin-modal-close" data-modal-close aria-label="Close">&times;</button></div>' +
      '  <div class="admin-modal-body" data-modal-body></div>' +
      '  <div class="admin-modal-foot" data-modal-foot></div>' +
      '</div>';
    document.body.appendChild(modalOverlay);
    modalOverlay.addEventListener('click', function (e) { if (e.target === modalOverlay) closeModal(); });
    modalOverlay.querySelector('[data-modal-close]').addEventListener('click', closeModal);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modalOverlay.classList.contains('is-open')) closeModal(); });
    return modalOverlay;
  }
  function openModal(opts) {
    var overlay = ensureModal();
    overlay.querySelector('[data-modal-title]').textContent = opts.title || '';
    overlay.querySelector('[data-modal-body]').innerHTML = opts.bodyHtml || '';
    var foot = overlay.querySelector('[data-modal-foot]');
    foot.innerHTML = '';
    (opts.buttons || []).forEach(function (btn) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = btn.className || 'btn btn-outline';
      b.textContent = btn.label;
      b.addEventListener('click', function () {
        if (btn.onClick) btn.onClick(closeModal);
        else closeModal();
      });
      foot.appendChild(b);
    });
    overlay.classList.add('is-open');
    return overlay;
  }
  function closeModal() {
    if (modalOverlay) modalOverlay.classList.remove('is-open');
  }
  window.KKModal = { open: openModal, close: closeModal };

  /** Confirmation dialog before destructive actions (delete/reject/suspend/deactivate). */
  function confirmAction(message, onConfirm, opts) {
    opts = opts || {};
    openModal({
      title: opts.title || 'Please Confirm',
      bodyHtml: '<p style="margin:0;">' + message + '</p>',
      buttons: [
        { label: 'Cancel', className: 'btn btn-outline' },
        { label: opts.confirmLabel || 'Confirm', className: 'btn ' + (opts.danger ? 'btn-danger-outline' : 'btn-primary'), onClick: function (close) { onConfirm(); close(); } }
      ]
    });
  }
  window.KKConfirm = confirmAction;

  /* ---------- Tabs ---------- */
  function initTabs() {
    document.querySelectorAll('[data-tabs]').forEach(function (root) {
      var buttons = Array.prototype.slice.call(root.querySelectorAll('.tab-btn'));
      var panels = Array.prototype.slice.call(document.querySelectorAll('[data-tab-panel][data-tabs-group="' + root.getAttribute('data-tabs') + '"]'));
      buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
          buttons.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
          var key = btn.getAttribute('data-tab');
          panels.forEach(function (p) { p.hidden = p.getAttribute('data-tab-panel') !== key; });
        });
      });
    });
  }

  /* ---------- Admin nav user chip + logout wiring (every protected page) ---------- */
  function initAdminChrome() {
    var admin = window.KKSession ? window.KKSession.getAdmin() : null;
    if (admin) {
      var nameEl = document.querySelector('[data-admin-name]');
      var avatarEl = document.querySelector('[data-admin-avatar]');
      if (nameEl) nameEl.textContent = admin.name;
      if (avatarEl) avatarEl.textContent = admin.name.split(' ').map(function (w) { return w[0]; }).slice(0, 2).join('').toUpperCase();
    }
    var logoutBtn = document.querySelector('[data-admin-logout]');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', function () {
        confirmAction('You will be signed out of the admin panel.', function () {
          KKSession.logoutAdmin();
          window.location.href = 'login.html';
        }, { confirmLabel: 'Logout', title: 'Logout' });
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    initSidebar();
    initTabs();
    initAdminChrome();
  });
})();
