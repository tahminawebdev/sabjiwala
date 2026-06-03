<?php
/**
 * Front page template — Sabjiwala storefront
 *
 * Rendered when WordPress > Settings > Reading is set to a static front page,
 * or automatically as the homepage when the theme is active.
 *
 * @package groser-children
 */

get_header();
?>

<main id="primary" class="site-main">
    <?php get_template_part( 'template-parts/home/hero' ); ?>
    <?php get_template_part( 'template-parts/home/trust-badges' ); ?>
    <?php get_template_part( 'template-parts/home/shop-by-category' ); ?>
    <?php get_template_part( 'template-parts/home/fresh-arrivals' ); ?>
    <?php get_template_part( 'template-parts/home/promo-section' ); ?>
</main>

<?php get_footer(); ?>
