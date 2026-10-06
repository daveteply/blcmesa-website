<?php
/**
 * Title: Weekly Sermon Video Post
 * Slug: blcmesa/sermon-post
 * Categories: blcmesa-sermons
 * Description: Ready-to-use template for weekly sermon videos, scripture reading, and sermon summary.
 */
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}},"border":{"radius":"8px","color":"var:preset|color|gray-subtle","width":"1px"}},"backgroundColor":"light","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-light-background-color has-background" style="border-color:var(--wp--preset--color--gray-subtle);border-width:1px;border-radius:8px;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--30)">
  <!-- wp:embed {"providerNameSlug":"youtube","responsive":true} -->
  <figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
  https://www.youtube.com/watch?v=dQw4w9WgXcQ
  </div><figcaption class="wp-element-caption">Replace with weekly sermon video link</figcaption></figure>
  <!-- /wp:embed -->

  <!-- wp:columns {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
  <div class="wp-block-columns" style="margin-top:var(--wp--preset--spacing--30)">
    <!-- wp:column {"width":"65%"} -->
    <div class="wp-block-column" style="flex-basis:65%">
      <!-- wp:heading {"level":3} -->
      <h3 class="wp-block-heading">Message Overview</h3>
      <!-- /wp:heading -->
      <!-- wp:paragraph -->
      <p>Summary of this Sunday's sermon message and key reflections.</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column {"width":"35%"} -->
    <div class="wp-block-column" style="flex-basis:35%">
      <!-- wp:heading {"level":4} -->
      <h4 class="wp-block-heading">Scripture &amp; Details</h4>
      <!-- /wp:heading -->
      <!-- wp:paragraph {"style":{"typography":{"fontSize":"var:preset|font-size|small"}}} -->
      <p style="font-size:var(--wp--preset--font-size--small)"><strong>Preacher:</strong> Pastor Brian Weinberger<br><strong>Scripture Reading:</strong> John 3:16-21<br><strong>Service:</strong> Sunday 9:00 AM</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->
</div>
<!-- /wp:group -->
