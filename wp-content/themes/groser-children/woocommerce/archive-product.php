<?php
/**
 * WooCommerce product archive — overrides groser parent template.
 * Renders the shop/category page using the Sabjiwala grid style.
 *
 * @package groser-children
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

?>
<main id="primary" class="site-main">
    <div style="max-width:1280px;margin:0 auto;padding:2rem 1rem;">

        <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
            <h2 class="sjw-section-title" style="margin-bottom:1.5rem;"><?php woocommerce_page_title(); ?></h2>
        <?php endif; ?>

        <?php do_action( 'woocommerce_before_main_content' ); ?>
        <?php do_action( 'woocommerce_archive_description' ); ?>

        <?php if ( woocommerce_product_loop() ) : ?>

            <?php do_action( 'woocommerce_before_shop_loop' ); ?>

            <div class="sjw-wc-grid">
                <?php woocommerce_product_loop_start(); ?>
                <?php if ( wc_get_loop_prop( 'total' ) ) : ?>
                    <?php while ( have_posts() ) : ?>
                        <?php the_post(); ?>
                        <?php do_action( 'woocommerce_shop_loop' ); ?>
                        <?php wc_get_template_part( 'content', 'product' ); ?>
                    <?php endwhile; ?>
                <?php endif; ?>
                <?php woocommerce_product_loop_end(); ?>
            </div>

            <?php do_action( 'woocommerce_after_shop_loop' ); ?>

        <?php else : ?>
            <?php do_action( 'woocommerce_no_products_found' ); ?>
        <?php endif; ?>

        <?php do_action( 'woocommerce_after_main_content' ); ?>
        <?php do_action( 'woocommerce_sidebar' ); ?>

    </div>
</main>

<?php get_footer( 'shop' ); ?>
