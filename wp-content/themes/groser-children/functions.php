<?php
/**
 * Groser Children — child theme functions
 *
 * Enqueues Google Fonts, parent theme stylesheet, and child stylesheet.
 * Inherits all WooCommerce support from the parent.
 * Adds Sabjiwala-specific WooCommerce overrides ported from groser-child.
 */

// -----------------------------------------------------------------------
// Enqueue styles & fonts
// -----------------------------------------------------------------------
add_action( 'wp_enqueue_scripts', 'groser_children_enqueue_styles' );
function groser_children_enqueue_styles() {
    $parent_version = wp_get_theme( 'groser' )->get( 'Version' );
    $child_version  = wp_get_theme()->get( 'Version' );

    // Google Fonts: Playfair Display + Inter
    wp_enqueue_style(
        'groser-children-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap',
        [],
        null
    );

    // Parent theme stylesheet
    wp_enqueue_style(
        'groser-style',
        get_template_directory_uri() . '/style.css',
        [],
        $parent_version
    );

    // Child stylesheet (style.css in this theme)
    wp_enqueue_style(
        'groser-children-style',
        get_stylesheet_uri(),
        [ 'groser-style' ],
        $child_version
    );
}

// -----------------------------------------------------------------------
// Chat widget JS — vanilla JS toggling the panel
// -----------------------------------------------------------------------
add_action( 'wp_enqueue_scripts', 'groser_children_enqueue_scripts' );
function groser_children_enqueue_scripts() {
    wp_enqueue_script(
        'groser-children-chat',
        get_stylesheet_directory_uri() . '/assets/js/chat-widget.js',
        [],
        wp_get_theme()->get( 'Version' ),
        true
    );
}

// -----------------------------------------------------------------------
// WooCommerce: disable SKU display globally
// -----------------------------------------------------------------------
add_filter( 'wc_product_sku_enabled', '__return_false' );

// -----------------------------------------------------------------------
// WooCommerce: remove category "posted in" from single product meta
// -----------------------------------------------------------------------
add_filter( 'woocommerce_product_meta_end', 'sjw_remove_categories_from_meta', 5 );
function sjw_remove_categories_from_meta( $html ) {
    return preg_replace( '/<span class="posted_in">.*?<\/span>/', '', $html );
}

// -----------------------------------------------------------------------
// WooCommerce: remove the product categories sidebar widget
// -----------------------------------------------------------------------
add_action( 'widgets_init', 'sjw_remove_category_widget', 11 );
function sjw_remove_category_widget() {
    unregister_widget( 'WC_Widget_Product_Categories' );
}

// -----------------------------------------------------------------------
// WooCommerce: wrap product loops on the front-page in our grid class
// -----------------------------------------------------------------------
add_filter( 'woocommerce_product_loop_start', 'sjw_wrap_product_loop_start' );
function sjw_wrap_product_loop_start( $html ) {
    return '<div class="sjw-wc-grid">' . $html;
}

add_filter( 'woocommerce_product_loop_end', 'sjw_wrap_product_loop_end' );
function sjw_wrap_product_loop_end( $html ) {
    return $html . '</div>';
}

// -----------------------------------------------------------------------
// Custom image sizes used by child theme sections
// -----------------------------------------------------------------------
add_action( 'after_setup_theme', 'groser_children_image_sizes' );
function groser_children_image_sizes() {
    add_image_size( 'sjw-product-thumb', 300, 300, true );
    add_image_size( 'sjw-category-circle', 160, 160, true );
    add_image_size( 'sjw-hero', 720, 600, false );
}

// -----------------------------------------------------------------------
// Category banner — custom meta field in WP Admin
// Products → Categories → Edit Category → Upload Banner
// -----------------------------------------------------------------------

add_action( 'product_cat_add_form_fields', 'sjw_cat_banner_add_field' );
function sjw_cat_banner_add_field() {
    ?>
    <div class="form-field">
        <label><?php esc_html_e( 'Banner Image', 'groser-children' ); ?></label>
        <input type="hidden" name="category_banner" id="category_banner" value="" />
        <div id="sjw_banner_preview" style="margin-bottom:8px;"></div>
        <button type="button" class="button sjw-upload-banner-btn">
            <?php esc_html_e( 'Upload Banner', 'groser-children' ); ?>
        </button>
        <button type="button" class="button sjw-remove-banner-btn" style="display:none;margin-left:4px;">
            <?php esc_html_e( 'Remove', 'groser-children' ); ?>
        </button>
        <p class="description"><?php esc_html_e( 'Recommended: 900×300 px. Shown at the top of the category page.', 'groser-children' ); ?></p>
    </div>
    <?php
}

add_action( 'product_cat_edit_form_fields', 'sjw_cat_banner_edit_field' );
function sjw_cat_banner_edit_field( $term ) {
    $banner = get_term_meta( $term->term_id, 'category_banner', true );
    ?>
    <tr class="form-field">
        <th scope="row"><label><?php esc_html_e( 'Banner Image', 'groser-children' ); ?></label></th>
        <td>
            <input type="hidden" name="category_banner" id="category_banner" value="<?php echo esc_url( $banner ); ?>" />
            <div id="sjw_banner_preview" style="margin-bottom:8px;">
                <?php if ( $banner ) : ?>
                    <img src="<?php echo esc_url( $banner ); ?>" style="max-width:400px;height:auto;border-radius:6px;display:block;" />
                <?php endif; ?>
            </div>
            <button type="button" class="button sjw-upload-banner-btn">
                <?php esc_html_e( $banner ? 'Change Banner' : 'Upload Banner', 'groser-children' ); ?>
            </button>
            <button type="button" class="button sjw-remove-banner-btn" style="margin-left:4px;<?php echo $banner ? '' : 'display:none;'; ?>">
                <?php esc_html_e( 'Remove', 'groser-children' ); ?>
            </button>
            <p class="description"><?php esc_html_e( 'Recommended: 900×300 px. Shown at the top of the category page.', 'groser-children' ); ?></p>
        </td>
    </tr>
    <?php
}

add_action( 'created_product_cat', 'sjw_save_cat_banner' );
add_action( 'edited_product_cat', 'sjw_save_cat_banner' );
function sjw_save_cat_banner( $term_id ) {
    if ( isset( $_POST['category_banner'] ) ) {
        $url = esc_url_raw( wp_unslash( $_POST['category_banner'] ) );
        if ( $url ) {
            update_term_meta( $term_id, 'category_banner', $url );
        } else {
            delete_term_meta( $term_id, 'category_banner' );
        }
    }
}

add_action( 'admin_enqueue_scripts', 'sjw_cat_banner_admin_scripts' );
function sjw_cat_banner_admin_scripts( $hook ) {
    if ( ! in_array( $hook, [ 'edit-tags.php', 'term.php' ], true ) ) {
        return;
    }
    if ( empty( $_GET['taxonomy'] ) || $_GET['taxonomy'] !== 'product_cat' ) {
        return;
    }
    wp_enqueue_media();
    wp_add_inline_script( 'jquery-core', "
        jQuery(function(\$) {
            var frame;
            \$(document).on('click', '.sjw-upload-banner-btn', function(e) {
                e.preventDefault();
                frame = wp.media({
                    title:    'Select Banner Image',
                    button:   { text: 'Use this image' },
                    multiple: false
                });
                frame.on('select', function() {
                    var att = frame.state().get('selection').first().toJSON();
                    \$('#category_banner').val(att.url);
                    \$('#sjw_banner_preview').html('<img src=\"' + att.url + '\" style=\"max-width:400px;height:auto;border-radius:6px;display:block;\" />');
                    \$('.sjw-remove-banner-btn').show();
                });
                frame.open();
            });
            \$(document).on('click', '.sjw-remove-banner-btn', function(e) {
                e.preventDefault();
                \$('#category_banner').val('');
                \$('#sjw_banner_preview').html('');
                \$(this).hide();
            });
        });
    " );
}

