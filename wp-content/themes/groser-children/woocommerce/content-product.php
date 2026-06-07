<?php
/**
 * Product loop item — groser-children override.
 * Adds a visible Add to Cart button below the product info.
 *
 * @package groser-children
 */

defined( 'ABSPATH' ) || exit;

global $product;
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}
?>
<li <?php wc_product_class( 'sjw-product-loop-item', $product ); ?>>

    <div class="product-holder position-relative">
        <?php woocommerce_template_loop_product_thumbnail(); ?>
        <a href="<?php echo esc_url( get_the_permalink() ); ?>" class="product__link" aria-label="<?php the_title_attribute(); ?>"></a>

        <?php do_action( 'woocommerce_before_shop_loop_item_title' ); ?>
    </div>

    <div class="product-info">
        <div class="product__review ul_li">
            <?php woocommerce_template_loop_rating(); ?>
        </div>
        <h2 class="product__title">
            <a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php the_title(); ?></a>
        </h2>
        <h4 class="product__price"><?php woocommerce_template_loop_price(); ?></h4>
    </div>

    <div class="sjw-loop-atc">
        <?php woocommerce_template_loop_add_to_cart( [ 'class' => 'sjw-atc-btn button alt' ] ); ?>
    </div>

</li>
