<?php
/**
* The template for displaying search results pages.
*
* @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
*
* @package thelawfirm
*/

get_header(); 


$search_results = get_search_query();
?>

<main>

<section class="search" id="top">
  <header class="search-header">
    <h2>RESULTS FOR <?php echo "'".$search_results."'";?></h2>
  </header>

</section>
<br /><br />

      

</main>
<?php get_footer(); ?>
