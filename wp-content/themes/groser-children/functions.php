<?php
/**
 * Groser Children — child theme functions
 *
 * Enqueues Google Fonts, parent theme stylesheet, and child stylesheet.
 * Inherits all WooCommerce support from the parent.
 * Adds Sabjiwala-specific WooCommerce overrides ported from groser-child.
 */

// -----------------------------------------------------------------------
// Enqueue styles & fonts
// -----------------------------------------------------------------------
add_action( 'wp_enqueue_scripts', 'groser_children_enqueue_styles' );
function groser_children_enqueue_styles() {
    $parent_version = wp_get_theme( 'groser' )->get( 'Version' );
    $child_version  = wp_get_theme()->get( 'Version' );

    // Google Fonts: Playfair Display + Inter
    wp_enqueue_style(
        'groser-children-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap',
        [],
        null
    );

    // Parent theme stylesheet
    wp_enqueue_style(
        'groser-style',
        get_template_directory_uri() . '/style.css',
        [],
        $parent_version
    );

    // Child stylesheet (style.css in this theme)
    wp_enqueue_style(
        'groser-children-style',
        get_stylesheet_uri(),
        [ 'groser-style' ],
        $child_version
    );
}

// -----------------------------------------------------------------------
// Chat widget JS — vanilla JS toggling the panel
// -----------------------------------------------------------------------
add_action( 'wp_enqueue_scripts', 'groser_children_enqueue_scripts' );
function groser_children_enqueue_scripts() {
    wp_enqueue_script(
        'groser-children-chat',
        get_stylesheet_directory_uri() . '/assets/js/chat-widget.js',
        [],
        wp_get_theme()->get( 'Version' ),
        true
    );
}

// -----------------------------------------------------------------------
// WooCommerce: disable SKU display globally
// -----------------------------------------------------------------------
add_filter( 'wc_product_sku_enabled', '__return_false' );

// -----------------------------------------------------------------------
// WooCommerce: remove category "posted in" from single product meta
// -----------------------------------------------------------------------
add_filter( 'woocommerce_product_meta_end', 'sjw_remove_categories_from_meta', 5 );
function sjw_remove_categories_from_meta( $html ) {
    return preg_replace( '/<span class="posted_in">.*?<\/span>/', '', $html );
}

// -----------------------------------------------------------------------
// WooCommerce: remove the product categories sidebar widget
// -----------------------------------------------------------------------
add_action( 'widgets_init', 'sjw_remove_category_widget', 11 );
function sjw_remove_category_widget() {
    unregister_widget( 'WC_Widget_Product_Categories' );
}

// -----------------------------------------------------------------------
// WooCommerce: wrap product loops on the front-page in our grid class
// -----------------------------------------------------------------------
add_filter( 'woocommerce_product_loop_start', 'sjw_wrap_product_loop_start' );
function sjw_wrap_product_loop_start( $html ) {
    return '<div class="sjw-wc-grid">' . $html;
}

add_filter( 'woocommerce_product_loop_end', 'sjw_wrap_product_loop_end' );
function sjw_wrap_product_loop_end( $html ) {
    return $html . '</div>';
}

// -----------------------------------------------------------------------
// Custom image sizes used by child theme sections
// -----------------------------------------------------------------------
add_action( 'after_setup_theme', 'groser_children_image_sizes' );
function groser_children_image_sizes() {
    add_image_size( 'sjw-product-thumb', 300, 300, true );
    add_image_size( 'sjw-category-circle', 160, 160, true );
    add_image_size( 'sjw-hero', 720, 600, false );
}
