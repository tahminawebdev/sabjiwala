<?php
/**
 * Groser Child Theme functions
 */

// Enqueue parent and child theme styles
add_action( 'wp_enqueue_scripts', function() {
    $parent_handle = 'groser-style';

    // Enqueue parent style first
    wp_enqueue_style( $parent_handle, get_template_directory_uri() . '/style.css', [], wp_get_theme( 'groser' )->get( 'Version' ) );

    // Then child style, dependent on parent
    wp_enqueue_style( 'groser-child-style', get_stylesheet_uri(), [ $parent_handle ], wp_get_theme()->get( 'Version' ) );
});

// Optional: make sure WooCommerce declares product categories taxonomy visibility in menus if WC active
add_action( 'after_setup_theme', function(){
    // No override needed; WooCommerce registers product_cat taxonomy.
    // This hook exists to keep child theme minimal and future-safe.
});


// Disable SKU everywhere (product page, cart, etc.)
add_filter( 'wc_product_sku_enabled', '__return_false' );

// Remove Categories from product meta on single product page
add_filter( 'woocommerce_product_meta_end', 'tc_remove_categories_from_meta', 5 );

function tc_remove_categories_from_meta( $html ) {
    // Remove the whole "posted in" (categories) part from meta HTML
    $html = preg_replace( '/<span class="posted_in">.*?<\/span>/', '', $html );
    return $html;
}

// Disable SKU everywhere
add_filter( 'wc_product_sku_enabled', '__return_false' );

function remove_shop_category_widget() {
    unregister_widget( 'WC_Widget_Product_Categories' );
}
add_action( 'widgets_init', 'remove_shop_category_widget', 11 );



