<?php
/**
 * The Template for displaying product archives, including the main shop page
 *
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

if ( function_exists( 'groser_page_breadcrumb' ) ) {
	groser_page_breadcrumb();
}

do_action( 'woocommerce_before_main_content' );

// Check if we're on the main shop page (not a category page)
$is_main_shop = is_shop() && ! is_product_category() && ! is_product_tag() && ! is_search();
?>

<div class="shop-section pb-80">
	<div class="container">
		<div class="row">
			<div class="col-xs-12">

				<?php if ( $is_main_shop ) : ?>
					<!-- Main Shop Page: Display Categories -->
					<div class="shop-categories-section">
						<div class="shop-categories-header">
							<h2 class="categories-title">Shop By Category</h2>
							<div class="category-nav-arrows">
								<button class="cat-nav-btn cat-prev" aria-label="Previous">&lt;</button>
								<button class="cat-nav-btn cat-next" aria-label="Next">&gt;</button>
							</div>
						</div>

						<div class="product-categories-row">
							<?php
							$product_categories = get_terms( array(
								'taxonomy'   => 'product_cat',
								'hide_empty' => true,
								'parent'     => 0,
								'orderby'    => 'name',
								'order'      => 'ASC',
							) );

							if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) :
								foreach ( $product_categories as $category ) :
									$thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
									$image_url = $thumbnail_id ? wp_get_attachment_url( $thumbnail_id ) : wc_placeholder_img_src( 'woocommerce_thumbnail' );
									$category_link = get_term_link( $category );
							?>
								<div class="category-item">
									<a href="<?php echo esc_url( $category_link ); ?>">
										<div class="category-circle">
											<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $category->name ); ?>" />
										</div>
										<p class="category-name"><?php echo esc_html( $category->name ); ?></p>
									</a>
								</div>
							<?php
								endforeach;
							else :
							?>
								<p class="no-categories">No categories found.</p>
							<?php endif; ?>
						</div>
					</div>

				<?php else : ?>
					<!-- Category Page: Display Products -->
					<div class="shop-area clearfix">
						<?php if ( is_active_sidebar( 'shop-sidebar-1' ) ) : ?>
							<div class="shop-sidebar">
								<?php dynamic_sidebar( 'shop-sidebar-1' ); ?>
							</div>
						<?php endif; ?>

						<div class="woocommerce-content-wrap <?php echo is_active_sidebar( 'shop-sidebar-1' ) ? 'left-sidebar' : 'no-shop-active-sidebar'; ?>">

							<div class="woocommerce-toolbar-top">
								<p class="woocommerce-result-count"><?php woocommerce_result_count(); ?></p>
								<?php woocommerce_catalog_ordering(); ?>
							</div>

							<div class="woocommerce-content-inner">
								<?php do_action( 'woocommerce_archive_description' ); ?>

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
							</div>
						</div>
					</div>
				<?php endif; ?>

			</div>
		</div>
	</div>
</div>

<?php
do_action( 'woocommerce_after_main_content' );
get_footer( 'shop' );
