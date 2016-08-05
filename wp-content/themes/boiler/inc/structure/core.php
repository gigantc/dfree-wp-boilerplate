<?php
/**
 * Core functions for messing with the front-end and stuff
 * 
 * 
 * @package lawfirm
 */




if ( ! function_exists( 'lawfirm_get_bg_img' ) ) :
  /**
   * returns the background image style tag)
   * ('field_name'; 'image_size', "is a sub field"
   * used to turn a user added image into a background image
   */
  function lawfirm_get_bg_img($field, $img_size, $sub = null) {
    if($sub){
      $img_id = get_sub_field($field);
    } else {
      $img_id = get_field($field);
    }
    
    $attr = '';
    
      switch ($img_size) {
          case 's': $size = 'lawfirm_img_small'; break; //640
          case 'm': $size = 'lawfirm_img_medium'; break; //800
          case 'l': $size = 'lawfirm_img_large'; break; //1000
          case 'xl': $size = 'lawfirm_img_x_large'; break; //1500
          case 'f': $size = 'lawfirm_img_full'; break; //2000
          case 'sq': $size = 'lawfirm_img_square'; break; //800
      }

      $img = wp_get_attachment_image_src( $img_id, $size, false, $attr );
      $style = 'style="background-image: url('.$img[0].');" background-size:cover;';
      return $style;

  }
endif;


/**
 * Custom [share] shortcode template
 * Adjuest shortcode-share.php to change the HTML of Roots share buttons code
 */
function custom_roots_share_buttons_template() {
  return get_template_directory() . '/template-parts/shortcode-share.php';
}
add_action('roots/share_template', 'custom_roots_share_buttons_template');