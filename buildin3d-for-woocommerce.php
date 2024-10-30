<?php
include_once(ABSPATH . 'wp-includes/pluggable.php');
/*
  Plugin Name: BuildIn3D for WooCommerce
  Plugin URI:  https://www.axlessoft.com
  Description: This plugin allows you to show a 3D animated instruction as a procts' image with no hassle in less than 30 seconds.
  Version: 0.9.1
  Author: Mihail Kirilov
  Text Domain: axlessoft
  Author URI: https://www.linkedin.com/in/mihail-kirilov-760b725b/

  Copyright: © 2021 Axlessoft
  License: GPLv2 or later
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

function axlessoft_return_instruction_steps_viewer_for_woo($product_id ,$image_src){
  // Returns the HTML of the viewer or false in order to fallback to default

  // This is an easy way to place the image inside the provided link from B3 so it shows up there.
  $b3_embed_code = axlessoft_return_embed_code($product_id);

  if ( $b3_embed_code == '')
    return false;
  
  $b3_embed_code = str_replace('</a>', '<img src="' . $image_src . '"></a>', $b3_embed_code);

  $html = '<div class="woocommerce-product-gallery__image woocommerce_single"
        data-thumb="' . $image_src  .'"
        style="height: 417px;"
      >' . $b3_embed_code . '</div>';
  return $html;
}

add_filter('woocommerce_single_product_image_thumbnail_html', function($html, $post_thumbnail_id){
  global $axlessoft_flag_for_first_image_product;

  if (!$axlessoft_flag_for_first_image_product) {
    $image_attributes = wp_get_attachment_image_src($post_thumbnail_id, array(400, 400));
    if ($image_attributes) {
      $image_src = $image_attributes[0];
    }else{
      $image_src = esc_url( wc_placeholder_img_src( 'woocommerce_single' ) );
    }
    
    $_html = axlessoft_return_instruction_steps_viewer_for_woo(get_the_ID(), $image_src);
    if ($_html)
      $html = $_html;
    $axlessoft_flag_for_first_image_product = true;
  }

  return $html;
}, 1, 2);

add_action( 'woocommerce_process_product_meta', function( $post_id ) {

  $strip_tags = isset( $_POST[ '_b3_embed_src' ] ) ? sanitize_text_field($_POST['_b3_embed_src']) : '';

  update_post_meta($post_id, '_b3_embed_src', $strip_tags );
});



add_filter( 'woocommerce_product_data_tabs', function( $tabs ) {
  // Create a custom b3 tab
  $tabs['custom_tab'] = array(
    'label'  => __( 'BuildIn3D', 'axlessoft' ),
    'target' => 'buildin3d_panel',
    'class'  => array(),
  );

  return $tabs;
});


add_action( 'woocommerce_product_data_panels', function() {
  // Create a custom b3 panel
  echo '<div id="buildin3d_panel" class="panel woocommerce_options_panel">';
  echo '  <div class="options_group">';

  // Creating all the fields and links needed in the panel
  $_b3_embed_src_args = array(
    'label' => 'B3 Embed Code',
    'id' => '_b3_embed_src',
    'rows' => 5,
    'placeholder' => "Get your embed code from https://platform.buildin3d.com",
    // TODO: Add a link to instructions on how to get the code.
    'description' => '</br></br><a href="https://platform.buildin3d.com/instructions/198-fabbrix-jungle-life-gorilla-in-3d-building-instructions">Get code for the gorilla instruction</a></br><a href="https://wordpress.org/plugins/buildin3d-for-woocommerce/#description">Help</a>'
  );
  woocommerce_wp_textarea_input( $_b3_embed_src_args );
  echo '<p><a target="_blank" href="https://platform.buildin3d.com/instructions">All avaliable 3D models.</a></p>';

  // Closing the panel
  echo '  </div>';
  echo '</div>';

});

function axlessoft_return_embed_code( $product_id ){
  $b3_embed_src = get_post_meta($product_id, "_b3_embed_src", true);

  if ( !$b3_embed_src) {
    return '';
  }

  return '<a class="buildin3d-instructions" href="' . $b3_embed_src .'" width="100%" height="100%"></a><script async src="https://platform.buildin3d.com/embed_widget.js"></script>';
}

