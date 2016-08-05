<?php
  $settings = \Roots\ShareButtons\Admin\get_settings();

  global $post;
  $shares = '';
  if (empty($url)) $url = get_permalink();


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
              <li class="entry-share-btn entry-share-btn-twitter icon-twitter">
                <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode(html_entity_decode($title, ENT_COMPAT, 'UTF-8')); ?>&amp;url=<?php echo urlencode($url); ?>" title="<?php _e('Share on Twitter', 'roots_share_buttons'); ?>">
                </a>
              </li>
            <?php endif;
            break;
          case 'facebook':
            if (in_array('facebook', $settings['buttons'])) : ?>
              <li class="entry-share-btn entry-share-btn-facebook icon-facebook">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($url); ?>" title="<?php _e('Share on Facebook', 'roots_share_buttons'); ?>">
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


            // switch the way we get the URL if it's a News story vs project
            if($post_type == "post"){
              $thumb_src = phpUri::parse(network_site_url())->join($thumb_src);
            } elseif ($post_type == "project") {
              $thumb_src = get_field('core_project_featured_image', $post->ID);
            }

            // Fallback to post title as a description if the post thumbnail doesn't have one
            $description = (!empty($thumb_alt)) ? $thumb_alt : $title;

            if (in_array('pinterest', $settings['buttons'])) : ?>
              <li class="entry-share-btn entry-share-btn-pinterest icon-pinterest">
                <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode($url); ?>&amp;media=<?php echo urlencode($thumb_src); ?>&amp;description=<?php echo urlencode($description); ?>" title="<?php _e('Share on Pinterest', 'roots_share_buttons'); ?>">
                </a>
              </li>
            <?php endif;
            break;
        }
      }
    ?>
  </ul>
</div>
