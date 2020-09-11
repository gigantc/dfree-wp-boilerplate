<?php
/**
 * Block Name: Basic Bullet List
 *
 * This is the template that an simple ul>li element
 */
?>

<span class="wrap basic-list">
<ul>
<?php
if( have_rows('block_basic_list') ):
    while( have_rows('block_basic_list') ) : the_row();

        $item = get_sub_field('item'); ?>
        <li><p><?= $item ?></p></li>
       
   <?php endwhile;
endif; ?>
</ul>
</span>