<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>
<div class="row">
	<div class="col-md-6">
		<div class="product-single-wrap mb-30">
			<?php
				/**
				 * woocommerce_before_single_product_summary hook.
				 *
				 * @hooked woocommerce_show_product_sale_flash - 10
				 * @hooked woocommerce_show_product_images - 20
				 */
				do_action( 'woocommerce_before_single_product_summary' );
			?>
		</div>
	</div>
	<div class="col-md-6 product-details-col">
		<div class="product-details">
			<?php woocommerce_template_single_title();?>
			<div class="rating">
				<?php woocommerce_template_single_rating(); ?>
			</div>
			<?php woocommerce_template_single_price();?>
			<?php woocommerce_template_single_excerpt();?>
			<div class="thb-product-meta-before mt-20">
				<div class="product_meta">
					<?php woocommerce_template_single_meta();?>
				</div>
			</div>
			<div class="product-option">
				<?php woocommerce_template_single_add_to_cart();?>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-xs-12">
		<div class="single-product-info">
			<?php woocommerce_output_product_data_tabs();?>
		</div>
	</div>
</div>
<div class="row">
    <div class="col col-xs-12">
<?php
	/**
	 * woocommerce_after_single_product_summary hook.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 */
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	do_action( 'woocommerce_after_single_product_summary' );
?>
	
</div>
</div>
</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>
