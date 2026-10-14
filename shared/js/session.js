/* ==========================================================================
   Khandelwal Samaj Membership Portal — DEMO session/auth helpers
   --------------------------------------------------------------------------
   Mock, client-side-only auth used while there is no backend (CLAUDE.md
   section 7 explicitly allows an isolated dev/mock mechanism when a real
   SMS/auth provider isn't configured yet — this is that mechanism).

   Real deployment MUST replace this with server-issued, server-verified
   sessions (httpOnly cookies / signed tokens). Never trust a client-only
   flag for authorization in production (CLAUDE.md sections 17/32).
   Depends on shared/js/store.js being loaded first.
   ========================================================================== */
(function (global) {
  'use strict';

  var MEMBER_KEY = 'kk_session_member';
  var ADMIN_KEY = 'kk_session_admin';

  function readJson(key) {
    try { return JSON.parse(global.localStorage.getItem(key)); } catch (e) { return null; }
  }
  function writeJson(key, value) {
    try { global.localStorage.setItem(key, JSON.stringify(value)); } catch (e) {}
  }
  function remove(key) {
    try { global.localStorage.removeItem(key); } catch (e) {}
  }

  var KKSession = {
    /* ---------- Member session ---------- */
    getMember: function () {
      var sess = readJson(MEMBER_KEY);
      if (!sess || !sess.memberId) return null;
      if (global.KKStore) {
        var record = global.KKStore.findById('members', sess.memberId);
        if (!record) return null;
        return record;
      }
      return sess;
    },
    loginMember: function (memberId) {
      writeJson(MEMBER_KEY, { memberId: memberId, loggedInAt: new Date().toISOString() });
    },
    logoutMember: function () {
      remove(MEMBER_KEY);
    },
    /** Redirects to loginUrl if no member session exists. Call at top of every protected member page. */
    requireMember: function (loginUrl) {
      var member = this.getMember();
      if (!member) {
        global.location.href = loginUrl || 'login.html';
        return null;
      }
      return member;
    },

    /* ---------- Admin session ---------- */
    getAdmin: function () {
      return readJson(ADMIN_KEY);
    },
    loginAdmin: function (adminUser) {
      writeJson(ADMIN_KEY, { username: adminUser.username, name: adminUser.name, role: adminUser.role, loggedInAt: new Date().toISOString() });
    },
    logoutAdmin: function () {
      remove(ADMIN_KEY);
    },
    /** Redirects to loginUrl if no admin session exists. Call at top of every protected admin page. */
    requireAdmin: function (loginUrl) {
      var admin = this.getAdmin();
      if (!admin) {
        global.location.href = loginUrl || 'login.html';
        return null;
      }
      return admin;
    }
  };

  global.KKSession = KKSession;
})(window);
