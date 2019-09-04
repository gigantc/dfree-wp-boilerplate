<?php
/**
* The Homepage.
*
* @package lawfirm
*/
get_header(); ?>

<main>

  
  <?php  
  while ( have_posts() ) : the_post();
    the_content(); 
  endwhile; // End of the loop.
  ?>
  

</main>

<?php get_footer(); ?>