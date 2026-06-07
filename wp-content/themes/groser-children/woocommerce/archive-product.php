<?php
/**
 * WooCommerce product archive — groser-children override.
 *
 * Three-way routing:
 *  1. Main /shop page              → top-level category circles
 *  2. Parent category WITH children → child category circles
 *  3. Leaf category / tag / search  → product grid (image + title + price)
 *
 * @package groser-children
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

if ( function_exists( 'groser_page_breadcrumb' ) ) {
    groser_page_breadcrumb();
}

do_action( 'woocommerce_before_main_content' );

// ── Routing flags ───────────────────────────────────────────────────────────
$is_main_shop = is_shop() && ! is_product_category() && ! is_product_tag() && ! is_search();

$child_categories = [];
if ( is_product_category() ) {
    $queried      = get_queried_object();
    $child_categories = get_terms( [
        'taxonomy'   => 'product_cat',
        'parent'     => $queried->term_id,
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ] );
}
$is_parent_category = is_product_category() && ! empty( $child_categories ) && ! is_wp_error( $child_categories );
?>

<div class="sjw-archive-wrap">

<?php
// ── 1. Main shop page ───────────────────────────────────────────────────────
if ( $is_main_shop ) :
    $top_level = get_terms( [
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'parent'     => 0,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ] );
?>
    <section class="sjw-categories">
        <div class="sjw-categories__inner">
            <div class="sjw-section-header">
                <h2 class="sjw-section-title"><?php esc_html_e( 'Shop By Category', 'groser-children' ); ?></h2>
                <span class="sjw-section-rule"></span>
            </div>
            <div class="sjw-categories__grid">
                <?php if ( ! empty( $top_level ) && ! is_wp_error( $top_level ) ) :
                    foreach ( $top_level as $cat ) :
                        $thumb_id  = get_term_meta( $cat->term_id, 'thumbnail_id', true );
                        $image_url = $thumb_id ? wp_get_attachment_url( $thumb_id ) : wc_placeholder_img_src( 'woocommerce_thumbnail' );
                ?>
                    <div class="sjw-cat-item">
                        <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
                            <div class="sjw-cat-circle">
                                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $cat->name ); ?>" loading="lazy" />
                            </div>
                            <p class="sjw-cat-label"><?php echo esc_html( $cat->name ); ?></p>
                        </a>
                    </div>
                <?php endforeach; else : ?>
                    <p class="sjw-no-items"><?php esc_html_e( 'No categories found.', 'groser-children' ); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php
// ── 2. Parent category that has child sub-categories ────────────────────────
elseif ( $is_parent_category ) :
?>
    <section class="sjw-categories sjw-subcategories">
        <div class="sjw-categories__inner">
            <div class="sjw-section-header">
                <h2 class="sjw-section-title"><?php echo esc_html( $queried->name ); ?></h2>
                <span class="sjw-section-rule"></span>
            </div>
            <div class="sjw-categories__grid">
                <?php foreach ( $child_categories as $cat ) :
                    $thumb_id  = get_term_meta( $cat->term_id, 'thumbnail_id', true );
                    $image_url = $thumb_id ? wp_get_attachment_url( $thumb_id ) : wc_placeholder_img_src( 'woocommerce_thumbnail' );
                ?>
                    <div class="sjw-cat-item">
                        <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
                            <div class="sjw-cat-circle">
                                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $cat->name ); ?>" loading="lazy" />
                            </div>
                            <p class="sjw-cat-label"><?php echo esc_html( $cat->name ); ?></p>
                            <?php if ( $cat->count ) : ?>
                                <span class="sjw-cat-count"><?php echo esc_html( $cat->count ); ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

<?php
// ── 3. Leaf category / tag / search — show products ─────────────────────────
else :

    // Category banner — ACF field first, fallback to WooCommerce thumbnail
    if ( is_product_category() && isset( $queried ) ) :
        $banner_url = '';
        $banner_alt = esc_attr( $queried->name );

        // Priority 1: ACF category_banner field
        if ( function_exists( 'get_field' ) ) {
            $acf = get_field( 'category_banner', $queried );
            if ( ! empty( $acf['url'] ) ) {
                $banner_url = $acf['url'];
                $banner_alt = ! empty( $acf['alt'] ) ? $acf['alt'] : $banner_alt;
            }
        }

        // Priority 2: WooCommerce category thumbnail
        if ( ! $banner_url ) {
            $thumb_id = get_term_meta( $queried->term_id, 'thumbnail_id', true );
            if ( $thumb_id ) {
                $thumb_src  = wp_get_attachment_image_src( $thumb_id, 'large' );
                $banner_url = $thumb_src ? $thumb_src[0] : '';
            }
        }
?>
    <div class="sjw-cat-banner<?php echo $banner_url ? '' : ' sjw-cat-banner--no-img'; ?>">
        <div class="sjw-cat-banner__text">
            <h1 class="sjw-cat-banner__title"><?php echo esc_html( strtoupper( $queried->name ) ); ?></h1>
            <?php if ( $queried->description ) : ?>
                <p class="sjw-cat-banner__desc"><?php echo esc_html( wp_strip_all_tags( $queried->description ) ); ?></p>
            <?php endif; ?>
        </div>
        <?php if ( $banner_url ) : ?>
        <div class="sjw-cat-banner__img-wrap">
            <img
                src="<?php echo esc_url( $banner_url ); ?>"
                alt="<?php echo esc_attr( $banner_alt ); ?>"
                class="sjw-cat-banner__img"
                loading="eager"
            />
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

    <div class="sjw-shop-area">

        <?php if ( is_active_sidebar( 'shop-sidebar-1' ) ) : ?>
            <aside class="sjw-shop-sidebar">
                <?php dynamic_sidebar( 'shop-sidebar-1' ); ?>
            </aside>
        <?php endif; ?>

        <div class="sjw-wc-grid <?php echo is_active_sidebar( 'shop-sidebar-1' ) ? 'has-sidebar' : ''; ?>">

            <div class="sjw-shop-toolbar">
                <p class="woocommerce-result-count"><?php woocommerce_result_count(); ?></p>
                <?php woocommerce_catalog_ordering(); ?>
            </div>

            <?php if ( woocommerce_product_loop() ) : ?>

                <?php woocommerce_product_loop_start(); ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php wc_get_template_part( 'content', 'product' ); ?>
                <?php endwhile; ?>
                <?php woocommerce_product_loop_end(); ?>

                <?php do_action( 'woocommerce_after_shop_loop' ); ?>

            <?php else : ?>
                <?php do_action( 'woocommerce_no_products_found' ); ?>
            <?php endif; ?>

        </div><!-- .sjw-wc-grid -->
    </div><!-- .sjw-shop-area -->

<?php endif; ?>

</div><!-- .sjw-archive-wrap -->

<?php
do_action( 'woocommerce_after_main_content' );
get_footer( 'shop' );
