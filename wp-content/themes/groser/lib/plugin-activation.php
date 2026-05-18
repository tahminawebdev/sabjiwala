<?php

require_once get_template_directory() . '/lib/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'groser_register_required_plugins' );

function groser_register_required_plugins() {

    $plugins = array(
        array(
			'name'               => esc_html__('Groser Tools', 'groser'),
			'slug'               => 'groser-tools',
			'source'             => 'https://themexriver.com/wp/groser/tools/groser-tools.zip', 
			'required'           => true,
			'force_activation'   => false,
			'force_deactivation' => false,
		),
        array(
            'name'               => esc_html__('Slider Revolution', 'groser'),
            'slug'               => 'revslider',
            'source'             => 'https://themexriver.com/wp/groser/tools/revslider.zip',
            'required'           => true,
        ), 
        array(
            'name'     => 'Elementor Website Builder',
            'slug'     => 'elementor',
            'required' => true,
        ),
        array(
            'name'     => 'FiboSearch – Ajax Search for WooCommerce',
            'slug'     => 'ajax-search-for-woocommerce',
            'required' => true,
        ),
        array(
            'name'     => 'WooCommerce',
            'slug'     => 'woocommerce',
            'required' => true,
        ),
        array(
            'name'     => 'WPC Smart Quick View for WooCommerce',
            'slug'     => 'woo-smart-quick-view',
            'required' => true,
        ),
        array(
            'name'     => 'WPC Smart Wishlist for WooCommerce',
            'slug'     => 'woo-smart-wishlist',
            'required' => true,
        ),        
        array(
            'name'     => 'Variation Swatches for WooCommerce',
            'slug'     => 'woo-variation-swatches',
            'required' => true,
        ),
        array(
            'name'     => 'WPC Fly Cart for WooCommerce',
            'slug'     => 'woo-fly-cart',
            'required' => true,
        ),
        
        array(
            'name'     => esc_html__('Contact Form 7', 'groser'),
            'slug'     => 'contact-form-7',
            'required' => false,
        ),
        array(
            'name'     => esc_html__('SVG Support', 'groser'),
            'slug'     => 'svg-support',
            'required' => false,
        ),

        array(
            'name'     => esc_html__('MC4WP: Mailchimp for WordPress', 'groser'),
            'slug'     => 'mailchimp-for-wp',
            'required' => false,
        ),
        array(
            'name'               => esc_html__('Slider Revolution', 'groser'),
            'slug'               => 'revslider',
            'source'             => 'https://themexriver.com/wp/groser/tools/plugin/revslider.zip',
            'required'           => true,
        ), 
        array(
            'name'     => esc_html__( 'One Click Demo Import', 'groser' ),
            'slug'     => 'one-click-demo-import',
            'required' => false,
        ),
        array(
            'name'     => esc_html__( 'Breadcrumb NavXT', 'groser' ),
            'slug'     => 'breadcrumb-navxt',
            'required' => false,
        ),

    );

    $config = array(
        'id'           => 'groser',
        'default_path' => '',
        'menu'         => 'tgmpa-install-plugins',
        'dismissable'  => true,
        'dismiss_msg'  => '',
        'is_automatic' => false,
        'message'      => '',

    );

    tgmpa( $plugins, $config );
}
