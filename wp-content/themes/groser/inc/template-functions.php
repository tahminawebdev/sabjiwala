<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package groser
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function groser_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'groser_body_classes' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function groser_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'groser_pingback_header' );

/**
 * Product Per Page Count
 *
 * @param [type] $per_page
 * @return void
 */

add_filter( 'loop_shop_per_page', 'groser_loop_shop_per_page', 30 );

function groser_loop_shop_per_page( $products ) {
	$product_count = cs_get_option('product_count');
	$products = $product_count;
	return $products;
}

function groser_add_to_cart_icon( $icon = true, $text = true ){
	global $product;
	$quantity = 1;
	$class = implode( ' ', array_filter( array(
		'action-cart button product_type_variable rtwpvs_add_to_cart rtwpvs_ajax_add_to_cart ',
		'product_type_' . $product->get_type(),
		$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
		$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
	) ) );

	$html = '';

	if ( $icon ) {
		$html .= '<i class="fas fa-shopping-basket"></i>';
	}

	if ( $text ) {
		$html .= '<span>' . $product->add_to_cart_text() . '</span>';
	}
	$html .= '<span class="custom-tooltip">'.esc_html('Add To Cart', 'groser').'</span>';

	echo sprintf( '<a rel="nofollow" title="%s" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s">' . $html . '</a>',
		esc_attr( $product->add_to_cart_text() ),
		esc_url( $product->add_to_cart_url() ),
		esc_attr( isset( $quantity ) ? $quantity : 1 ),
		esc_attr( $product->get_id() ),
		esc_attr( $product->get_sku() ),
		esc_attr( isset( $class ) ? $class : 'action-cart' )
	);
}

/**
 * Wishlist Hide Default Button
 */
add_filter( 'woosw_button_position_archive', '__return_false' );
add_filter( 'woosw_button_position_single', '__return_false' );
/**
 * Quick View Default Button
 */
add_filter( 'woosq_button_position', '__return_false' );

/**
 * compayer
 */
add_filter( 'woosc_button_position_archive', '__return_false' );
add_filter( 'woosc_button_position_single', '__return_false' );


add_filter( 'woocommerce_add_to_cart_fragments', 'groser_mini_cart_count');
function groser_mini_cart_count($fragments){
    ob_start();
    $items_count = WC()->cart->get_cart_contents_count();
    ?>
    <span id="mini-cart-count" class="count"><?php echo esc_html($items_count) ? $items_count : '0'; ?></span>
    <?php
        $fragments['#mini-cart-count'] = ob_get_clean();
    return $fragments;
}

/**
 * Get Breadcrumb
 *
 * @return void
 */
function groser_the_breadcrumb() {
	global $wp_query;
	$queried_object = get_queried_object();
	$breadcrumb     = '';
	$delimiter      = ' / ';
	$before         = '<li class="groserbcrumb-item groserbcrumb-begin">';
	$after          = '</li>';
	$breadcrumb .='<ul class="list-unstyled d-flex align-items-center">';
	if ( ! is_front_page() ) {
		$breadcrumb .= $before . '<a href="' . home_url( '/' ) . '">' . esc_html__( 'Home', 'groser' ) . ' &nbsp;</a>' . $after;
		/** If category or single post */
		if ( is_category() ) {
			$cat_obj       = $wp_query->get_queried_object();
			$this_category = get_category( $cat_obj->term_id );
			if ( $this_category->parent != 0 ) {
				$parent_category = get_category( $this_category->parent );
				$breadcrumb      .= get_category_parents( $parent_category, true, $delimiter );
			}
			$breadcrumb .= $before . '<a href="' . get_category_link( get_query_var( 'cat' ) ) . '">' . single_cat_title( '', false ) . '</a>' . $after;
		} elseif ( $wp_query->is_posts_page ) {
			$breadcrumb .= $before . $queried_object->post_title . $after;
		} elseif ( is_tax() ) {
			$breadcrumb .= $before . '<a href="' . get_term_link( $queried_object ) . '">' . $queried_object->name . '</a>' . $after;
		} elseif ( is_page() ) /** If WP pages */ {
			global $post;
			if ( $post->post_parent ) {
				$anc = get_post_ancestors( $post->ID );
				foreach ( $anc as $ancestor ) {
					$breadcrumb .= $before . '<a href="' . get_permalink( $ancestor ) . '">' . get_the_title( $ancestor ) . ' &nbsp;</a>' . $after;
				}
				$breadcrumb .= $before . '' . get_the_title( $post->ID ) . '' . $after;
			} else {
				$breadcrumb .= $before . '' . get_the_title() . '' . $after;
			}
		} elseif ( is_singular() ) {
			if ( $category = wp_get_object_terms( get_the_ID(), array( 'category', 'location', 'tax_feature' ) ) ) {
				if ( ! is_wp_error( $category ) ) {
					$breadcrumb .= $before . '<a href="' . get_term_link( groser_set( $category, '0' ) ) . '">' . groser_set( groser_set( $category, '0' ), 'name' ) . '&nbsp;</a>' . $after;
					$breadcrumb .= $before . '' . get_the_title() . '' . $after;
				} else {
					$breadcrumb .= $before . '' . get_the_title() . '' . $after;
				}
			} else {
				$breadcrumb .= $before . '' . get_the_title() . '' . $after;
			}
		} elseif ( is_tag() ) {
			$breadcrumb .= $before . '<a href="' . get_term_link( $queried_object ) . '">' . single_tag_title( '', false ) . '</a>' . $after;
		} /**If tag template*/
		elseif ( is_day() ) {
			$breadcrumb .= $before . '<a href="#">' . esc_html__( 'Archive for ', 'groser' ) . get_the_time( 'F jS, Y' ) . '</a>' . $after;
		} /** If daily Archives */
		elseif ( is_month() ) {
			$breadcrumb .= $before . '<a href="' . get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) . '">' . __( 'Archive for ', 'groser' ) . get_the_time( 'F, Y' ) . '</a>' . $after;
		} /** If montly Archives */
		elseif ( is_year() ) {
			$breadcrumb .= $before . '<a href="' . get_year_link( get_the_time( 'Y' ) ) . '">' . __( 'Archive for ', 'groser' ) . get_the_time( 'Y' ) . '</a>' . $after;
		} /** If year Archives */
		elseif ( is_author() ) {
			$breadcrumb .= $before . '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( "ID" ) ) ) . '">' . __( 'Archive for ', 'groser' ) . get_the_author() . '</a>' . $after;
		} /** If author Archives */
		elseif ( is_search() ) {
			$breadcrumb .= $before . '' . esc_html__( 'Search Results for ', 'groser' ) . get_search_query() . '' . $after;
		} /** if search template */
		elseif ( is_404() ) {
			$breadcrumb .= $before . '' . esc_html__( '404 - Not Found', 'groser' ) . '' . $after;
			/** if search template */
		} elseif ( is_post_type_archive( 'product' ) ) {
			$shop_page_id = wc_get_page_id( 'shop' );
			if ( get_option( 'page_on_front' ) !== $shop_page_id ) {
				$shop_page = get_post( $shop_page_id );
				$_name     = wc_get_page_id( 'shop' ) ? get_the_title( wc_get_page_id( 'shop' ) ) : '';
				if ( ! $_name ) {
					$product_post_type = get_post_type_object( 'product' );
					$_name             = $product_post_type->labels->singular_name;
				}
				if ( is_search() ) {
					$breadcrumb .= $before . '<a href="' . get_post_type_archive_link( 'product' ) . '">' . $_name . '</a>' . $delimiter . esc_html__( 'Search results for &ldquo;', 'groser' ) . get_search_query() . '&rdquo;' . $after;
				} elseif ( is_paged() ) {
					$breadcrumb .= $before . '<a href="' . get_post_type_archive_link( 'product' ) . '">' . $_name . '</a>' . $after;
				} else {
					$breadcrumb .= $before . $_name . $after;
				}
			}
		} else {
			$breadcrumb .= $before . '<a href="' . get_permalink() . '">' . get_the_title() . '</a>' . $after;
		}
		/** Default value */
	}
	$breadcrumb .='</ul>';

	return $breadcrumb;
}

/**
 * Gallery Column
 */
add_filter ( 'woocommerce_product_thumbnails_columns', 'groser_change_gallery_columns' );

function groser_change_gallery_columns() {
     return 1;
}

/**
 * @snippet       CSS to Move Gallery Columns @ Single Product Page
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 3.5.4
 */

add_filter ( 'storefront_product_thumbnail_columns', 'groser_change_gallery_columns_storefront' );

function groser_change_gallery_columns_storefront() {
     return 1;
}

function get_product_search_form(){ ?>
	<form role="search" method="get" class="woocommerce-product-search widget__search" action="<?php echo esc_url( home_url( '/'  ) ); ?>">
		<label class="screen-reader-text" for="s"><?php _e( 'Search for:', 'groser' ); ?></label>
		<input type="search" class="search-field" placeholder="<?php esc_attr_e('Search...', 'groser' ); ?>" value="<?php echo get_search_query(); ?>" name="s" title="<?php esc_attr_e('Search...', 'groser' ); ?>" />
		<button type="submit"><i class="fas fa-search"></i></button>
		<input type="hidden" name="post_type" value="product" />
	</form>
<?php
}

/**
 * Ajax wishlist Count
 */

function yith_wcwl_get_items_count() {
	ob_start();
	if(function_exists('YITH_WCWL')):
	?>
	  <a href="<?php echo esc_url( YITH_WCWL()->get_wishlist_url() ); ?>">
	  <img src="<?php echo esc_url(get_template_directory_uri());?>/assets/img/icon/heart.svg" alt="">
		<span class="yith-wcwl-items-count count">
			<?php echo esc_html( yith_wcwl_count_all_products() ); ?>
		</span>
	  </a>
	<?php
	return ob_get_clean();
	endif;
  }

  if ( defined( 'YITH_WCWL' ) && ! function_exists( 'yith_wcwl_ajax_update_count' ) ) {
	function yith_wcwl_ajax_update_count() {
	  wp_send_json( array(
		'count' => yith_wcwl_count_all_products()
	  ) );
	}

	add_action( 'wp_ajax_yith_wcwl_update_wishlist_count', 'yith_wcwl_ajax_update_count' );
	add_action( 'wp_ajax_nopriv_yith_wcwl_update_wishlist_count', 'yith_wcwl_ajax_update_count' );
  }

if ( defined( 'YITH_WCWL' ) && ! function_exists( 'yith_wcwl_enqueue_custom_script' ) ) {
function yith_wcwl_enqueue_custom_script() {
	wp_add_inline_script(
	'jquery-yith-wcwl',
	"
		jQuery( function( $ ) {
		$( document ).on( 'added_to_wishlist removed_from_wishlist', function() {
			$.get( yith_wcwl_l10n.ajax_url, {
			action: 'yith_wcwl_update_wishlist_count'
			}, function( data ) {
			$('.yith-wcwl-items-count').children('i').html( data.count );
			} );
		} );
		} );
	"
	);
}

add_action( 'wp_enqueue_scripts', 'yith_wcwl_enqueue_custom_script', 20 );
}

/**
 * Authore Avater
 */
function groser_main_author_avatars($size) {
    echo get_avatar(get_the_author_meta('email'), $size);
}

add_action('genesis_entry_header', 'groser_post_author_avatars');

/**
 * Post Read Time
 */
function groser_reading_time() {
	global $post;
	$content = get_post_field( 'post_content', $post->ID );
	$word_count = str_word_count( strip_tags( $content ) );
	$readingtime = ceil($word_count / 200);
	if ($readingtime == 1) {
	$timer = esc_html(" min read");
	} else {
	$timer = esc_html(" min read");
	}
	$totalreadingtime = $readingtime . $timer;
	return $totalreadingtime;
}

/**
 * Search Widget
 */
function groser_search_widgets( $form ) {
    $form = '<form role="search" method="get" id="searchform" class="widget__search" action="' . home_url( '/' ) . '" >
    <input class="form_control" placeholder="' .esc_attr__( 'Search..', 'groser' ) . '" type="text"  value="' . get_search_query() . '" name="s" id="s" />
    <button type="submit"><i class="fas fa-search"></i></button>
    </form>';

    return $form;
}
add_filter( 'get_search_form', 'groser_search_widgets', 100 );


/**
 * Category Count Markup
 */
function groser_category_html( $links ) {
    $links = str_replace( '</a> (', '<span class="cat-number">(', $links );
    $links = str_replace( ')', ')</span><i class="fas fa-chevron-right"></i></a>', $links );
    return $links;
}
add_filter( 'wp_list_categories', 'groser_category_html' );

/**
 * Archive Count Markup
 */
function groser_archive_html($links) {
	$links = str_replace('</a>&nbsp;(', '<span class="cat-number">(', $links);
    $links = str_replace(')', ')</span><i class="fas fa-chevron-right"></i></a>', $links);
    return $links;
}

add_filter('get_archives_link', 'groser_archive_html');

/**
 * post Pagination
 */
function groser_pagination() {
    global $wp_query;
    $links = paginate_links( array(
    'current'   => max( 1, get_query_var( 'paged' ) ),
    'total'     => $wp_query->max_num_pages,
    'type'      => 'list',
    'mid_size'  => 3,
    'prev_text' => '<i class="fal fa-angle-double-left"></i>',
    'next_text' => '<i class="fal fa-angle-double-right"></i>',
    ) );

    echo wp_kses_post( $links );
}



/**
 * Comment Message Box
 */
function groser_comment_reform( $arg ) {

	$arg['title_reply']   = esc_html__( 'Leave a comment', 'groser' );
	$arg['comment_field'] = '<div class="comments-form"><div class="col-md-12"><div class="field-item"><textarea id="comment" class="form_control" name="comment" cols="77" rows="3" placeholder="' . esc_attr__( "Comment", "groser" ) . '" aria-required="true"></textarea></div></div></div>';

	return $arg;

}
add_filter( 'comment_form_defaults', 'groser_comment_reform' );
/**
 * Comment Form Field
 */

function groser_modify_comment_form_fields( $fields ) {
	$commenter = wp_get_current_commenter();
	$req       = get_option( 'require_name_email' );

	$fields['author'] = '<div class="comments-form"><div class="row"><div class="col-md-6"><div class="field-item"><input type="text" name="author" id="author" value="' . esc_attr( $commenter['comment_author'] ) . '" placeholder="' . esc_attr__( "Name", "groser" ) . '" size="22" tabindex="1"' . ( $req ? 'aria-required="true"' : '' ) . ' class="form_control" /></div></div>';

	$fields['email'] = '<div class="col-md-6"><div class="field-item"><input type="email" name="email" id="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" placeholder="' . esc_attr__( "Email", "groser" ) . '" size="22" tabindex="2"' . ( $req ? 'aria-required="true"' : '' ) . ' class="form_control"  /></div></div>';

	$fields['url'] = '<div class="col-md-12"><div class="field-item"><input type="url" name="url" id="url" value="' . esc_attr( $commenter['comment_author_url'] ) . '" placeholder="' . esc_attr__( "Website", "groser" ) . '" size="22" tabindex="2"' . ( $req ? 'aria-required="false"' : '' ) . ' class="form_control"  /></div></div></div></div>';

	return $fields;
}
add_filter( 'comment_form_default_fields', 'groser_modify_comment_form_fields' );

// comment Move Field
function groser_move_comment_field_to_bottom( $fields ) {
	$comment_field = $fields['comment'];
	unset( $fields['comment'] );
	$fields['comment'] = $comment_field;
	return $fields;
}
add_filter( 'comment_form_fields', 'groser_move_comment_field_to_bottom' );


/**
 * Comment List Modification
 */
function groser_comments( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;?>

	<li <?php comment_class('comment');?> id="comment-<?php comment_ID()?>">
        <div class="comments-box">
            <?php if ( get_avatar( $comment ) ) {?>
                <div class="comments-avatar">
                    <?php echo get_avatar( $comment, 100 ); ?>
                </div>
            <?php }?>

            <div class="comments-text">
				<div class="avatar-name">
					<h5 class="name"><?php comment_author_link()?></h5>
					<span class="comment-date"><?php echo esc_html( get_comment_date( 'dS M Y' ) ); ?></span>
					<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'], 'reply_text' => wp_kses('<i class="fal fa-reply"></i> Reply', true) ) ) );?>
				</div>
                <?php if ( $comment->comment_approved == '0' ): ?>
                    <p><em><?php esc_html_e( 'Your comment is awaiting moderation.', 'groser' );?></em></p>
                <?php endif;?>
                <?php comment_text();?>
            </div>
        </div>
	</li>


<?php
}


/**
 * groser Category Search
 *
 * @return void
 */
function groser_product_search(){ ?>
	<form name="myform" method="GET" class="header__search-box" action="<?php echo esc_url(home_url('/'));?>">
		<?php if (class_exists('WooCommerce')): ?>
			<div class="select-box">
				<?php
				if (isset($_REQUEST['product_cat']) && !empty($_REQUEST['product_cat'])) {
					$optsetlect = $_REQUEST['product_cat'];
				} else {
					$optsetlect = 0;
				}
				$args = array(
					'show_option_all' => esc_html__('All Categories', 'groser'),
					'hierarchical' => 1,
					'class' => 'cat',
					'echo' => 1,
					'value_field' => 'slug',
					'selected' => $optsetlect,
				);
				$args['taxonomy'] = 'product_cat';
				$args['name'] = 'product_cat';
				$args['class'] = 'cate-dropdown hidden-xs';
				wp_dropdown_categories($args);

				?>
			</div>

			<input type="hidden" value="product" name="post_type">
			<?php endif;?>
			<input type="text"  name="s" class="searchbox" maxlength="128" value="<?php echo get_search_query();?>" placeholder="<?php esc_attr_e('Search For Product', 'groser');?>">

			<button type="submit"><i class="fas fa-search"></i></button>
  </form>
  <?php
}



/**
 * groser Single Post Nav
 */
function groser_single_post_pagination(){
    $groser_prev_post = get_previous_post();
    $groser_next_post = get_next_post();
?>
<div class="row post-nav">
	<div class="col-lg-6 col-md-6">
		<div class="post-nav__wrap left-post">
			<a class="post-nav__link" href="<?php echo esc_url(get_the_permalink($groser_prev_post));?>">
				<i class="fas fa-angle-left"></i>
			</a>
			<div class="post-nav__item tx-post ul_li">
				<?php if(has_post_thumbnail($groser_prev_post)):?>
				<div class="post-thumb">
					<a href="<?php echo esc_url(get_the_permalink($groser_prev_post));?>"><img src="<?php echo esc_url(get_the_post_thumbnail_url($groser_prev_post, 'thumbnail'));?>" alt="<?php the_title_attribute();?>"></a>
				</div>
				<?php endif;?>
				<div class="post-content">
					<h2 class="post-title border-effect-2"><a href="<?php echo esc_url(get_the_permalink($groser_prev_post));?>"><?php echo esc_html(wp_trim_words( get_the_title($groser_prev_post), 5, '' ));?></a></h2>
					<div class="post-meta post-meta--2 ul_li mt-6">

						<span class="date"><i class="fas fa-calendar-alt"></i><?php echo wp_kses( get_the_date('M j, Y', $groser_prev_post), true ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-6 col-md-6">
		<div class="post-nav__wrap right-post">
			<a class="post-nav__link" href="<?php echo esc_url(get_the_permalink($groser_next_post));?>">
				<i class="fas fa-angle-right"></i>
			</a>
			<div class="post-nav__item tx-post ul_li">
				<?php if(has_post_thumbnail($groser_next_post)):?>
				<div class="post-thumb">
					<a href="<?php echo esc_url(get_the_permalink($groser_next_post));?>"><img src="<?php echo esc_url(get_the_post_thumbnail_url($groser_next_post, 'thumbnail'));?>" alt="<?php the_title_attribute();?>"></a>
				</div>
				<?php endif;?>
				<div class="post-content">
					<h2 class="post-title border-effect-2"><a href="<?php echo esc_url(get_the_permalink($groser_next_post));?>"><?php echo esc_html(wp_trim_words( get_the_title($groser_next_post), 5, '' ));?></a></h2>
					<div class="post-meta post-meta--2 ul_li mt-6">
						<span class="date"><i class="fas fa-calendar-alt"></i><?php echo wp_kses( get_the_date('M j, Y', $groser_next_post), true ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
}



/**
 * Discont Percent
 */
add_filter( 'woocommerce_sale_flash', 'groser_percentage_to_sale_badge', 20, 3 );
function groser_percentage_to_sale_badge( $html, $post, $product ) {

  if( $product->is_type('variable')){
      $percentages = array();

      // Get all variation prices
      $prices = $product->get_variation_prices();

      // Loop through variation prices
      foreach( $prices['price'] as $key => $price ){
          // Only on sale variations
          if( $prices['regular_price'][$key] !== $price ){
              // Calculate and set in the array the percentage for each variation on sale
              $percentages[] = round( 100 - ( floatval($prices['sale_price'][$key]) / floatval($prices['regular_price'][$key]) * 100 ) );
          }
      }
      // We keep the highest value
      $percentage = max($percentages) . '%';

  } elseif( $product->is_type('grouped') ){
      $percentages = array();

      // Get all variation prices
      $children_ids = $product->get_children();

      // Loop through variation prices
      foreach( $children_ids as $child_id ){
          $child_product = wc_get_product($child_id);

          $regular_price = (float) $child_product->get_regular_price();
          $sale_price    = (float) $child_product->get_sale_price();

          if ( $sale_price != 0 || ! empty($sale_price) ) {
              // Calculate and set in the array the percentage for each child on sale
              $percentages[] = round(100 - ($sale_price / $regular_price * 100));
          }
      }
      // We keep the highest value
      $percentage = max($percentages) . '%';

  } else {
      $regular_price = (float) $product->get_regular_price();
      $sale_price    = (float) $product->get_sale_price();

      if ( $sale_price != 0 || ! empty($sale_price) ) {
          $percentage    = round(100 - ($sale_price / $regular_price * 100)) . '%';
      } else {
          return $html;
      }
  }
  return '' . $percentage . '';
}

add_action('get_groser_current_product_category', 'groser_get_current_product_category');
function groser_get_current_product_category(){
    global $product;
    $terms = get_the_terms( $product->get_id(), 'product_cat' );
    if(!empty($terms)):
    foreach($terms  as $term){
        $product_cat_name = $term->name;
        $product_cat_id =  get_term_link( $term->term_id);
            break;
        }
    ?>
    <a href="<?php echo esc_url($product_cat_id); ?>"> <?php echo esc_attr($product_cat_name); ?></a>
<?php
endif;
}

function grose_stopout_badge_display(){
	global $product;
	$out_of_stock = $product->get_stock_status();

	if(get_post_meta(get_the_ID(), 'groser_product_meta', true)) {
		$gproduct_meta = get_post_meta(get_the_ID(), 'groser_product_meta', true);
	} else {
		$gproduct_meta = array();
	}
	if( array_key_exists( 'product_st_bg_color', $gproduct_meta )) {
		$product_st_bg_color = $gproduct_meta['product_st_bg_color'];
	} else {
		$product_st_bg_color = '';
	}
	?>

	<?php if($out_of_stock == 'outofstock'): ?>
		<span class="out-stock__badge stout" style="background-color:<?php echo esc_attr($product_st_bg_color);?>"><?php esc_html_e('Out of stock', 'groser');?></span>
	<?php endif;?>

<?php
}
add_action( 'grose_stopout_badge', 'grose_stopout_badge_display' );


/**
 * Sold Count & Progressbar
 */
add_action('groser_product_soldu_progress_count', 'groser_product_sold_count_display');
function groser_product_sold_count_display() {
    global $product;
        $sold_items = get_post_meta($product->get_id(), 'sold_items', true);
        $sold_items_type = get_post_meta($product->get_id(), 'sold_items_type', true);
        $sale_quantity = get_post_meta($product->get_id(), 'sale_quantity', true);
        if(!empty($sold_items) && !empty($sale_quantity)): ?>
		<div class="total-sold_items">
			<?php echo esc_html('Sold'); ?> : <?php echo esc_attr($sold_items); ?> <?php echo esc_attr($sold_items_type); ?>

			<?php echo esc_html('/'); ?>
			<?php echo esc_attr($sale_quantity); ?> <?php echo esc_attr($sold_items_type); ?>
		</div>
		<div class="product__progress progress">
			<div class="progress-bar" role="progressbar" style="width: <?php echo esc_attr( $sold_items / $sale_quantity * 100 ); ?>%" aria-valuenow="<?php echo esc_attr( $sold_items / $sale_quantity * 100 ); ?>" aria-valuemin="0" aria-valuemax="100"></div>
		</div>
<?php endif;
}



/**
 * Sale Quantity
 */
add_action( 'woocommerce_product_options_general_product_data', 'groser_woocommerce_product_general_data', 10);
function groser_woocommerce_product_general_data(){
    woocommerce_wp_text_input([
        'id' => 'sale_quantity',
        'label' => __('Sale quantity', 'groser'),
        'type'              => 'number',
        'custom_attributes' => array(
            'step' 	=> 'any',
            'min'	=> '0'
        ),
    ]);
    woocommerce_wp_text_input([
        'id' => 'sold_items',
        'label' => __('Sold Total Items', 'groser'),
        'type'              => 'number',
        'custom_attributes' => array(
            'step' 	=> 'any',
            'min'	=> '0'
        ),
    ]);
    woocommerce_wp_text_input([
        'id' => 'sold_items_type',
        'label' => __('Sold Total Type (Kg , g , li , Ml)', 'groser'),
        'type'              => 'text',

    ]);
}

/**
 * Custom Meta Processes
 */
function groser_wooc_custom_data_save($post_id){
    $product = wc_get_product($post_id);
	  $woosale_quantity = isset($_POST['sale_quantity']) ? $_POST['sale_quantity'] : '';
	  $product->update_meta_data('sale_quantity', sanitize_text_field($woosale_quantity));
      $woosold_items = isset($_POST['sold_items']) ? $_POST['sold_items'] : '';
	  $product->update_meta_data('sold_items', sanitize_text_field($woosold_items));
      $sold_items_type = isset($_POST['sold_items_type']) ? $_POST['sold_items_type'] : '';
	  $product->update_meta_data('sold_items_type', sanitize_text_field($sold_items_type));
	  $product->save();
}
add_action( 'woocommerce_process_product_meta','groser_wooc_custom_data_save', 10 );
