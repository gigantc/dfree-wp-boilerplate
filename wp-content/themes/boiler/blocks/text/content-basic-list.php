<?php
/**
 * Block Name: Basic Bullet List
 *
 * This is the template that an simple ul>li element
 */

// render the example image pop-up in the gutenburg admin
if (get_field('is_example')) : ?>


    <img src="<?= get_template_directory_uri() ?>/blocks/basics/image-center.jpg" />


<?php 
// render the block in the browser
else : ?>

<span class="basic-list">
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


<?php endif; ?>