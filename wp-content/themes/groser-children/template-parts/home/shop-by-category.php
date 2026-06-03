<?php
/**
 * Shop by category section.
 *
 * Pulls top-level WooCommerce product categories ordered by menu order.
 * Falls back to hard-coded Sabjiwala defaults (with Base44 CDN images) when
 * WooCommerce is inactive or no categories exist.
 */

$cats = [];

if ( class_exists( 'WooCommerce' ) ) {
    $terms = get_terms( [
        'taxonomy'   => 'product_cat',
        'orderby'    => 'menu_order',
        'order'      => 'ASC',
        'hide_empty' => false,
        'parent'     => 0,
        'exclude'    => [ get_option( 'default_product_cat', 0 ) ],
        'number'     => 8,
    ] );
    if ( ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            $thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
            $image_url    = $thumbnail_id
                ? wp_get_attachment_image_url( $thumbnail_id, 'sjw-category-circle' )
                : wc_placeholder_img_src( 'sjw-category-circle' );

            $cats[] = [
                'name' => $term->name,
                'url'  => get_term_link( $term ),
                'img'  => $image_url,
            ];
        }
    }
}

// Fallback static categories from sabji-fresh-roots
if ( empty( $cats ) ) {
    $cats = [
        [ 'name' => 'Vegetables',         'url' => '#', 'img' => 'https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/26ce627c1_generated_187b942d.png' ],
        [ 'name' => 'Fruits',             'url' => '#', 'img' => 'https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/3fa8aaf45_generated_b80a28ce.png' ],
        [ 'name' => 'Rice & Flour',       'url' => '#', 'img' => 'https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/bc4997696_generated_a8e30237.png' ],
        [ 'name' => 'Meat',               'url' => '#', 'img' => 'https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/391fd7676_generated_3e95df38.png' ],
        [ 'name' => 'Fish & Seafood',     'url' => '#', 'img' => 'https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/30a858ed6_generated_1541d4b6.png' ],
        [ 'name' => 'Spices & Pulses',    'url' => '#', 'img' => 'https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/e10d0d526_generated_ccd5b657.png' ],
        [ 'name' => 'Frozen Food',        'url' => '#', 'img' => 'https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/c7cfd59d1_generated_c10c7b5c.png' ],
        [ 'name' => 'Drinks & Beverages', 'url' => '#', 'img' => 'https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/e5563a864_generated_aa2af954.png' ],
    ];
}
?>
<section class="sjw-categories">
    <div class="sjw-categories__inner">
        <div class="sjw-section-header">
            <h3 class="sjw-section-title"><?php esc_html_e( 'Shop by Category', 'groser-children' ); ?></h3>
            <div class="sjw-section-rule"></div>
        </div>
        <div class="sjw-categories__grid">
            <?php foreach ( $cats as $cat ) : ?>
                <a href="<?php echo esc_url( $cat['url'] ); ?>" class="sjw-cat-item">
                    <div class="sjw-cat-circle">
                        <img
                            src="<?php echo esc_url( $cat['img'] ); ?>"
                            alt="<?php echo esc_attr( $cat['name'] ); ?>"
                            loading="lazy"
                            width="160"
                            height="160"
                        >
                    </div>
                    <span class="sjw-cat-label"><?php echo esc_html( $cat['name'] ); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
