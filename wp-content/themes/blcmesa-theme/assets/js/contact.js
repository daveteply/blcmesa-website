/**
 * Contact Page Parameter Routing & Form Interactivity
 * Bethlehem Lutheran Church (blcmesa.org)
 *
 * Handles non-spoofable '?to=' parameter routing, in-page staff card triggers,
 * and AJAX form submissions with CSRF tokens and feedback states.
 */

(function () {
  'use strict';

  // Friendly query parameter aliases mapping to canonical keys
  const ALIASES = {
    'secretary': 'office',
    'general': 'office',
    'brian': 'pastor',
    'preschool': 'noahsark',
    'jill': 'noahsark',
    'jordan': 'music',
    'choir': 'music',
    'stephen': 'care',
    'faith-nurse': 'nurse',
    'families': 'youth',
    'ihelp': 'outreach',
    'garden': 'outreach'
  };

  /**
   * Resolves a client key against the server-injected validRecipients array.
   *
   * @param {string} rawKey - Untrusted query parameter or data-to attribute.
   * @return {string|null} Validated key or null if not allowed.
   */
  function resolveRecipientKey(rawKey) {
    if (!rawKey) return null;
    const normalized = String(rawKey).trim().toLowerCase();
    const candidate = ALIASES[normalized] || normalized;
    const allowed = (typeof blcContactData !== 'undefined' && Array.isArray(blcContactData.validRecipients))
      ? blcContactData.validRecipients
      : ['office', 'pastor', 'noahsark', 'music', 'care', 'nurse', 'youth', 'outreach'];
    return allowed.includes(candidate) ? candidate : null;
  }

  /**
   * Safely selects a recipient option in the contact form.
   *
   * @param {string} rawKey - Untrusted query parameter or data-to attribute.
   * @param {boolean} shouldScroll - Whether to scroll to the form.
   */
  function applyRecipient(rawKey, shouldScroll) {
    const safeKey = resolveRecipientKey(rawKey);
    if (!safeKey) return;

    const recipientSelect = document.getElementById('contact-recipient');
    if (recipientSelect) {
      recipientSelect.value = safeKey;
      recipientSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }

    if (shouldScroll) {
      const formCard = document.getElementById('contact-form') || document.querySelector('.blc-contact-form-card');
      if (formCard) {
        formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        setTimeout(() => {
          const nameInput = document.getElementById('contact-name') || recipientSelect;
          if (nameInput) {
            nameInput.focus();
          }
        }, 400);
      }
    }
  }

  function initContactForm() {
    // 1. Check URL search parameters
    const urlParams = new URLSearchParams(window.location.search);
    const toParam = urlParams.get('to');
    if (toParam) {
      applyRecipient(toParam, true);
    }

    // 2. Attach click handlers to in-page staff buttons
    const staffButtons = document.querySelectorAll('.blc-staff-contact-btn[data-to]');
    staffButtons.forEach((btn) => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        const targetTo = this.getAttribute('data-to');
        applyRecipient(targetTo, true);
      });
    });

    // 3. Handle AJAX Form Submission
    const nativeForm = document.getElementById('blc-native-contact-form');
    if (!nativeForm) return;

    const feedbackBox = document.getElementById('contact-form-feedback');
    const submitBtn = nativeForm.querySelector('.blc-form-submit-btn');

    nativeForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const ajaxEndpoint = (typeof blcContactData !== 'undefined' && blcContactData.ajaxUrl)
        ? blcContactData.ajaxUrl
        : '/wp-admin/admin-ajax.php';

      const nonce = (typeof blcContactData !== 'undefined' && blcContactData.nonce)
        ? blcContactData.nonce
        : (nativeForm.querySelector('input[name="blc_nonce"]')?.value || '');

      const formData = new FormData(nativeForm);
      formData.set('action', 'blc_submit_contact');
      if (nonce) {
        formData.set('blc_nonce', nonce);
      }

      // UI Loading state
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.setAttribute('data-original-text', submitBtn.innerHTML);
        submitBtn.innerHTML = 'Sending Message...';
      }

      if (feedbackBox) {
        feedbackBox.style.display = 'none';
        feedbackBox.className = 'blc-form-feedback';
      }

      fetch(ajaxEndpoint, {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (feedbackBox) {
            feedbackBox.style.display = 'block';
            if (data.success) {
              feedbackBox.className = 'blc-form-feedback blc-feedback-success';
              feedbackBox.innerHTML = `<p><strong>Message Sent!</strong> ${data.data.message}</p>`;

              if (data.data?.mockEmail) {
                console.group('%c📧 [BLC Dev Test] Mock Contact Form Submission', 'color: #D49B28; font-weight: bold; font-size: 13px;');
                console.info('To:          ', data.data.mockEmail.to);
                console.info('From:        ', data.data.mockEmail.from);
                console.info('Phone:       ', data.data.mockEmail.phone);
                console.info('Subject:     ', data.data.mockEmail.subject);
                console.info('Submitted At:', data.data.mockEmail.submittedAt);
                console.info('Message:     \n' + data.data.mockEmail.message);
                console.groupEnd();
              }

              nativeForm.reset();
              // Re-apply any default recipient if parameter present
              const currentTo = new URLSearchParams(window.location.search).get('to');
              if (currentTo) applyRecipient(currentTo, false);
            } else {
              feedbackBox.className = 'blc-form-feedback blc-feedback-error';
              feedbackBox.innerHTML = `<p><strong>Error:</strong> ${data.data?.message || 'Unable to send message. Please try again or call (480) 832-0462.'}</p>`;
            }
            feedbackBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
        })
        .catch(err => {
          if (feedbackBox) {
            feedbackBox.style.display = 'block';
            feedbackBox.className = 'blc-form-feedback blc-feedback-error';
            feedbackBox.innerHTML = '<p><strong>Connection Error:</strong> Could not reach the server. Please try again or call the church office at (480) 832-0462.</p>';
            feedbackBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
        })
        .finally(() => {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || 'Send Message &rarr;';
          }
        });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initContactForm);
  } else {
    initContactForm();
  }
})();
