<?php
/**
* The template for displaying archive pages.
*
* @link https://codex.wordpress.org/Template_Hierarchy
*
* @package lawfirm
*/

get_header(); 
?>


<main>
<header class="news-header">
	<?php
	$archive_title = get_the_archive_title();
	$archive_title_remove_month = str_replace("Month: ", "", $archive_title);
	$archive_title_remove_category = str_replace("Category: ", "", $archive_title_remove_month);
	$archive_title_remove_tag = str_replace("Tag: ", "", $archive_title_remove_category);
	$archive_title_display = strtoupper($archive_title_remove_tag);
	?>
	<h2 style="text-align:center;">RESULTS FOR <?php echo "'".$archive_title_display."'";?></h2>
</header>
</main>



<?php 
endif;
get_footer(); 
?>