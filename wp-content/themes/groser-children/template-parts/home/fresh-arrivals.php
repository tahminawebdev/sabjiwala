<?php
/**
 * Today's Fresh Arrivals section.
 *
 * Shows the 6 most recent WooCommerce products. Falls back to static cards
 * (from sabji-fresh-roots FreshArrivals.jsx) when WooCommerce is inactive.
 */

$use_wc = class_exists( 'WooCommerce' );

if ( $use_wc ) {
    $products = wc_get_products( [
        'status'  => 'publish',
        'limit'   => 6,
        'orderby' => 'date',
        'order'   => 'DESC',
    ] );
}
?>
<section class="sjw-arrivals">
    <div class="sjw-arrivals__inner">
        <div class="sjw-arrivals__header">
            <h3 class="sjw-arrivals__title"><?php esc_html_e( "Today's Fresh Arrivals", 'groser-children' ); ?></h3>
            <a href="<?php echo esc_url( $use_wc ? wc_get_page_permalink( 'shop' ) : '#' ); ?>" class="sjw-arrivals__link">
                <?php esc_html_e( 'View All', 'groser-children' ); ?>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>

        <?php if ( $use_wc && ! empty( $products ) ) : ?>
            <!-- Live WooCommerce products -->
            <div class="sjw-products-grid">
                <?php foreach ( $products as $product ) : ?>
                    <div class="sjw-product-card">
                        <div class="sjw-product-card__img-wrap">
                            <?php if ( $product->is_on_sale() ) : ?>
                                <span class="sjw-product-card__badge"><?php esc_html_e( 'SALE', 'groser-children' ); ?></span>
                            <?php elseif ( ( time() - strtotime( $product->get_date_created() ) ) < DAY_IN_SECONDS * 7 ) : ?>
                                <span class="sjw-product-card__badge"><?php esc_html_e( 'FRESH', 'groser-children' ); ?></span>
                            <?php endif; ?>
                            <div class="sjw-product-card__img-bg">
                                <?php echo $product->get_image( 'sjw-product-thumb' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                            </div>
                        </div>
                        <div class="sjw-product-card__body">
                            <h4 class="sjw-product-card__name"><?php echo esc_html( $product->get_name() ); ?></h4>
                            <p class="sjw-product-card__weight">
                                <?php echo esc_html( $product->get_weight() ? $product->get_weight() . ' ' . get_option( 'woocommerce_weight_unit' ) : '' ); ?>
                            </p>
                            <div class="sjw-product-card__footer">
                                <span class="sjw-product-card__price"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
                                <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="sjw-product-card__add-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                                    <?php esc_html_e( 'Add', 'groser-children' ); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else : ?>
            <!-- Static fallback from sabji-fresh-roots -->
            <?php
            $static_products = [
                [ 'name' => 'Bottle Gourd (Lau)',    'weight' => '1.3 kg',       'price' => '£1.49',  'badge' => 'FRESH', 'img' => 'https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/1ea416ea3_generated_5bc7e8f1.png' ],
                [ 'name' => 'Hilsha Fish (Large)',   'weight' => '~800g – 700g', 'price' => '£12.99', 'badge' => 'FRESH', 'img' => 'https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/0e8eac12c_generated_012586f1.png' ],
                [ 'name' => 'Beef With Bone',        'weight' => '1kg',          'price' => '£6.49',  'badge' => 'FRESH', 'img' => 'https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/d0979f7c9_generated_c963f0fa.png' ],
                [ 'name' => 'Coriander (Dhania)',    'weight' => 'Bunch',        'price' => '£0.69',  'badge' => 'FRESH', 'img' => 'https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/9a5da718e_generated_85d6a36f.png' ],
                [ 'name' => 'Premium Basmati Rice',  'weight' => '5kg',          'price' => '£11.99', 'badge' => '',      'img' => 'https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/5b2d980a9_generated_f9047cb7.png' ],
                [ 'name' => 'Mustard Oil',           'weight' => '1 Litre',      'price' => '£3.99',  'badge' => '',      'img' => 'https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/7d5f361b9_generated_ff192790.png' ],
            ];
            ?>
            <div class="sjw-products-grid">
                <?php foreach ( $static_products as $p ) : ?>
                    <div class="sjw-product-card">
                        <div class="sjw-product-card__img-wrap">
                            <?php if ( $p['badge'] ) : ?>
                                <span class="sjw-product-card__badge"><?php echo esc_html( $p['badge'] ); ?></span>
                            <?php endif; ?>
                            <div class="sjw-product-card__img-bg">
                                <img src="<?php echo esc_url( $p['img'] ); ?>" alt="<?php echo esc_attr( $p['name'] ); ?>" loading="lazy" width="300" height="300">
                            </div>
                        </div>
                        <div class="sjw-product-card__body">
                            <h4 class="sjw-product-card__name"><?php echo esc_html( $p['name'] ); ?></h4>
                            <p class="sjw-product-card__weight"><?php echo esc_html( $p['weight'] ); ?></p>
                            <div class="sjw-product-card__footer">
                                <span class="sjw-product-card__price"><?php echo esc_html( $p['price'] ); ?></span>
                                <a href="#" class="sjw-product-card__add-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                                    <?php esc_html_e( 'Add to Cart', 'groser-children' ); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>
