<?php
/**
 * News and Posts functions
 *
 * 
 * @package lawfirm
 */


/**
 * Template functions used for posts.
 *
 * @package lawfirm
 */

if ( ! function_exists( 'lawfirm_post_header' ) ) {
  /**
   * Display the post header with a link to the single post
   * @since 1.0.0
   */
  function lawfirm_post_header() {
    the_title();
  }
}




if ( ! function_exists( 'lawfirm_post_content' ) ) {
  /**
   * Display the post content with a link to the single post
   * @since 1.0.0
   */
  function lawfirm_post_content() {
    ?>
    <div class="entry-content" itemprop="articleBody">
    <?php

    if ( is_home() || is_search() ) {
      the_excerpt();
    } else {
      the_content(
        sprintf(
          __( 'Continue reading %s', 'core' ),
          '<span class="screen-reader-text">' . get_the_title() . '</span>'
        )
      );
    }

    wp_link_pages( array(
      'before' => '<div class="page-links">' . __( 'Pages:', 'core' ),
      'after'  => '</div>',
    ) );
    ?>
    </div><!-- .entry-content -->
    <?php
  }
}





if ( ! function_exists( 'lawfirm_get_total_post_count' ) ) :
  /**
   * Get the total amount of news posts
   * @since 1.0.0
   */
  function lawfirm_get_total_post_count() {
    $args = array(
      'order' => 'DESC',
      'posts_per_page' => -1
    );
  $news_posts = new WP_Query($args); 
  $num_posts = $news_posts->post_count;
  //wp_reset_postdata();
  return $num_posts;
  }
endif;






if ( ! function_exists( 'lawfirm_get_total_post_count_archive' ) ) :
  /**
   * Get the total amount of news posts on an archive page
   * @since 1.0.0
   */
  function lawfirm_get_total_post_count_archive() {
    
    while ( have_posts() ) : the_post();
    global $wp_query;
    $total_posts = $wp_query->found_posts;
    endwhile;

    return $total_posts;

  }
endif;





if ( ! function_exists( 'lawfirm_load_news_posts' ) ) :
/**
 * Load all news posts via ajax
 * @since 1.0.0
 */
function lawfirm_load_news_posts() {

  $paged = $_POST['paged'];

  $project_args = array(
      'order' => 'DESC',
      'post_type' => 'project',
      'posts_per_page' => 1,
      'paged' => $paged
    );
  $project_posts = new WP_Query($project_args); 
  
  if ( $project_posts->have_posts() ): 
    while ( $project_posts->have_posts() ): $project_posts->the_post(); 
    
    $post_id = get_the_ID();
    $featured_image = get_field('lawfirm_project_featured_image');
    $state = get_field('lawfirm_project_state');
    $city = get_field('lawfirm_project_city');
    $summary = get_field('lawfirm_project_summary');
    $desc = lawfirm_concat_search_description($summary);
    ?>

      <article class="entry project">
        <h2 class="entry-title">PROJECT: <a href="<?= the_permalink(); ?>"><?php the_title(); ?></a></h2>
        
        <div class="entry-image" style="background:#eeeeee url('<?php echo $featured_image; ?>') center no-repeat; background-size:cover;"><img src="http://placehold.it/855x440" alt="<?php the_title() ?>" class="entry-image" style="opacity:0;"></div>
        <p class="entry-meta"><?php the_date('m.d.y'); ?>&nbsp;&nbsp;|&nbsp;&nbsp;<?=$city;?>, <?=$state;?></p>
        <p class="entry-excerpt"><?= $desc ?> <a href="<?= the_permalink(); ?>">Read More ></a></p>
      </article>

   <?php endwhile; 
   wp_reset_postdata();
  endif; 
  


  $args = array(
      'order' => 'DESC',
      'posts_per_page' => 4,
      'paged' => $paged
    );
  $news_posts = new WP_Query($args);
  $post_count = '1';

  if ( $news_posts->have_posts() ): 

      while ( $news_posts->have_posts() ): $news_posts->the_post(); 
      $post_id = get_the_id();
      //get the featured image
      $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'lawfirm_img_large' , false, '' ); 
      
      //get the location
      $number_of_posts = $news_posts->post_count;
      $location = get_field('location', $post_id);
      $location_post = $location;
      setup_postdata( $location_post );
      $city = get_the_title($location_post);
      $state = get_the_terms( $location_post, 'location-state' );
      $location_link = get_permalink($location_post);
      
      // sets the post to display as full width
      if ($number_of_posts == '1'){
        $full_width = '1';
      } elseif($number_of_posts == '3'){
        $full_width = '3';
      }


      //display all the stuffs
      ?>

      <?php if ($full_width == $post_count){
        echo "<div class='full-width'>";
      } ?>

      <article class="entry news-post-entry">

        <div class="blog-featured-img" style="background:#eeeeee url('<?php echo $featured_image[0]; ?>') center no-repeat; background-size:cover;">
          <img src="<?php echo $featured_image[0]; ?>" alt="<?php the_title(); ?>" class="entry-image" width=387 height=199 style="opacity:0;">
        </div>

        <?php if ($full_width == $post_count){
          echo "<div class='archive-info'>";
        } ?>

        <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        
        <p class="date-location"><?php echo get_the_date('m.d.y')."&nbsp;&nbsp;|&nbsp;&nbsp;"; ?>
          <?php 
          // get the city and state
          $location_post = get_field('location', $post_id);
          if( $location_post ){
            $city = $location_post->post_title;
            $state_obj = get_the_terms( $location_post, 'location-state' );
            $state = $state_obj[0]->name;
            $location_link = get_permalink($location_post);
            echo "<a href='".$location_link."'>".$city.", ".$state."</a>";
          } else {
            echo "Nationwide";
          }
          ?>
          </p>

        <?php 
          $excerpt = get_the_excerpt();
          $desc = lawfirm_concat_search_description($excerpt);
          ?>
          <p><?php echo $desc; ?>
          <a href="<?php the_permalink(); ?>">Read More ></a>
          </p>

          <?php if ($full_width == $post_count){
            echo "</div>";
          } ?>
      </article>

      <?php if ($full_width == $post_count){
        echo "</div>";
      } ?>

      <?php
      $post_count++;
      endwhile; 
      wp_reset_postdata();
  endif; 
  
  die();
}
endif;





if ( ! function_exists( 'lawfirm_load_archive_posts' ) ) :
/**
 * Load posts on an archive page
 * @since 1.0.0
 */
function lawfirm_load_archive_posts() {
  $paged = $_POST['paged'];
  
  if ( have_posts() ) :
    while ( have_posts() ) : the_post();
    $post_id = get_the_id();
    $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'lawfirm_img_large' , false, '' );
      //get the location
      $location = get_field('location', $post_id);
      $location_post = $location;
      setup_postdata( $location_post );
      $city = get_the_title($location_post);
      $state = get_the_terms( $location_post, 'location-state' );
      $location_link = get_permalink($location_post->ID);

      //display all the stuffs
      ?>
      <article class="entry news-post-entry archive-post-entry">
        
        <div class="blog-featured-img" style="background:#eeeeee url('<?php echo $featured_image[0]; ?>') center no-repeat; background-size:cover;">
          <img src="<?php echo $featured_image[0]; ?>" alt="<?php the_title(); ?>" class="entry-image" width=387 height=199 style="opacity:0;">
        </div>

        <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        
        <p class="entry-meta date-location"><?php echo get_the_date('m.d.y'); ?>
          <?php 
          if ( !in_category('nationwide') ){
            printf('&nbsp;&nbsp;|&nbsp;&nbsp;<a href="%s"> %s, %s</a>',$location_link, $city, $state[0]->name); 
          } else {
            echo('&nbsp;&nbsp;|&nbsp;&nbsp;Nationwide');
          }
          ?>
        </p>

        <?php 
          $excerpt = get_the_excerpt();
          $desc = lawfirm_concat_search_description($excerpt);
          ?>
          <p><?php echo $desc; ?>
          <a href="<?php the_permalink(); ?>">Read More ></a>
          </p>
      </article>
     <?php
    endwhile;
  endif;
  wp_reset_postdata();
}
endif;



