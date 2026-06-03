<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">

<?php get_template_part( 'template-parts/home/topbar' ); ?>

<!-- ==============================
     STICKY HEADER
     ============================== -->
<header id="masthead" class="sjw-header">
    <div class="sjw-header__inner">

        <!-- Mobile menu toggle -->
        <button class="sjw-header__mobile-toggle" aria-label="<?php esc_attr_e( 'Toggle menu', 'groser-children' ); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
            </svg>
        </button>

        <!-- Logo -->
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="sjw-header__logo">
            <?php
            if ( has_custom_logo() ) {
                the_custom_logo();
            } else { ?>
                <img
                    src="https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/e343c0e74_generated_dad3d2ee.png"
                    alt="<?php bloginfo( 'name' ); ?>"
                    class="sjw-header__logo-img"
                    width="56"
                    height="56"
                >
            <?php } ?>
            <div class="sjw-header__logo-text">
                <p class="sjw-header__logo-title"><?php bloginfo( 'name' ); ?></p>
                <p class="sjw-header__logo-sub"><?php bloginfo( 'description' ); ?></p>
            </div>
        </a>

        <!-- Desktop search -->
        <div class="sjw-header__search">
            <?php get_search_form(); ?>
        </div>

        <!-- Action icons -->
        <div class="sjw-header__actions">
            <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>" class="sjw-header__action-btn" aria-label="<?php esc_attr_e( 'My Account', 'groser-children' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
                    <span class="sjw-header__action-label"><?php esc_html_e( 'My Account', 'groser-children' ); ?></span>
                </a>
            <?php else : ?>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="sjw-header__action-btn" aria-label="<?php esc_attr_e( 'Login / Account', 'groser-children' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
                    <span class="sjw-header__action-label"><?php esc_html_e( 'Login / Account', 'groser-children' ); ?></span>
                </a>
            <?php endif; ?>

            <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>" class="sjw-header__action-btn" aria-label="<?php esc_attr_e( 'Cart', 'groser-children' ); ?>">
                    <span class="sjw-header__cart-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                        <span class="sjw-header__cart-count"><?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?></span>
                    </span>
                    <span class="sjw-header__action-label">
                        <?php esc_html_e( 'Cart', 'groser-children' ); ?>
                        <?php if ( WC()->cart ) : ?>
                            <?php echo wc_price( WC()->cart->get_cart_contents_total() ); ?>
                        <?php endif; ?>
                    </span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mobile search bar -->
    <div class="sjw-header__mobile-search">
        <input type="search" placeholder="<?php esc_attr_e( 'Search for products...', 'groser-children' ); ?>" aria-label="<?php esc_attr_e( 'Search', 'groser-children' ); ?>">
        <button class="sjw-header__mobile-search-btn" aria-label="<?php esc_attr_e( 'Search', 'groser-children' ); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
        </button>
    </div>
</header>

<?php get_template_part( 'template-parts/home/category-nav' ); ?>
