/* ==========================================================================
   Khandelwal Samaj Membership Portal — shared front-end behavior
   Static/demo phase: no backend wired yet. OTP + form submissions are
   clearly-marked DEV MOCKS per CLAUDE.md section 7 (only used when no real
   SMS/payment provider is configured). Replace mockApi.* with real API
   calls once the server layer exists.
   ========================================================================== */
(function () {
  'use strict';

  /* ---------- Mobile navigation ---------- */
  function initNav() {
    var toggle = document.querySelector('[data-nav-toggle]');
    var nav = document.querySelector('[data-main-nav]');
    if (!toggle || !nav) return;

    toggle.addEventListener('click', function () {
      var isOpen = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', String(isOpen));
      document.body.style.overflow = isOpen ? 'hidden' : '';
    });

    nav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
      });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && nav.classList.contains('is-open')) {
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        toggle.focus();
      }
    });
  }

  /* ---------- Nav "More" dropdown (member area header) ---------- */
  function initNavDropdowns() {
    document.querySelectorAll('[data-nav-dropdown]').forEach(function (dropdown) {
      var toggle = dropdown.querySelector('[data-nav-dropdown-toggle]');
      var menu = dropdown.querySelector('[data-nav-dropdown-menu]');
      if (!toggle || !menu) return;

      function close() { menu.hidden = true; toggle.setAttribute('aria-expanded', 'false'); }
      function open() { menu.hidden = false; toggle.setAttribute('aria-expanded', 'true'); }

      toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        if (menu.hidden) open(); else close();
      });
      document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target)) close();
      });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !menu.hidden) { close(); toggle.focus(); }
      });
    });
  }

  /* ---------- Carousel (center banner) ---------- */
  function initCarousels() {
    document.querySelectorAll('[data-carousel]').forEach(function (root) {
      var slides = Array.prototype.slice.call(root.querySelectorAll('.carousel-slide'));
      var dotsWrap = root.querySelector('[data-carousel-dots]');
      var prevBtn = root.querySelector('[data-carousel-prev]');
      var nextBtn = root.querySelector('[data-carousel-next]');
      if (!slides.length) return;

      var index = slides.findIndex(function (s) { return s.classList.contains('is-active'); });
      if (index < 0) index = 0;
      var autoplayMs = parseInt(root.getAttribute('data-autoplay') || '5500', 10);
      var timer = null;

      if (dotsWrap) {
        dotsWrap.innerHTML = '';
        slides.forEach(function (_, i) {
          var dot = document.createElement('button');
          dot.type = 'button';
          dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
          dot.addEventListener('click', function () { goTo(i); restart(); });
          dotsWrap.appendChild(dot);
        });
      }

      function render() {
        slides.forEach(function (s, i) { s.classList.toggle('is-active', i === index); });
        if (dotsWrap) {
          Array.prototype.slice.call(dotsWrap.children).forEach(function (d, i) {
            d.classList.toggle('is-active', i === index);
          });
        }
      }
      function goTo(i) { index = (i + slides.length) % slides.length; render(); }
      function next() { goTo(index + 1); }
      function prev() { goTo(index - 1); }
      function start() {
        stop();
        if (slides.length > 1) timer = setInterval(next, autoplayMs);
      }
      function stop() { if (timer) { clearInterval(timer); timer = null; } }
      function restart() { start(); }

      if (prevBtn) prevBtn.addEventListener('click', function () { prev(); restart(); });
      if (nextBtn) nextBtn.addEventListener('click', function () { next(); restart(); });

      root.addEventListener('mouseenter', stop);
      root.addEventListener('mouseleave', start);
      root.addEventListener('focusin', stop);
      root.addEventListener('focusout', start);

      root.setAttribute('tabindex', '0');
      root.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight') { next(); restart(); }
        if (e.key === 'ArrowLeft') { prev(); restart(); }
      });

      var touchStartX = null;
      root.addEventListener('touchstart', function (e) { touchStartX = e.changedTouches[0].clientX; }, { passive: true });
      root.addEventListener('touchend', function (e) {
        if (touchStartX === null) return;
        var dx = e.changedTouches[0].clientX - touchStartX;
        if (Math.abs(dx) > 40) { dx < 0 ? next() : prev(); restart(); }
        touchStartX = null;
      }, { passive: true });

      render();
      start();
    });
  }

  /* ---------- Accordion (FAQ) ---------- */
  function initAccordions() {
    document.querySelectorAll('.accordion-trigger').forEach(function (trigger) {
      trigger.addEventListener('click', function () {
        var panel = document.getElementById(trigger.getAttribute('aria-controls'));
        var expanded = trigger.getAttribute('aria-expanded') === 'true';
        trigger.setAttribute('aria-expanded', String(!expanded));
        if (panel) panel.setAttribute('data-open', String(!expanded));
      });
    });
  }

  /* ---------- Lightbox (gallery) ---------- */
  function initLightbox() {
    var overlay = document.querySelector('[data-lightbox]');
    if (!overlay) return;
    var titleEl = overlay.querySelector('[data-lightbox-title]');
    var descEl = overlay.querySelector('[data-lightbox-desc]');
    var mediaEl = overlay.querySelector('.placeholder-media');
    var closeBtn = overlay.querySelector('[data-lightbox-close]');
    var lastFocused = null;

    function open(title, desc, imgUrl) {
      lastFocused = document.activeElement;
      if (titleEl) titleEl.textContent = title || '';
      if (descEl) descEl.textContent = desc || '';
      if (mediaEl) {
        if (imgUrl) {
          mediaEl.style.backgroundImage = "url('" + imgUrl + "')";
          mediaEl.style.backgroundSize = 'cover';
          mediaEl.style.backgroundPosition = 'center';
          mediaEl.querySelectorAll('svg').forEach(function (svg) { svg.style.display = 'none'; });
        } else {
          mediaEl.style.backgroundImage = '';
        }
      }
      overlay.classList.add('is-open');
      closeBtn && closeBtn.focus();
      document.body.style.overflow = 'hidden';
    }
    function close() {
      overlay.classList.remove('is-open');
      document.body.style.overflow = '';
      if (lastFocused) lastFocused.focus();
    }
    document.querySelectorAll('[data-gallery-tile]').forEach(function (tile) {
      tile.addEventListener('click', function () {
        open(tile.getAttribute('data-title'), tile.getAttribute('data-desc'), tile.getAttribute('data-img'));
      });
    });
    if (closeBtn) closeBtn.addEventListener('click', close);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && overlay.classList.contains('is-open')) close();
    });
  }

  /* ---------- Field validation helpers ---------- */
  var Validate = {
    mobile: function (v) { return /^[6-9]\d{9}$/.test((v || '').trim()); },
    required: function (v) { return (v || '').trim().length > 0; },
    email: function (v) { return !v || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()); },
    otp: function (v) { return /^\d{6}$/.test((v || '').trim()); }
  };

  function setFieldState(field, valid, message) {
    if (!field) return;
    field.classList.toggle('has-error', !valid);
    field.classList.toggle('is-valid', valid);
    var err = field.querySelector('.error-text');
    if (err && message) err.textContent = message;
  }

  function initFormValidation() {
    document.querySelectorAll('form[data-validate]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        var valid = true;
        form.querySelectorAll('[data-rule]').forEach(function (input) {
          var rule = input.getAttribute('data-rule');
          var field = input.closest('.field');
          var ok = true;
          if (rule === 'mobile') ok = Validate.mobile(input.value);
          if (rule === 'required') ok = Validate.required(input.value);
          if (rule === 'email') ok = Validate.email(input.value);
          if (rule === 'otp') ok = Validate.otp(input.value);
          setFieldState(field, ok);
          if (!ok) valid = false;
        });
        if (!valid) {
          e.preventDefault();
          var firstError = form.querySelector('.has-error input, .has-error select, .has-error textarea');
          if (firstError) firstError.focus();
        }
      });
      form.querySelectorAll('[data-rule]').forEach(function (input) {
        input.addEventListener('blur', function () {
          var rule = input.getAttribute('data-rule');
          var field = input.closest('.field');
          var ok = true;
          if (rule === 'mobile') ok = Validate.mobile(input.value);
          if (rule === 'required') ok = Validate.required(input.value);
          if (rule === 'email') ok = Validate.email(input.value);
          if (rule === 'otp') ok = Validate.otp(input.value);
          setFieldState(field, ok);
        });
      });
    });
  }

  /* ---------- OTP inputs auto-advance ---------- */
  function initOtpInputs() {
    document.querySelectorAll('.otp-inputs').forEach(function (group) {
      var inputs = Array.prototype.slice.call(group.querySelectorAll('input'));
      inputs.forEach(function (input, i) {
        input.addEventListener('input', function () {
          input.value = input.value.replace(/\D/g, '').slice(0, 1);
          if (input.value && inputs[i + 1]) inputs[i + 1].focus();
        });
        input.addEventListener('keydown', function (e) {
          if (e.key === 'Backspace' && !input.value && inputs[i - 1]) inputs[i - 1].focus();
        });
      });
    });
  }

  /* ---------- DEV MOCK: OTP request / resend cooldown ----------
     Clearly isolated mock flow used only in local/dev builds where no
     SMS provider is configured (CLAUDE.md section 7). */
  function initMockOtpFlow() {
    document.querySelectorAll('[data-otp-flow]').forEach(function (flow) {
      var mobileStep = flow.querySelector('[data-step="mobile"]');
      var otpStep = flow.querySelector('[data-step="otp"]');
      var sendBtn = flow.querySelector('[data-action="send-otp"]');
      var verifyBtn = flow.querySelector('[data-action="verify-otp"]');
      var resendBtn = flow.querySelector('[data-action="resend-otp"]');
      var resendTimerEl = flow.querySelector('[data-resend-timer]');
      var mobileInput = flow.querySelector('[data-rule="mobile"]');
      var devHint = flow.querySelector('[data-dev-otp-hint]');
      var attemptsLeftEl = flow.querySelector('[data-attempts-left]');
      var maxAttempts = 3;
      var attemptsLeft = maxAttempts;
      var mockCode = '123456';
      var cooldownTimer = null;

      function startCooldown(seconds) {
        if (!resendBtn) return;
        var remaining = seconds;
        resendBtn.disabled = true;
        clearInterval(cooldownTimer);
        cooldownTimer = setInterval(function () {
          remaining -= 1;
          if (resendTimerEl) resendTimerEl.textContent = remaining > 0 ? ('Resend OTP in ' + remaining + 's') : '';
          if (remaining <= 0) {
            clearInterval(cooldownTimer);
            resendBtn.disabled = false;
          }
        }, 1000);
      }

      if (sendBtn) {
        sendBtn.addEventListener('click', function () {
          if (mobileInput && !Validate.mobile(mobileInput.value)) {
            setFieldState(mobileInput.closest('.field'), false);
            mobileInput.focus();
            return;
          }
          sendBtn.classList.add('is-loading');
          sendBtn.disabled = true;
          setTimeout(function () {
            sendBtn.classList.remove('is-loading');
            sendBtn.disabled = false;
            if (mobileStep) mobileStep.hidden = true;
            if (otpStep) otpStep.hidden = false;
            if (devHint) devHint.textContent = 'DEV MODE: SMS provider not configured. Mock OTP is 123456.';
            startCooldown(30);
            var firstOtp = otpStep && otpStep.querySelector('.otp-inputs input');
            if (firstOtp) firstOtp.focus();
            flow.dispatchEvent(new CustomEvent('kk:otp-sent'));
          }, 700);
        });
      }

      if (resendBtn) {
        resendBtn.addEventListener('click', function () {
          attemptsLeft = maxAttempts;
          if (attemptsLeftEl) attemptsLeftEl.textContent = attemptsLeft + ' attempts left';
          startCooldown(30);
        });
      }

      if (verifyBtn) {
        verifyBtn.addEventListener('click', function () {
          var otpInputs = flow.querySelectorAll('.otp-inputs input');
          var code = Array.prototype.map.call(otpInputs, function (i) { return i.value; }).join('');
          var errorBox = flow.querySelector('[data-otp-error]');
          if (code.length < 6) {
            if (errorBox) { errorBox.hidden = false; errorBox.textContent = 'Please enter the full 6-digit OTP.'; }
            return;
          }
          verifyBtn.classList.add('is-loading');
          verifyBtn.disabled = true;
          setTimeout(function () {
            verifyBtn.classList.remove('is-loading');
            verifyBtn.disabled = false;
            if (code === mockCode) {
              if (errorBox) errorBox.hidden = true;
              var successStep = flow.querySelector('[data-step="success"]');
              if (otpStep) otpStep.hidden = true;
              if (successStep) successStep.hidden = false;
              var mobileValue = mobileInput ? mobileInput.value.trim() : '';
              flow.dispatchEvent(new CustomEvent('kk:otp-verified', { detail: { mobile: mobileValue } }));
            } else {
              attemptsLeft -= 1;
              if (attemptsLeftEl) attemptsLeftEl.textContent = Math.max(attemptsLeft, 0) + ' attempts left';
              if (errorBox) {
                errorBox.hidden = false;
                errorBox.textContent = attemptsLeft > 0
                  ? 'Incorrect OTP. Please try again.'
                  : 'Maximum attempts reached. Please request a new OTP.';
              }
              if (attemptsLeft <= 0 && verifyBtn) verifyBtn.disabled = true;
            }
          }, 600);
        });
      }
    });
  }

  /* ---------- Business directory / gallery client-side filtering (demo) ---------- */
  function initFilterLists() {
    document.querySelectorAll('[data-filter-list]').forEach(function (root) {
      var items = Array.prototype.slice.call(root.querySelectorAll('[data-filter-item]'));
      var emptyState = root.querySelector('[data-filter-empty]');
      var searchInput = root.querySelector('[data-filter-search]');
      var selects = Array.prototype.slice.call(root.querySelectorAll('[data-filter-select]'));

      function apply() {
        var term = (searchInput && searchInput.value || '').trim().toLowerCase();
        var activeSelects = selects.map(function (s) { return { key: s.getAttribute('data-filter-select'), value: s.value }; });
        var visibleCount = 0;
        items.forEach(function (item) {
          var text = (item.getAttribute('data-search-text') || item.textContent || '').toLowerCase();
          var matchesTerm = !term || text.indexOf(term) !== -1;
          var matchesSelects = activeSelects.every(function (s) {
            return !s.value || item.getAttribute('data-' + s.key) === s.value;
          });
          var visible = matchesTerm && matchesSelects;
          item.hidden = !visible;
          if (visible) visibleCount += 1;
        });
        if (emptyState) emptyState.hidden = visibleCount !== 0;
      }

      if (searchInput) searchInput.addEventListener('input', apply);
      selects.forEach(function (s) { s.addEventListener('change', apply); });
      apply();
    });
  }

  /* ---------- Donation / offline toggle ---------- */
  function initPaymentModeToggle() {
    document.querySelectorAll('[data-payment-toggle]').forEach(function (root) {
      var buttons = Array.prototype.slice.call(root.querySelectorAll('[data-mode]'));
      var panels = Array.prototype.slice.call(document.querySelectorAll('[data-mode-panel]'));
      buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
          buttons.forEach(function (b) { b.classList.toggle('btn-primary', b === btn); b.classList.toggle('btn-outline', b !== btn); b.setAttribute('aria-pressed', String(b === btn)); });
          panels.forEach(function (p) { p.hidden = p.getAttribute('data-mode-panel') !== btn.getAttribute('data-mode'); });
        });
      });
    });
  }

  /* ---------- Animate progress bars into view ---------- */
  function initProgressReveal() {
    var bars = document.querySelectorAll('.progress-fill[data-target-width]');
    if (!bars.length) return;
    bars.forEach(function (bar) {
      requestAnimationFrame(function () {
        bar.style.width = bar.getAttribute('data-target-width') + '%';
      });
    });
  }

  /* ---------- Generic submit-mock for forms without a backend yet ---------- */
  function initMockSubmitForms() {
    document.querySelectorAll('form[data-mock-submit]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        if (form.classList.contains('has-error')) return;
        var hasInvalid = form.querySelector('.field.has-error');
        if (hasInvalid) return;
        e.preventDefault();
        var successMsg = form.querySelector('[data-submit-success]');
        var submitBtn = form.querySelector('[type="submit"]');
        if (submitBtn) { submitBtn.classList.add('is-loading'); submitBtn.disabled = true; }
        setTimeout(function () {
          if (submitBtn) { submitBtn.classList.remove('is-loading'); submitBtn.disabled = false; }
          if (successMsg) successMsg.hidden = false;
          form.reset();
        }, 700);
      });
    });
  }

  /* ---------- Member-aware nav (public pages only) ----------
     If a member session exists (shared/js/session.js loaded on this page),
     swap the Login/Become-a-Member links for a Dashboard/name link. Pages
     that already render their own nav-cta (e.g. member area pages) don't
     include session.js's guard the same way, so this only affects the
     public marketing pages. */
  function initMemberAwareNav() {
    if (typeof KKSession === 'undefined') return;
    var member = KKSession.getMember();
    if (!member) return;
    var navCta = document.querySelector('header.site-header .nav-cta');
    if (!navCta || !navCta.querySelector('a[href="login.html"]')) return;
    navCta.innerHTML =
      '<a href="member/dashboard.html" class="btn btn-outline btn-sm">' + member.name.split(' ')[0] + '</a>' +
      '<a href="member/dashboard.html" class="btn btn-gold btn-sm">My Dashboard</a>';
  }

  document.addEventListener('DOMContentLoaded', function () {
    initNav();
    initNavDropdowns();
    initCarousels();
    initAccordions();
    initLightbox();
    initFormValidation();
    initOtpInputs();
    initMockOtpFlow();
    initFilterLists();
    initPaymentModeToggle();
    initProgressReveal();
    initMockSubmitForms();
    initMemberAwareNav();
  });
})();

/* Local photo pool (assets/images/new/1-6.jpg). Maps any key to a stable photo. */
function photoFor(key) {
  var s = String(key == null ? '' : key), h = 0;
  for (var i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) >>> 0;
  return 'assets/images/new/' + (h % 6 + 1) + '.jpg';
}
