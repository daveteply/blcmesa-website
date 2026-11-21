<?php
/**
 * Title: Prayers We Use Page Layout
 * Slug: blcmesa/page-prayers
 * Categories: blcmesa-church, blcmesa-sermons
 * Description: Clean devotional layout featuring quick-link sub-nav pills and beautifully styled Christian and Lutheran prayers.
 */
?>
<!-- wp:group {"align":"wide","className":"blc-page-content-wrapper","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide blc-page-content-wrapper">

  <!-- PAGE LEAD INTRO -->
  <!-- wp:paragraph {"align":"center","className":"blc-page-lead","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|30"}}}} -->
  <p class="has-text-align-center blc-page-lead" style="margin-bottom:var(--wp--preset--spacing--30)">Timeless prayers, collects, and confessions of faith that sustain our community in daily devotion and Sunday worship.</p>
  <!-- /wp:paragraph -->

  <!-- IN-PAGE SUB-NAV PILLS (Quick Links) -->
  <!-- wp:group {"className":"blc-subnav-container","layout":{"type":"flex","justifyContent":"center","flexWrap":"wrap"}} -->
  <div class="wp-block-group blc-subnav-container">
    <nav class="blc-subnav-pills" aria-label="Prayers quick links">
      <a href="#apostles-creed" class="blc-subnav-pill">Apostles&rsquo; Creed</a>
      <a href="#lords-prayer" class="blc-subnav-pill">The Lord&rsquo;s Prayer</a>
      <a href="#benediction" class="blc-subnav-pill">The Benediction</a>
      <a href="#how-to-pray" class="blc-subnav-pill">How to Pray</a>
    </nav>
  </div>
  <!-- /wp:group -->

  <!-- PRAYERS CARDS CONTAINER -->
  <div class="blc-prayers-grid">

    <!-- PRAYER 1: APOSTLES' CREED -->
    <article id="apostles-creed" class="blc-prayer-card">
      <div class="blc-prayer-card-header">
        <span class="blc-prayer-badge">Confession of Faith</span>
        <h2 class="blc-prayer-title">The Apostles&rsquo; Creed</h2>
        <p class="blc-prayer-scripture">The historic baptismal confession of the Christian Church</p>
      </div>
      <div class="blc-prayer-body">
        <p><strong>I believe in God, the Father almighty,</strong> creator of heaven and earth.</p>
        <p><strong>I believe in Jesus Christ, God’s only Son, our Lord,</strong> who was conceived by the Holy Spirit, born of the virgin Mary, suffered under Pontius Pilate, was crucified, died, and was buried; he descended to the dead. On the third day he rose again; he ascended into heaven, he is seated at the right hand of the Father, and he will come to judge the living and the dead.</p>
        <p><strong>I believe in the Holy Spirit,</strong> the holy catholic Church, the communion of saints, the forgiveness of sins, the resurrection of the body, and the life everlasting.</p>
        <p class="blc-prayer-amen">Amen.</p>
      </div>
    </article>

    <!-- PRAYER 2: THE LORD'S PRAYER -->
    <article id="lords-prayer" class="blc-prayer-card">
      <div class="blc-prayer-card-header">
        <span class="blc-prayer-badge">The Model Prayer</span>
        <h2 class="blc-prayer-title">The Lord&rsquo;s Prayer</h2>
        <p class="blc-prayer-scripture">Matthew 6:9&ndash;13 &bull; Luke 11:2&ndash;4</p>
      </div>
      <div class="blc-prayer-body">
        <p>Our Father, who art in heaven,<br>
        hallowed be thy name,<br>
        thy kingdom come,<br>
        thy will be done,<br>
        on earth as it is in heaven.</p>
        <p>Give us this day our daily bread;<br>
        and forgive us our trespasses,<br>
        as we forgive those who trespass against us;<br>
        and lead us not into temptation,<br>
        but deliver us from evil.</p>
        <p>For thine is the kingdom,<br>
        and the power, and the glory,<br>
        forever and ever.</p>
        <p class="blc-prayer-amen">Amen.</p>
      </div>
    </article>

    <!-- PRAYER 3: THE BENEDICTION -->
    <article id="benediction" class="blc-prayer-card">
      <div class="blc-prayer-card-header">
        <span class="blc-prayer-badge">Blessing &amp; Dismissal</span>
        <h2 class="blc-prayer-title">The Benediction (Aaronic Blessing)</h2>
        <p class="blc-prayer-scripture">Numbers 6:24&ndash;26</p>
      </div>
      <div class="blc-prayer-body">
        <p>The Lord bless you and keep you.</p>
        <p>The Lord make his face shine on you and be gracious to you.</p>
        <p>The Lord look upon you with favor and give you peace.</p>
        <p class="blc-prayer-amen">Amen.</p>
      </div>
    </article>

    <!-- PRAYER 4: HOW TO PRAY & REFLECTION -->
    <article id="how-to-pray" class="blc-prayer-card">
      <div class="blc-prayer-card-header">
        <span class="blc-prayer-badge">Guidance &amp; Encouragement</span>
        <h2 class="blc-prayer-title">How Does a Person Pray?</h2>
        <p class="blc-prayer-scripture">A word on quiet conversation with God</p>
      </div>
      <div class="blc-prayer-body">
        <p>It is impossible to pray incorrectly. The prayers of a young child or an ancient saint are equal in God&rsquo;s ears. If you have never prayed before, simply get quiet, listen to your breathing, and talk to God.</p>
        <p>Tell God what is on your heart&mdash;your hopes and dreams, your fears and worries, your thankfulness and failures. Then simply listen. Listen with the ears of your heart for God&rsquo;s still, small voice and reassuring peace. You are always heard, cherished, and loved.</p>
        <p class="blc-prayer-amen">&ldquo;Do not worry about anything, but in everything by prayer and supplication with thanksgiving let your requests be made known to God.&rdquo; &mdash; Philippians 4:6</p>
      </div>
    </article>

  </div>

</div>
<!-- /wp:group -->
