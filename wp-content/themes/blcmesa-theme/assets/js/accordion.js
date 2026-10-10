/**
 * BLC Mesa - Smooth Accordion Animations
 * Provides buttery-smooth Angular Material-style expand/collapse transitions
 * for native WordPress <details> blocks with cubic-bezier easing.
 */
document.addEventListener('DOMContentLoaded', function () {
  const detailsList = document.querySelectorAll('details.blc-details-item');

  detailsList.forEach(function (details) {
    const summary = details.querySelector('summary');
    if (!summary) return;

    summary.addEventListener('click', function (e) {
      // Prevent default instant snap so Web Animations API can animate height smoothly
      e.preventDefault();

      if (details.dataset.animating === 'true') {
        return;
      }

      if (details.open) {
        collapsePanel(details, summary);
      } else {
        expandPanel(details, summary);
      }
    });
  });

  function expandPanel(details, summary) {
    details.dataset.animating = 'true';
    
    // Set open true immediately so scrollHeight can be accurately computed
    details.open = true;

    const startHeight = summary.offsetHeight;
    const endHeight = details.scrollHeight;

    const animation = details.animate(
      [
        { height: startHeight + 'px', opacity: 0.95 },
        { height: endHeight + 'px', opacity: 1 }
      ],
      {
        duration: 320,
        easing: 'cubic-bezier(0.4, 0.0, 0.2, 1)' // Angular Material standard easing
      }
    );

    animation.onfinish = function () {
      details.style.height = '';
      details.dataset.animating = 'false';
    };
  }

  function collapsePanel(details, summary) {
    details.dataset.animating = 'true';

    const startHeight = details.offsetHeight;
    const endHeight = summary.offsetHeight;

    const animation = details.animate(
      [
        { height: startHeight + 'px', opacity: 1 },
        { height: endHeight + 'px', opacity: 0.95 }
      ],
      {
        duration: 260,
        easing: 'cubic-bezier(0.4, 0.0, 0.2, 1)'
      }
    );

    animation.onfinish = function () {
      details.open = false;
      details.style.height = '';
      details.dataset.animating = 'false';
    };
  }
});
