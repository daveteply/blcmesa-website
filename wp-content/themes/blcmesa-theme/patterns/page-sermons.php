<?php
/**
 * Title: Sermons & Worship Services Page Layout
 * Slug: blcmesa/page-sermons
 * Categories: blcmesa-sermons, blcmesa-church
 * Description: Full responsive Sermons and Services page layout including featured weekly video embed, Sunday in-person schedule, campus map & office hours, and Vimeo video archive.
 */
?>
<!-- wp:group {"align":"wide","className":"blc-page-content-wrapper","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide blc-page-content-wrapper">

  <!-- PAGE LEAD INTRO -->
  <!-- wp:paragraph {"align":"center","className":"blc-page-lead","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|30"}}}} -->
  <p class="has-text-align-center blc-page-lead" style="margin-bottom:var(--wp--preset--spacing--30)">Join us in worship every week—gather with our welcoming community in the Sanctuary or experience uplifting worship online from home.</p>
  <!-- /wp:paragraph -->

  <!-- IN-PAGE SUB-NAV PILLS -->
  <!-- wp:group {"className":"blc-subnav-container","layout":{"type":"flex","justifyContent":"center","flexWrap":"wrap"}} -->
  <div class="wp-block-group blc-subnav-container">
    <nav class="blc-subnav-pills" aria-label="Sermons and worship services categories">
      <a href="#latest-sermon" class="blc-subnav-pill">Latest Sermon</a>
      <a href="#worship-schedule" class="blc-subnav-pill">Sunday Worship</a>
      <a href="#location-hours" class="blc-subnav-pill">Location &amp; Office Hours</a>
      <a href="#vimeo-archive" class="blc-subnav-pill">Video Archive</a>
    </nav>
  </div>
  <!-- /wp:group -->

  <!-- ========================================================= -->
  <!-- SECTION 1: LATEST SERMON & ONLINE BROADCAST               -->
  <!-- ========================================================= -->
  <!-- wp:group {"className":"blc-section-block blc-category-section","layout":{"type":"constrained"}} -->
  <div id="latest-sermon" class="wp-block-group blc-section-block blc-category-section">
    <!-- wp:heading {"level":2,"className":"blc-section-title","textAlign":"center"} -->
    <h2 class="wp-block-heading blc-section-title has-text-align-center">Watch Worship Online</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"className":"blc-section-subtitle","textAlign":"center","textColor":"muted"} -->
    <p class="has-text-align-center blc-section-subtitle has-muted-color has-text-color">Weekly worship videos and sermons shared directly on Vimeo for our church family and online visitors.</p>
    <!-- /wp:paragraph -->

    <!-- FEATURED SERMON CARD -->
    <!-- wp:group {"className":"blc-sermon-featured-card","backgroundColor":"white","layout":{"type":"constrained"}} -->
    <div class="wp-block-group blc-sermon-featured-card has-white-background-color has-background">
      <!-- wp:columns {"verticalAlignment":"center","className":"blc-sermon-columns"} -->
      <div class="wp-block-columns are-vertically-aligned-center blc-sermon-columns">
        <!-- wp:column {"width":"58%","className":"blc-sermon-video-col"} -->
        <div class="wp-block-column blc-sermon-video-col" style="flex-basis:58%">
          <!-- wp:embed {"url":"https://vimeo.com/1232853175","type":"video","providerNameSlug":"vimeo","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
          <figure class="wp-block-embed is-type-video is-provider-vimeo wp-block-embed-vimeo wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
          https://vimeo.com/1232853175
          </div>
          <figcaption class="wp-element-caption">Latest Message: Oct 4, 2026 — Rescue at Sea</figcaption>
          </figure>
          <!-- /wp:embed -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"width":"42%","className":"blc-sermon-info-col"} -->
        <div class="wp-block-column blc-sermon-info-col" style="flex-basis:42%">
          <span class="blc-ministry-badge">Featured Message</span>
          <h3 class="blc-sermon-heading">Sunday Worship Video</h3>
          <p class="blc-sermon-desc">Whether traveling, caring for family, or worshiping from the comfort of home, you are always part of Bethlehem. Weekly service recordings include our inspiring liturgy, Scripture reading, choral anthems, and Pastor Brian's message.</p>
          
          <div class="blc-sermon-meta-box">
            <div class="blc-sermon-meta-item">
              <span class="blc-meta-icon" aria-hidden="true">&#x1F4C5;</span>
              <div>
                <strong>Video Release Schedule</strong>
                <p>Worship videos are posted weekly on our Vimeo channel ahead of Sunday worship (typically Saturday evening or Sunday afternoon).</p>
              </div>
            </div>
            <div class="blc-sermon-meta-item">
              <span class="blc-meta-icon" aria-hidden="true">&#x1F464;</span>
              <div>
                <strong>Preacher:</strong> Pastor Brian Weinberger
              </div>
            </div>
          </div>

          <!-- wp:buttons {"className":"blc-sermon-action-buttons"} -->
          <div class="wp-block-buttons blc-sermon-action-buttons">
            <!-- wp:button {"className":"blc-btn-gold"} -->
            <div class="wp-block-button blc-btn-gold"><a class="wp-block-button__link wp-element-button" href="https://vimeo.com/blcmesa" target="_blank" rel="noopener noreferrer">Watch on Vimeo <span class="blc-external-icon" aria-hidden="true">&#x2197;</span></a></div>
            <!-- /wp:button -->
            <!-- wp:button {"className":"blc-btn-outline"} -->
            <div class="wp-block-button blc-btn-outline"><a class="wp-block-button__link wp-element-button" href="#worship-schedule">In-Person Details</a></div>
            <!-- /wp:button -->
          </div>
          <!-- /wp:buttons -->
        </div>
        <!-- /wp:column -->
      </div>
      <!-- /wp:columns -->
    </div>
    <!-- /wp:group -->

  </div>
  <!-- /wp:group -->

  <!-- ========================================================= -->
  <!-- SECTION 2: SUNDAY IN-PERSON WORSHIP                       -->
  <!-- ========================================================= -->
  <!-- wp:group {"className":"blc-section-block blc-category-section","layout":{"type":"constrained"}} -->
  <div id="worship-schedule" class="wp-block-group blc-section-block blc-category-section">
    <!-- wp:heading {"level":2,"className":"blc-section-title","textAlign":"center"} -->
    <h2 class="wp-block-heading blc-section-title has-text-align-center">In-Person Sunday Worship</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"className":"blc-section-subtitle","textAlign":"center","textColor":"muted"} -->
    <p class="has-text-align-center blc-section-subtitle has-muted-color has-text-color">We gather each Sunday morning to give thanks, hear God's Word, and share communion together.</p>
    <!-- /wp:paragraph -->

    <!-- WORSHIP HIGHLIGHTS GRID -->
    <div class="blc-worship-grid">

      <!-- CARD 1: Time & Sanctuary -->
      <div class="blc-worship-card">
        <div class="blc-worship-card-header">
          <span class="blc-worship-icon" aria-hidden="true">&#x1F558;</span>
          <span class="blc-ministry-badge">Sanctuary Service</span>
        </div>
        <h3 class="blc-worship-card-title">9:00 AM Every Sunday</h3>
        <p>Our traditional and blended Lutheran liturgy begins at 9:00 AM in the Sanctuary. Accessible seating, large print bulletins, and assisted listening devices are always available.</p>
        <div class="blc-worship-card-footer">
          <span><strong>When:</strong> Sundays at 9:00 AM</span>
        </div>
      </div>

      <!-- CARD 2: Holy Communion -->
      <div class="blc-worship-card">
        <div class="blc-worship-card-header">
          <span class="blc-worship-icon" aria-hidden="true">&#x1F377;</span>
          <span class="blc-ministry-badge">Open Table</span>
        </div>
        <h3 class="blc-worship-card-title">Holy Communion</h3>
        <p>At Bethlehem, we celebrate an open table. Christ is the host, and all baptized believers are welcome to receive the sacrament. Gluten-free wafers and grape juice are readily provided.</p>
        <div class="blc-worship-card-footer">
          <span><strong>Celebrated:</strong> Every Sunday</span>
        </div>
      </div>

      <!-- CARD 3: Children's Message -->
      <div class="blc-worship-card">
        <div class="blc-worship-card-header">
          <span class="blc-worship-icon" aria-hidden="true">&#x1F31F;</span>
          <span class="blc-ministry-badge">Families &amp; Youth</span>
        </div>
        <h3 class="blc-worship-card-title">Children Always Welcome</h3>
        <p>Children are a joyful blessing in worship. During each service, young ones are invited forward for a special Children's Message. Children's activity bags are also available in the narthex.</p>
        <div class="blc-worship-card-footer">
          <span><strong>Atmosphere:</strong> Family-Friendly</span>
        </div>
      </div>

      <!-- CARD 4: Coffee & Fellowship -->
      <div class="blc-worship-card">
        <div class="blc-worship-card-header">
          <span class="blc-worship-icon" aria-hidden="true">&#x2615;</span>
          <span class="blc-ministry-badge">Fellowship</span>
        </div>
        <h3 class="blc-worship-card-title">Coffee &amp; Fellowship Hour</h3>
        <p>Stay after the service for fresh brewed coffee, snacks, and warm conversation in the Fellowship Hall. It’s the perfect opportunity to meet Pastor Brian, greet neighbors, and make friends.</p>
        <div class="blc-worship-card-footer">
          <span><strong>When:</strong> 10:15 AM (After Service)</span>
        </div>
      </div>

      <!-- CARD 5: Come As You Are -->
      <div class="blc-worship-card">
        <div class="blc-worship-card-header">
          <span class="blc-worship-icon" aria-hidden="true">&#x2728;</span>
          <span class="blc-ministry-badge">What to Expect</span>
        </div>
        <h3 class="blc-worship-card-title">Come As You Are</h3>
        <p>Dress comfortably—you’ll find everything from jeans and t-shirts to Sunday best. What matters is gathering together in God’s grace and love. There is never any pressure.</p>
        <div class="blc-worship-card-footer">
          <span><strong>Dress:</strong> Casual &amp; Comfortable</span>
        </div>
      </div>

      <!-- CARD 6: Music & Praise -->
      <div class="blc-worship-card">
        <div class="blc-worship-card-header">
          <span class="blc-worship-icon" aria-hidden="true">&#x1F3B6;</span>
          <span class="blc-ministry-badge">Music Ministry</span>
        </div>
        <h3 class="blc-worship-card-title">Inspiring Music</h3>
        <p>Enjoy vibrant hymns and anthems accompanied by our Chancel Choir, organ, piano, and Heart Fire acoustic ensemble (guitars, banjo, percussion) under the direction of Bekka Weinberger.</p>
        <div class="blc-worship-card-footer">
          <span><strong>Music:</strong> Choral &amp; Heart Fire</span>
        </div>
      </div>

    </div>
  </div>
  <!-- /wp:group -->

  <!-- ========================================================= -->
  <!-- SECTION 3: LOCATION & CHURCH OFFICE HOURS                 -->
  <!-- ========================================================= -->
  <!-- wp:group {"className":"blc-section-block blc-category-section","layout":{"type":"constrained"}} -->
  <div id="location-hours" class="wp-block-group blc-section-block blc-category-section">
    <!-- wp:heading {"level":2,"className":"blc-section-title","textAlign":"center"} -->
    <h2 class="wp-block-heading blc-section-title has-text-align-center">Campus Location &amp; Office Hours</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"className":"blc-section-subtitle","textAlign":"center","textColor":"muted"} -->
    <p class="has-text-align-center blc-section-subtitle has-muted-color has-text-color">Conveniently located in East Mesa with ample accessible parking and welcoming grounds.</p>
    <!-- /wp:paragraph -->

    <!-- LOCATION 2-COLUMN WRAPPER -->
    <div class="blc-location-grid">

      <!-- LEFT: Details & Office Hours -->
      <div class="blc-location-details-card">
        <span class="blc-ministry-badge">Church Campus</span>
        <h3 class="blc-location-title">Bethlehem Lutheran Church</h3>

        <div class="blc-location-info-group">
          <div class="blc-location-info-row">
            <span class="blc-location-icon" aria-hidden="true">&#x1F4CD;</span>
            <div>
              <strong>Address</strong>
              <p>2745 N. 32nd Street<br>Mesa, Arizona 85213<br><span class="blc-text-muted">(North of McDowell Road)</span></p>
            </div>
          </div>

          <div class="blc-location-info-row">
            <span class="blc-location-icon" aria-hidden="true">&#x1F552;</span>
            <div>
              <strong>Church Office Hours</strong>
              <p><strong>Monday – Thursday:</strong> 8:00 AM – 1:00 PM<br><strong>Fridays:</strong> Closed<br><span class="blc-text-muted">If it is an urgent pastoral need, please reach out directly to Pastor Brian.</span></p>
            </div>
          </div>

          <div class="blc-location-info-row">
            <span class="blc-location-icon" aria-hidden="true">&#x1F4DE;</span>
            <div>
              <strong>Contact &amp; Inquiries</strong>
              <p>Phone: <a href="tel:4808320462" class="blc-inline-link">(480) 832-0462</a><br>Online: <a href="/contact/?to=office" class="blc-inline-link">Send an Online Message</a></p>
            </div>
          </div>
        </div>

        <div class="blc-location-actions">
          <a href="https://maps.google.com/?q=2745+N+32nd+St,+Mesa,+AZ+85213" target="_blank" rel="noopener noreferrer" class="wp-element-button blc-btn-gold-link">Open in Google Maps <span class="blc-external-icon" aria-hidden="true">&#x2197;</span></a>
          <a href="/contact/?to=office" class="wp-element-button blc-btn-outline-link">Contact Office</a>
        </div>
      </div>

      <!-- RIGHT: Embedded Interactive Map -->
      <div class="blc-location-map-wrap">
        <iframe 
          title="Bethlehem Lutheran Church Location Map"
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3328.3604273427068!2d-111.76339038480035!3d33.46596578076953!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x872ba429dec113d5%3A0x9fa623e62619a83a!2s2745+N+32nd+St%2C+Mesa%2C+AZ+85213!5e0!3m2!1sen!2sus!4v1510255833378" 
          width="100%" 
          height="100%" 
          style="border:0;" 
          allowfullscreen="" 
          loading="lazy" 
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>

    </div>
  </div>
  <!-- /wp:group -->

  <!-- ========================================================= -->
  <!-- SECTION 4: VIMEO ARCHIVE & BROADCAST CALLOUT             -->
  <!-- ========================================================= -->
  <!-- wp:group {"className":"blc-section-block blc-category-section","layout":{"type":"constrained"}} -->
  <div id="vimeo-archive" class="wp-block-group blc-section-block blc-category-section">
    <!-- wp:group {"className":"blc-archive-banner-card","backgroundColor":"midnight-slate","layout":{"type":"constrained"}} -->
    <div class="wp-block-group blc-archive-banner-card has-midnight-slate-background-color has-background">
      <div class="blc-archive-banner-inner">
        <span class="blc-archive-eyebrow">Video &amp; Sermon Library</span>
        <h2 class="blc-archive-title">Over 660+ Services &amp; Special Events on Vimeo</h2>
        <p class="blc-archive-text">Looking for a past Sunday message, special seasonal concert, Ash Wednesday or Christmas Eve worship? Our complete media library is freely accessible on Vimeo. Follow our channel to receive notifications whenever new messages and choral performances are published.</p>
        
        <div class="blc-archive-actions">
          <a href="https://vimeo.com/blcmesa" target="_blank" rel="noopener noreferrer" class="wp-element-button blc-btn-gold-link">Browse Full Vimeo Library <span class="blc-external-icon" aria-hidden="true">&#x2197;</span></a>
          <a href="/#worship" class="wp-element-button blc-btn-outline-white">Plan Your Sunday Visit</a>
        </div>
      </div>
    </div>
    <!-- /wp:group -->
  </div>
  <!-- /wp:group -->

</div>
<!-- /wp:group -->
