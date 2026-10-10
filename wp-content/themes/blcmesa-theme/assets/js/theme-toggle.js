/**
 * Bethlehem Lutheran Church - Theme Mode Toggle Handler
 * Supports Light, Dark, and System (OS default) preferences with localStorage persistence.
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'blc-color-theme';
  var mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

  function getStoredPreference() {
    try {
      return localStorage.getItem(STORAGE_KEY) || 'system';
    } catch (e) {
      return 'system';
    }
  }

  function setStoredPreference(pref) {
    try {
      localStorage.setItem(STORAGE_KEY, pref);
    } catch (e) {
      // Storage unavailable or disabled
    }
  }

  function resolveEffectiveTheme(pref) {
    if (pref === 'dark' || pref === 'light') {
      return pref;
    }
    return mediaQuery.matches ? 'dark' : 'light';
  }

  function applyTheme(pref) {
    var effective = resolveEffectiveTheme(pref);
    var root = document.documentElement;

    root.setAttribute('data-theme', effective);
    root.setAttribute('data-theme-pref', pref);

    updateToggleButtons(pref, effective);
  }

  function getNextPreference(currentPref) {
    if (currentPref === 'system') return 'light';
    if (currentPref === 'light') return 'dark';
    return 'system';
  }

  function updateToggleButtons(pref, effective) {
    var buttons = document.querySelectorAll('.blc-theme-toggle');
    if (!buttons.length) return;

    var titles = {
      system: 'Theme: System (Auto - matches your device). Click for Light.',
      light: 'Theme: Light. Click for Dark.',
      dark: 'Theme: Dark. Click for System.'
    };

    var ariaLabels = {
      system: 'Color theme is System. Currently displaying ' + effective + ' mode. Click to switch to Light mode.',
      light: 'Color theme is Light mode. Click to switch to Dark mode.',
      dark: 'Color theme is Dark mode. Click to switch to System mode.'
    };

    buttons.forEach(function (btn) {
      btn.setAttribute('data-current-pref', pref);
      btn.setAttribute('data-effective-theme', effective);
      btn.setAttribute('aria-label', ariaLabels[pref] || 'Toggle color theme');
      btn.setAttribute('title', titles[pref] || 'Toggle color theme');

      // Update visible icon states
      var iconLight = btn.querySelector('.blc-theme-icon-light');
      var iconDark = btn.querySelector('.blc-theme-icon-dark');
      var iconSystem = btn.querySelector('.blc-theme-icon-system');

      if (iconLight) iconLight.style.display = (pref === 'light') ? 'inline-block' : 'none';
      if (iconDark) iconDark.style.display = (pref === 'dark') ? 'inline-block' : 'none';
      if (iconSystem) iconSystem.style.display = (pref === 'system') ? 'inline-block' : 'none';
    });
  }

  function handleToggleClick(e) {
    e.preventDefault();
    var currentPref = getStoredPreference();
    var nextPref = getNextPreference(currentPref);

    setStoredPreference(nextPref);
    applyTheme(nextPref);
  }

  function init() {
    var currentPref = getStoredPreference();
    applyTheme(currentPref);

    // Bind click events to all toggle buttons on page
    var buttons = document.querySelectorAll('.blc-theme-toggle');
    buttons.forEach(function (btn) {
      btn.removeEventListener('click', handleToggleClick);
      btn.addEventListener('click', handleToggleClick);
    });

    // Listen for OS system theme changes
    if (mediaQuery.addEventListener) {
      mediaQuery.addEventListener('change', function () {
        if (getStoredPreference() === 'system') {
          applyTheme('system');
        }
      });
    } else if (mediaQuery.addListener) {
      mediaQuery.addListener(function () {
        if (getStoredPreference() === 'system') {
          applyTheme('system');
        }
      });
    }

    // Synchronize across multiple browser tabs
    window.addEventListener('storage', function (e) {
      if (e.key === STORAGE_KEY) {
        var newPref = e.newValue || 'system';
        applyTheme(newPref);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
