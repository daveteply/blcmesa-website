/**
 * Mobile Navigation Overlay Toggle Handler
 * Clean, rock-solid full-screen fade overlay toggle.
 */
document.addEventListener('DOMContentLoaded', function () {
  const navContainers = document.querySelectorAll('.wp-block-navigation');

  navContainers.forEach(function (nav) {
    const openBtn = nav.querySelector('.wp-block-navigation__responsive-container-open');
    const closeBtn = nav.querySelector('.wp-block-navigation__responsive-container-close');
    const modal = nav.querySelector('.wp-block-navigation__responsive-container');

    if (!openBtn || !modal) return;

    function openMenu() {
      modal.classList.add('is-menu-open', 'has-modal-open');
      modal.setAttribute('aria-hidden', 'false');
      document.documentElement.classList.add('has-modal-open');
      document.body.classList.add('has-modal-open');
    }

    function closeMenu() {
      modal.classList.remove('is-menu-open', 'has-modal-open');
      modal.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove('has-modal-open');
      document.body.classList.remove('has-modal-open');
    }

    openBtn.addEventListener('click', function (e) {
      e.preventDefault();
      openMenu();
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', function (e) {
        e.preventDefault();
        closeMenu();
      });
    }

    // Close when clicking any navigation link inside the drawer
    const navLinks = modal.querySelectorAll('.wp-block-navigation-item__link');
    navLinks.forEach(function (link) {
      link.addEventListener('click', function () {
        closeMenu();
      });
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modal.classList.contains('is-menu-open')) {
        closeMenu();
      }
    });
  });
});
