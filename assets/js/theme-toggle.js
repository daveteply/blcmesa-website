/**
 * Bethlehem Lutheran Church - Smart 2-State Color Scheme Toggle
 *
 * Follows Modern Web Guidance `dark-mode-toggle`:
 * - Synchronizes `<meta name="color-scheme">` ("light dark", "light", "dark").
 * - Flips immediately to the opposite of the currently rendered scheme.
 * - Stores an explicit override only when diverging from the OS preference;
 *   removes the stored key when returning to the OS preference so the site
 *   resumes tracking automatic OS theme changes.
 * - Never removes a stored override when the OS preference later changes to match it.
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'color-scheme';
  const LEGACY_STORAGE_KEY = 'blc-color-theme';
  const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

  function getSystemScheme() {
    return mediaQuery.matches ? 'dark' : 'light';
  }

  function getStoredScheme() {
    try {
      const value = localStorage.getItem(STORAGE_KEY);
      if (value === 'light' || value === 'dark') {
        return value;
      }
      const legacy = localStorage.getItem(LEGACY_STORAGE_KEY);
      if (legacy === 'light' || legacy === 'dark') {
        localStorage.setItem(STORAGE_KEY, legacy);
        localStorage.removeItem(LEGACY_STORAGE_KEY);
        return legacy;
      }
      if (legacy !== null) {
        localStorage.removeItem(LEGACY_STORAGE_KEY);
      }
    } catch (e) {
      // Storage unavailable or blocked; fall back to system scheme
    }
    return null;
  }

  function persistScheme(scheme) {
    try {
      if (scheme === 'light' || scheme === 'dark') {
        localStorage.setItem(STORAGE_KEY, scheme);
      } else {
        localStorage.removeItem(STORAGE_KEY);
      }
    } catch (e) {
      // Storage unavailable or blocked; scheme still applies for current page
    }
  }

  function getMetaColorScheme() {
    let meta = document.querySelector('meta[name="color-scheme"]');
    if (!meta && document.head) {
      meta = document.createElement('meta');
      meta.name = 'color-scheme';
      document.head.prepend(meta);
    }
    return meta;
  }

  function applyScheme(storedScheme) {
    const meta = getMetaColorScheme();
    if (meta) {
      meta.content = storedScheme || 'light dark';
    }

    const effective = storedScheme || getSystemScheme();
    document.documentElement.setAttribute('data-theme', effective);

    const buttons = document.querySelectorAll('.blc-theme-toggle');
    const title = effective === 'dark' ? 'Switch to light theme' : 'Switch to dark theme';
    buttons.forEach(function (btn) {
      btn.setAttribute('title', title);
    });
  }

  function handleToggleClick(e) {
    e.preventDefault();
    const systemScheme = getSystemScheme();
    const renderedScheme = getStoredScheme() || systemScheme;
    const targetScheme = renderedScheme === 'dark' ? 'light' : 'dark';

    // Divergence is checked only at storage time: store literal override if
    // different from OS, or clear storage to resume following OS preference.
    const nextStored = targetScheme !== systemScheme ? targetScheme : null;
    persistScheme(nextStored);
    applyScheme(nextStored);
  }

  function init() {
    applyScheme(getStoredScheme());

    document.querySelectorAll('.blc-theme-toggle').forEach(function (btn) {
      btn.removeEventListener('click', handleToggleClick);
      btn.addEventListener('click', handleToggleClick);
    });

    // React to OS changes without ever removing an explicit user override
    mediaQuery.addEventListener('change', function () {
      const stored = getStoredScheme();
      if (!stored) {
        applyScheme(null);
      }
    });

    // Synchronize theme overrides across open browser tabs
    window.addEventListener('storage', function (e) {
      if (e.key === STORAGE_KEY || e.key === null) {
        applyScheme(getStoredScheme());
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
