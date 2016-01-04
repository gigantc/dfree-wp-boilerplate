<?php
  $settings = \Roots\ShareButtons\Admin\get_settings();

  global $post;
  $shares = '';
  if (empty($url)) $url = get_permalink();

  $thumb_id = get_post_thumbnail_id($post->ID);

  if (empty($title)) $title = get_the_title();

  if (in_array('enabled', $settings['share_count'])) {
    $shares           = new \Roots\ShareButtons\ShareCount\shareCount($url);
    $shares_twitter   = $shares->get_tweets();
    $shares_fb        = $shares->get_fb();
    $shares_gplus     = $shares->get_plusones();
    $shares_linkedin  = $shares->get_linkedin();
    $shares_pinterest = $shares->get_pinterest();
  }
?>

<div class="entry-share">
  <ul class="entry-share-btns">
    <?php
      foreach($settings['button_order'] as $setting) {
        switch($setting) {
          case 'twitter':
            if (in_array('twitter', $settings['buttons'])) : ?>
              <li class="entry-share-btn entry-share-btn-twitter">
                <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode(html_entity_decode($title, ENT_COMPAT, 'UTF-8')); ?>&amp;url=<?php echo urlencode($url); ?>" title="<?php _e('Share on Twitter', 'roots_share_buttons'); ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 64 64"><path d="M64 12.15c-2.355 1.045-4.885 1.75-7.54 2.068 2.71-1.625 4.792-4.198 5.772-7.264-2.537 1.505-5.347 2.597-8.338 3.186-2.395-2.552-5.808-4.146-9.584-4.146-7.25 0-13.13 5.88-13.13 13.13 0 1.03.116 2.03.34 2.992-10.912-.548-20.588-5.775-27.064-13.72-1.13 1.94-1.778 4.196-1.778 6.602 0 4.556 2.318 8.575 5.84 10.93-2.15-.07-4.176-.66-5.946-1.643v.165c0 6.362 4.525 11.668 10.532 12.875-1.102.3-2.262.46-3.46.46-.845 0-1.668-.082-2.47-.235 1.672 5.216 6.52 9.013 12.267 9.118-4.493 3.522-10.154 5.62-16.306 5.62-1.06 0-2.105-.06-3.132-.183 5.81 3.726 12.713 5.9 20.128 5.9 24.15 0 37.358-20.008 37.358-37.36 0-.568-.013-1.135-.038-1.698 2.566-1.85 4.792-4.164 6.552-6.797z" fill="#fff"></path></svg>
                  <b><?php _e('Tweet', 'roots_share_buttons'); ?></b>
                  <?php if ($shares) : ?>
                    <span class="count"><?php echo $shares_twitter; ?></span>
                  <?php endif; ?>
                </a>
              </li>
            <?php endif;
            break;
          case 'facebook':
            if (in_array('facebook', $settings['buttons'])) : ?>
              <li class="entry-share-btn entry-share-btn-facebook">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($url); ?>" title="<?php _e('Share on Facebook', 'roots_share_buttons'); ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 64 64"><path d="M42 12c-5.523 0-10 4.477-10 10v6h-8v8h8v28h8V36h9l2-8H40v-6c0-1.105.895-2 2-2h10v-8H42z" fill="#fff"></path></svg>
                  <b><?php _e('Share', 'roots_share_buttons'); ?></b>
                  <?php if ($shares) : ?>
                    <span class="count"><?php echo $shares_fb; ?></span>
                  <?php endif; ?>
                </a>
              </li>
            <?php endif;
            break;
          case 'google_plus':
            if (in_array('google_plus', $settings['buttons'])) : ?>
              <li class="entry-share-btn entry-share-btn-google-plus">
                <a href="https://plus.google.com/share?url=<?php echo urlencode($url); ?>" title="<?php _e('Share on Google+', 'roots_share_buttons'); ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 64 64"><path d="M34.942 4H18.196C10.688 4 3.623 9.688 3.623 16.276c0 6.733 5.118 12.167 12.755 12.167.53 0 1.047-.01 1.553-.047-.495.95-.85 2.018-.85 3.128 0 1.87 1.007 3.388 2.28 4.627-.962 0-1.89.03-2.903.03C7.157 36.18 0 42.1 0 48.242c0 6.05 7.847 9.832 17.147 9.832 10.602 0 16.457-6.015 16.457-12.064 0-4.85-1.43-7.754-5.855-10.882-1.515-1.072-4.41-3.677-4.41-5.21 0-1.794.513-2.678 3.215-4.79 2.77-2.163 4.73-5.205 4.73-8.744 0-4.213-1.876-8.32-5.398-9.673h5.31l3.748-2.708zm-5.85 40.966c.134.56.206 1.138.206 1.727 0 4.888-3.15 8.707-12.186 8.707-6.428 0-11.07-4.07-11.07-8.956 0-4.79 5.758-8.778 12.185-8.708 1.5.016 2.898.257 4.167.668 3.49 2.427 5.992 3.798 6.698 6.563zm-10.29-18.23c-4.316-.13-8.416-4.827-9.16-10.49-.745-5.668 2.148-10.004 6.462-9.875 4.313.13 8.415 4.677 9.16 10.342s-2.15 10.154-6.462 10.024zM52 16V4h-4v12H36v4h12v12h4V20h12v-4z" fill="#fff"></path></svg>
                  <b><?php _e('+1', 'roots_share_buttons'); ?></b>
                  <?php if ($shares) : ?>
                    <span class="count"><?php echo $shares_gplus; ?></span>
                  <?php endif; ?>
                </a>
              </li>
            <?php endif;
            break;
          case 'linkedin':
            if (in_array('linkedin', $settings['buttons'])) : ?>
              <li class="entry-share-btn entry-share-btn-linkedin">
                <a href="http://www.linkedin.com/shareArticle?mini=true&amp;url=<?php echo urlencode($url); ?>&amp;summary=" title="<?php _e('Share on LinkedIn', 'roots_share_buttons'); ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 64 64"><path d="M53.25 0h-42.5C4.838 0 0 4.838 0 10.75v42.5C0 59.163 4.838 64 10.75 64h42.5C59.163 64 64 59.163 64 53.25v-42.5C64 4.838 59.163 0 53.25 0zM24 52h-8V24h8v28zm-4-32c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm32 32h-8V36c0-2.21-1.79-4-4-4s-4 1.79-4 4v16h-8V24h8v4.967C37.65 26.7 40.172 24 43 24c4.97 0 9 4.477 9 10v18z" fill="#fff"></path></svg>
                  <b><?php _e('Share', 'roots_share_buttons'); ?></b>
                  <?php if ($shares) : ?>
                    <span class="count"><?php echo $shares_linkedin; ?></span>
                  <?php endif; ?>
                </a>
              </li>
            <?php endif;
            break;
          case 'pinterest':
            // Don't show 'Pin It' button if post doesn't have a thumbnail
            if (empty($thumb_id)) break;

            // Get thumbnail URL
            $thumb = wp_get_attachment_image_src($thumb_id, 'thumbnail_size');
            $thumb_src = (isset($thumb[0])) ? $thumb[0] : null;
            $thumb_alt = get_post_meta($thumb_id , '_wp_attachment_image_alt', true);

            // Make sure thumbnail URL isn't relative
            $thumb_src = phpUri::parse(network_site_url())->join($thumb_src);

            // Fallback to post title as a description if the post thumbnail doesn't have one
            $description = (!empty($thumb_alt)) ? $thumb_alt : $title;

            if (in_array('pinterest', $settings['buttons'])) : ?>
              <li class="entry-share-btn entry-share-btn-pinterest">
                <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode($url); ?>&amp;media=<?php echo urlencode($thumb_src); ?>&amp;description=<?php echo urlencode($description); ?>" title="<?php _e('Share on Pinterest', 'roots_share_buttons'); ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 64 64"><path d="M32 0C14.327 0 0 14.327 0 32c0 13.557 8.434 25.135 20.338 29.798-.28-2.532-.532-6.415.11-9.18.582-2.497 3.754-15.906 3.754-15.906s-.957-1.916-.957-4.75c0-4.45 2.58-7.77 5.79-7.77 2.73 0 4.05 2.05 4.05 4.507 0 2.744-1.75 6.85-2.65 10.653-.755 3.186 1.596 5.784 4.737 5.784 5.688 0 10.06-5.998 10.06-14.654 0-7.662-5.505-13.02-13.367-13.02-9.105 0-14.45 6.83-14.45 13.888 0 2.75 1.06 5.7 2.382 7.304.26.317.3.595.222.917-.244 1.012-.784 3.186-.89 3.63-.14.587-.464.71-1.07.43-3.997-1.862-6.495-7.705-6.495-12.398 0-10.094 7.334-19.365 21.143-19.365 11.1 0 19.727 7.91 19.727 18.482 0 11.028-6.953 19.903-16.605 19.903-3.243 0-6.29-1.685-7.334-3.675 0 0-1.605 6.11-1.993 7.607-.723 2.78-2.673 6.264-3.978 8.39C25.52 63.5 28.7 64 32 64c17.673 0 32-14.326 32-32S49.673 0 32 0z" fill="#fff"></path></svg>
                  <b><?php _e('Pin it', 'roots_share_buttons'); ?></b>
                  <?php if ($shares) : ?>
                    <span class="count"><?php echo $shares_pinterest; ?></span>
                  <?php endif; ?>
                </a>
              </li>
            <?php endif;
            break;
        }
      }
    ?>
  </ul>
</div>
