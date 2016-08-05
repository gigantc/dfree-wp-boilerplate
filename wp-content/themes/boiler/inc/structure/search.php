<?php
/**
 * Search 
 *
 * 
 * @package lawfirm
 */


if ( ! function_exists( 'lawfirm_load_search_posts' ) ) :
/**
 * Load posts a search page
 * @since 1.0.0
 */
function lawfirm_load_search_posts() {

  if ( have_posts() ) {
    while ( have_posts() ) : the_post();
    
    $post_id = get_the_id();
    
    //type of post
    $is_a_location = get_post_meta($post_id, 'location_header_address_cityzip');
    $is_a_project = get_post_meta($post_id, 'lawfirm_project_code');
    $type = get_post_type();
    
    //
    //
    // LOCATION POST
    //
    //
    if($type = "post" && $is_a_location){ 

      $featured_image_id = get_field('location_header_hero_image');
      $featured_image = wp_get_attachment_image_src( $featured_image_id, 'lawfirm_img_large' );
      ?>
      <article class="entry">
        
        <div class="blog-featured-img" style="background:#eeeeee url('<?php echo $featured_image[0]; ?>') center no-repeat; background-size:cover;">
          <img src="<?php echo $featured_image[0]; ?>" alt="<?php the_title(); ?>" class="entry-image" width=387 height=199 style="opacity:0;">
        </div>
        <?php 
        $excerpt = get_field('location_header_office_description');
        $desc = lawfirm_concat_search_description($excerpt);
        ?>
        <div class="archive-info">
          <h2 class="entry-title"><span>Office:</span><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <p><?php echo $desc; ?>
            <a href="<?php the_permalink(); ?>">Read More ></a>
          </p>
        </div>
      </article>
      <?php
    //
    //
    // PROJECT POST
    //
    //
    } elseif ($type = "post" && $is_a_project){ ?>

      <article class="entry">
        
        <div class="blog-featured-img" style="background:#eeeeee url('<?php echo the_field('lawfirm_project_featured_image', $post_id); ?>') center no-repeat; background-size:cover;">
          <img src="<?php echo the_field('lawfirm_project_featured_image', $post_id); ?>" alt="<?php the_title(); ?>" class="entry-image" width=387 height=199 style="opacity:0;">
        </div>
        <?php 
        $excerpt = get_field('lawfirm_project_summary');
        $desc = lawfirm_concat_search_description($excerpt);
        ?>
        <div class="archive-info">
          <h2 class="entry-title"><span>Project:</span><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <p><?php echo $desc; ?>
            <a href="<?php the_permalink(); ?>">Read More ></a>
          </p>
        </div>
      </article>
      <?php
    //
    //
    // NEWS POST
    //
    //
    } else {

      $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'lawfirm_img_large' , false, '' );
      //get the location
      $location = get_field('location', $post_id);
      $location_post = $location;
      setup_postdata( $location_post );
      $city = get_the_title($location_post);
      $state = get_the_terms( $location_post, 'location-state' );
      $location_link = get_permalink($location_post->ID);
      ?>
      <article class="entry">
        
        
        <div class="blog-featured-img" style="background:#eeeeee url('<?php echo $featured_image[0]; ?>') center no-repeat; background-size:cover;">
          <img src="<?php echo $featured_image[0]; ?>" alt="<?php the_title(); ?>" class="entry-image" width=387 height=199 style="opacity:0;">
        </div>

        <div class="archive-info">
          <h2 class="entry-title"><span>News:</span><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          
          <p class="entry-meta"><?php echo get_the_date('m.d.y')."&nbsp;&nbsp;|&nbsp;&nbsp;"; ?>
            <?php printf('<a href="%s"> %s, %s</a>',$location_link, $city, $state[0]->name); ?>
          </p>
          <?php 
          $excerpt = get_the_excerpt();
          $desc = lawfirm_concat_search_description($excerpt);
          ?>
          <p><?php echo $desc; ?>
          <a href="<?php the_permalink(); ?>">Read More ></a>
          </p>
        </div>
      </article>
      
      <?php }

    endwhile;
  } else { 
    // results if nothing is found
    ?>
     <article style='margin:25px 0px 400px 0px;'>
        <p>No results were found.</p>
      </article>
  <?php
  }
  wp_reset_postdata();
}
endif;




if ( ! function_exists( 'lawfirm_concat_search_description' ) ) :
/**
 * Load posts a search page
 * @since 1.0.0
 */
function lawfirm_concat_search_description($str) {
    $limit=250;
    $strip = false;
    $str = ($strip == true)?strip_tags($str):$str;
    if (strlen ($str) > $limit) {
        $str = substr ($str, 0, $limit - 3);
        return (substr ($str, 0, strrpos ($str, ' ')).'...');
    }
    return trim($str);
}
endif;