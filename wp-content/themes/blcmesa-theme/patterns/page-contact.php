<?php

/**
 * Title: Contact Us Page Layout
 * Slug: blcmesa/page-contact
 * Categories: blcmesa-church
 * Description: Clean, responsive contact layout with church office hours, staff directory message triggers, and privacy-shielded recipient form routing.
 */
?>
<!-- wp:group {"align":"wide","className":"blc-page-content-wrapper","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide blc-page-content-wrapper">

  <!-- PAGE LEAD INTRO -->
  <!-- wp:paragraph {"align":"center","className":"blc-page-lead","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}}} -->
  <p class="has-text-align-center blc-page-lead" style="margin-bottom:var(--wp--preset--spacing--40)">We would love to hear from you. Reach out to our church office or send a direct message to any of our pastors and ministry staff below.</p>
  <!-- /wp:paragraph -->

  <!-- TWO-COLUMN CONTACT LAYOUT -->
  <!-- wp:columns {"className":"blc-contact-grid","align":"wide"} -->
  <div class="wp-block-columns alignwide blc-contact-grid">

    <!-- LEFT COLUMN: OFFICE HOURS & STAFF DIRECTORY -->
    <!-- wp:column {"width":"45%","className":"blc-contact-info-col"} -->
    <div class="wp-block-column blc-contact-info-col" style="flex-basis:45%">

      <!-- OFFICE & CAMPUS DETAILS CARD -->
      <!-- wp:group {"className":"blc-card-cream blc-contact-info-card","backgroundColor":"cream","layout":{"type":"constrained"}} -->
      <div class="wp-block-group blc-card-cream blc-contact-info-card has-cream-background-color has-background">
        <!-- wp:heading {"level":3,"className":"blc-contact-card-title"} -->
        <h3 class="wp-block-heading blc-contact-card-title">Church Office</h3>
        <!-- /wp:heading -->

        <div class="blc-contact-detail-row">
          <div class="blc-contact-detail-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
          </div>
          <div>
            <strong>Office Hours:</strong><br>
            Monday &ndash; Thursday: 8:30 AM &ndash; 1:30 PM<br>
            <span class="blc-text-muted">Closed Fridays &amp; Saturdays</span>
          </div>
        </div>

        <div class="blc-contact-detail-row">
          <div class="blc-contact-detail-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
            </svg>
          </div>
          <div>
            <strong>Phone &amp; Fax:</strong><br>
            Phone: <a href="tel:4808320462" class="blc-inline-link">(480) 832-0462</a><br>
            Fax: (480) 832-0404
          </div>
        </div>

        <div class="blc-contact-detail-row">
          <div class="blc-contact-detail-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
          </div>
          <div>
            <strong>Campus Location:</strong><br>
            2745 N. 32nd St., Mesa, AZ 85213<br>
            <span class="blc-text-muted">Southeast corner of McDowell &amp; 32nd St</span><br>
            <a href="https://maps.google.com/?q=2745+N+32nd+St,+Mesa,+AZ+85213" target="_blank" rel="noopener noreferrer" class="blc-inline-link blc-external-link">Get Directions <span aria-hidden="true">&#x2197;</span></a>
          </div>
        </div>
      </div>
      <!-- /wp:group -->

      <!-- STAFF DIRECTORY CARD -->
      <!-- wp:group {"className":"blc-card-cream blc-contact-staff-card","backgroundColor":"cream","layout":{"type":"constrained"}} -->
      <div class="wp-block-group blc-card-cream blc-contact-staff-card has-cream-background-color has-background">
        <!-- wp:heading {"level":3,"className":"blc-contact-card-title"} -->
        <h3 class="wp-block-heading blc-contact-card-title">Staff &amp; Ministry Leaders</h3>
        <!-- /wp:heading -->

        <!-- wp:paragraph {"className":"blc-text-muted"} -->
        <p class="blc-text-muted">Click any leader to direct your message to them in the contact form:</p>
        <!-- /wp:paragraph -->

        <div class="blc-staff-directory-list">

          <!-- Staff 1: Pastor Brian -->
          <div class="blc-staff-directory-item">
            <div class="blc-staff-info">
              <span class="blc-staff-name">Pastor Brian Weinberger</span>
              <span class="blc-staff-role">Pastor</span>
            </div>
            <button type="button" class="blc-staff-contact-btn" data-to="pastor" aria-label="Message Pastor Brian">
              Message <span aria-hidden="true">&rarr;</span>
            </button>
          </div>

          <!-- Staff 2: Bekkah Weinberger -->
          <div class="blc-staff-directory-item">
            <div class="blc-staff-info">
              <span class="blc-staff-name">Bekkah Weinberger</span>
              <span class="blc-staff-role">Church Administration &amp; Music</span>
            </div>
            <button type="button" class="blc-staff-contact-btn" data-to="office" aria-label="Message Bekkah Weinberger">
              Message <span aria-hidden="true">&rarr;</span>
            </button>
          </div>

          <!-- Staff 3: Jill Majestic -->
          <div class="blc-staff-directory-item">
            <div class="blc-staff-info">
              <span class="blc-staff-name">Jill Majestic</span>
              <span class="blc-staff-role">Noah's Ark Preschool Director</span>
            </div>
            <button type="button" class="blc-staff-contact-btn" data-to="noahsark" aria-label="Message Jill Majestic">
              Message <span aria-hidden="true">&rarr;</span>
            </button>
          </div>

          <!-- Staff 4: Jordan Webb -->
          <div class="blc-staff-directory-item">
            <div class="blc-staff-info">
              <span class="blc-staff-name">Jordan Webb</span>
              <span class="blc-staff-role">Music Director</span>
            </div>
            <button type="button" class="blc-staff-contact-btn" data-to="music" aria-label="Message Jordan Webb">
              Message <span aria-hidden="true">&rarr;</span>
            </button>
          </div>

          <!-- Staff 5: Sara Oliveira -->
          <div class="blc-staff-directory-item">
            <div class="blc-staff-info">
              <span class="blc-staff-name">Sara Oliveira</span>
              <span class="blc-staff-role">Social Media &amp; Nursery</span>
            </div>
            <button type="button" class="blc-staff-contact-btn" data-to="youth" aria-label="Message Sara Oliveira">
              Message <span aria-hidden="true">&rarr;</span>
            </button>
          </div>

          <!-- Staff 6: Karen Vidot -->
          <div class="blc-staff-directory-item">
            <div class="blc-staff-info">
              <span class="blc-staff-name">Karen Vidot</span>
              <span class="blc-staff-role">Care &amp; Mission Coordinator</span>
            </div>
            <button type="button" class="blc-staff-contact-btn" data-to="care" aria-label="Message Karen Vidot">
              Message <span aria-hidden="true">&rarr;</span>
            </button>
          </div>

        </div>
      </div>
      <!-- /wp:group -->

    </div>
    <!-- /wp:column -->

    <!-- RIGHT COLUMN: INTERACTIVE CONTACT FORM -->
    <!-- wp:column {"width":"55%","className":"blc-contact-form-col"} -->
    <div class="wp-block-column blc-contact-form-col" style="flex-basis:55%">

      <!-- wp:group {"className":"blc-card-cream blc-contact-form-card","backgroundColor":"cream","layout":{"type":"constrained"}} -->
      <div id="contact-form" class="wp-block-group blc-card-cream blc-contact-form-card has-cream-background-color has-background">
        <!-- wp:heading {"level":3,"className":"blc-contact-card-title"} -->
        <h3 class="wp-block-heading blc-contact-card-title">Send a Message</h3>
        <!-- /wp:heading -->

        <!-- wp:html -->
        <form id="blc-native-contact-form" class="blc-contact-form" method="post" action="/wp-admin/admin-post.php">
          <input type="hidden" name="action" value="blc_submit_contact" />
          <input type="text" name="blc_hp_check" class="blc-hp-field" style="display:none !important;" tabindex="-1" autocomplete="off" aria-hidden="true" />

          <div class="blc-form-field">
            <label for="contact-name" class="blc-form-label">Your Name <span class="blc-required">*</span></label>
            <input type="text" id="contact-name" name="name" class="blc-form-input" required placeholder="First and Last Name" />
          </div>

          <div class="blc-form-row">
            <div class="blc-form-field blc-form-half">
              <label for="contact-email" class="blc-form-label">Email Address <span class="blc-required">*</span></label>
              <input type="email" id="contact-email" name="email" class="blc-form-input" required placeholder="you@example.com" />
            </div>
            <div class="blc-form-field blc-form-half">
              <label for="contact-phone" class="blc-form-label">Phone Number <span class="blc-text-muted">(Optional)</span></label>
              <input type="tel" id="contact-phone" name="phone" class="blc-form-input" placeholder="(480) 555-0123" />
            </div>
          </div>

          <div class="blc-form-field">
            <label for="contact-recipient" class="blc-form-label">Recipient / Ministry Area <span class="blc-required">*</span></label>
            <select id="contact-recipient" name="recipient" class="blc-form-select" required>
              <option value="office">Church Office &amp; General Inquiries</option>
              <option value="pastor">Pastor &amp; Pastoral Care</option>
              <option value="noahsark">Noah's Ark Preschool</option>
              <option value="music">Music Ministry</option>
              <option value="care">Fellowship &amp; Care / Stephen Ministry</option>
              <option value="nurse">Faith Community Nurse</option>
              <option value="youth">Children &amp; Youth Ministries</option>
              <option value="outreach">Community Outreach &amp; I-HELP</option>
            </select>
          </div>

          <div class="blc-form-field">
            <label for="contact-subject" class="blc-form-label">Subject</label>
            <input type="text" id="contact-subject" name="subject" class="blc-form-input" placeholder="How can we help you?" />
          </div>

          <div class="blc-form-field">
            <label for="contact-message" class="blc-form-label">Message <span class="blc-required">*</span></label>
            <textarea id="contact-message" name="message" class="blc-form-textarea" rows="5" required placeholder="Write your message or prayer request here..."></textarea>
          </div>

          <!-- Anti-spam & Privacy Shield Notice -->
          <div class="blc-form-privacy-note">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16" aria-hidden="true">
              <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <span><strong>Privacy Protected:</strong> Your information is kept strictly confidential and only transmitted internally to the selected church ministry leader.</span>
          </div>

          <div id="contact-form-feedback" class="blc-form-feedback" style="display:none;">
            <p><strong>Thank you for contacting Bethlehem Lutheran Church!</strong> Your message has been received and our office will follow up shortly.</p>
          </div>

          <button type="submit" class="blc-btn-gold-link blc-form-submit-btn">
            Send Message <span aria-hidden="true">&rarr;</span>
          </button>

          <!-- 
            NOTE FOR CHURCH WEBMASTER:
            To use the legacy inline Jotform instead, replace this form block with:
            <script type="text/javascript" src="https://form.jotform.com/jsform/73125498184160"></script>
            Or insert your preferred WordPress form plugin shortcode (e.g. Forminator or Fluent Forms).
          -->
        </form>
        <!-- /wp:html -->

      </div>
      <!-- /wp:group -->

    </div>
    <!-- /wp:column -->

  </div>
  <!-- /wp:columns -->

</div>
<!-- /wp:group -->