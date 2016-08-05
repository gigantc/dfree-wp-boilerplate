<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package lawfirm
 */

?>
    <footer class="global-footer" data-ajaxurl="<?php echo admin_url('admin-ajax.php'); ?>">
    
    </footer>

    </div><!-- #page -->
    <?php wp_footer(); 

    // add Google Anlaytics if we are on the production site
    if ($_SERVER['HTTP_HOST']==="XXXXXXX.com" || $_SERVER['HTTP_HOST']==="www.XXXXXXX.com") { 
        echo "<script>
                window.ga=function(){ga.q.push(arguments)};ga.q=[];ga.l=+new Date;
                ga('create','UA-XXXXXXX-1','auto');ga('send','pageview')
              </script>
              <script src='https://www.google-analytics.com/analytics.js' async defer></script>";
    } ?>

  </body>
</html>
