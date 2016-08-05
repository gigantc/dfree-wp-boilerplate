<?php
/**
 * News sidebar functions
 *
 * 
 * @package lawfirm
 */



if ( ! function_exists( 'lawfirm_news_popular_tags' ) ) :
  /**
   * displays popular tags on the news sidebar
   * @since 1.0.0
   */
  function lawfirm_news_popular_tags() {
    global $post;
    //setup arrays for storage
    $counts = [];
    $tag_links = [];

    //basic HTML stucture to fit the current core theme
    echo '<div class="widget tag-widget"><h3>Popular Tags</h3><ul>';

    //query all posts 
    $args = array( 
      'order' => 'DESC',
      'posts_per_page' => -1
    );
    $posts = new WP_Query( $args );
    if ( $posts->have_posts() ) :
      while ( $posts->have_posts() ): $posts->the_post();
        //echo "tag";
      $tags = wp_get_post_tags($post->ID);
      foreach ( $tags as $tag ) {
        $counts[$tag->name] = $tag->count;
        $tag_links[$tag->name] = get_tag_link( $tag->term_id );
      }
      endwhile;
    endif;
    wp_reset_postdata();


    //sort the count array for most used
    asort($counts);
    $counts = array_reverse( $counts, true );
    $i = 0;
    foreach ( $counts as $tag => $count ) {
      $i++;
      $tag_link = esc_url($tag_links[$tag]);
      $tag = str_replace(' ', '&nbsp;', esc_html( $tag ));
      if($i < 6){
        print "<li><a href=\"$tag_link\" data-count=\"$count\">$tag</a></li>";
      }
    }
    echo "</ul></div>";
  }
endif;




if ( ! function_exists( 'lawfirm_news_archive_list' ) ) :
  /**
   * displays a dropdown of the archive list
   * @since 1.0.0
   */
  function lawfirm_news_archive_list() {
    ?>
    
    <div class="widget archive-widget">
    <h3>Archive</h3>
    <select name="archive-dropdown" onchange='document.location.href=this.options[this.selectedIndex].value;'>
      <?php
      $dropdown_args = apply_filters( 'widget_archives_dropdown_args', array(
        'type' => 'monthly',
        'format' => 'option'
      ) ); 
      ?>

      <option value="">Select</option>
      <?php $archives = wp_get_archives($dropdown_args); ?>
    </select>
  </div>

  <?php
  }
endif;



if ( ! function_exists( 'lawfirm_news_search_box' ) ) :
  /**
   * displays a dropdown of the archive list
   * @since 1.0.0
   */
  function lawfirm_news_search_box() {
    ?>
    
    <div class="widget search-widget">
      <h3>Search</h3>
      <form method="get" id="searchform" action="<?php bloginfo('url'); ?>/">
        <input type="search" class="search-field" placeholder="Search..." name="s" id="s" />
        <button><i class="icon-search"></i></button>
      </form>
    </div>

  <?php
  }
endif;