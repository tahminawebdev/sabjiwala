<?php
include_once( get_template_directory() . '/lib/ocdi/codestar.php');
function groser_ocdi_import_files() {
	return array(
		array(
			'import_file_name'             => 'Groser Main',
			'categories'                   => array( 'groser' ),
			'local_import_file'            => trailingslashit( get_template_directory() ) . 'lib/ocdi/demo/content.xml',
			'local_import_widget_file'     => trailingslashit( get_template_directory() ) . 'lib/ocdi/demo/widgets.wie',
			'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'lib/ocdi/demo/customizer.dat',
			'local_import_json'           => array(
				array(
					'file_path'   => trailingslashit( get_template_directory() ) . 'lib/ocdi/demo/codestar.json',
					'option_name' => 'groser',
				),
			),
			'import_preview_image_url'     => '',
			'import_notice'                => esc_html__( 'Import process may take 3-10 minutes. If you facing any issues please contact our support.After Import Succesfuly go to Appearance->Menu And Set your Menu', 'groser' ),
			'preview_url'                  => '',
		),
	);
}
add_filter( 'pt-ocdi/import_files', 'groser_ocdi_import_files' );

function groser_ocdi_after_import( $selected_import ) {
	// Assign groser menus to their locations where will be display.
    $primary  = get_term_by( 'name', 'Main Menu', 'nav_menu' );

    set_theme_mod(
        'nav_menu_locations',
        array(
            'main_menu' => $primary->term_id,
        )
    );

	//Revulation Slider Import
	if( class_exists('RevSliderSliderImport') ) {
		foreach(array('home2', 'homemain') as $slider) {
			$file = get_template_directory() . '/lib/ocdi/slider/'.$slider.'.zip';
			if( file_exists($file) ) {
				$importer = new RevSliderSliderImport();
				$response = $importer->import_slider( true, $file );
			}
		}
    }

    // groser Assign front page and posts page Set
    $front_page_id	= get_page_by_title( 'Home' );

    $blog_page_id	= get_page_by_title( 'Blog' );


    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $front_page_id->ID );
    update_option( 'page_for_posts', $blog_page_id->ID );
}
add_action( 'pt-ocdi/after_import', 'groser_ocdi_after_import' );

function groser_ocdi_before_content_import() {
    add_filter( 'wp_import_post_data_processed', 'groser_ocdi_wp_import_post_data_processed', 99, 2 );
}
add_action( 'pt-ocdi/before_content_import', 'groser_ocdi_before_content_import', 99 );

function groser_ocdi_wp_import_post_data_processed( $postdata, $data ) {
    return wp_slash( $postdata );
}

add_filter( 'pt-ocdi/disable_pt_branding', '__return_true' );


function ocdi_plugin_intro_text( $default_text ) {

	function xriver_let_to_num( $size ) {
		$l = substr( $size, -1 );
		$ret = substr( $size, 0, -1 );
		switch ( strtoupper( $l ) ) {
		case 'P':
			$ret *= 1024;
		case 'T':
			$ret *= 1024;
		case 'G':
			$ret *= 1024;
		case 'M':
			$ret *= 1024;
		case 'K':
			$ret *= 1024;
		}
		return $ret;
	}
	$ssl_check = 'https' === substr( get_home_url(), 0, 5 );
	$green_mark = '<mark class="green"><span class="dashicons dashicons-yes"></span></mark>';

	$tatheme = wp_get_theme();

	$plugins_counts = (array) get_option( 'active_plugins', [] );

	if ( is_multisite() ) {
		$network_activated_plugins = array_keys( get_site_option( 'active_sitewide_plugins', [] ) );
		$plugins_counts = array_merge( $plugins_counts, $network_activated_plugins );
	}

	$default_text = '';

	$default_text .= '
	<div class="demo__ip-notice">
<table class="system-status-table">
	<h1>'.esc_html__('Importan Notice: Before demo import please make sure your server meet(green) all required options for installing the theme').'</h1>
	<p class="error">'.esc_html__('If any of the option is under red mark, please contact your hosting provider and ask them to change it as recommended.').'</p>
	<tbody>
	<tr>
	<td>' . esc_html__("WP Version", "groser") . '</td>
	<td>
	' . esc_html($GLOBALS['wp_version']) . '
	<mark class="green">- We recommend using WordPress version 5.1 or above for greater performance and security.</mark></td>
	</tr>
	<tr>
	<td>' . esc_html__("Language", "groser") . '</td>
	<td>' . get_locale() . '</td>
	</tr>
	<tr>
	<td>' . esc_html__("WP Memory Limit", "groser") . '</td>
	<td>';

		$memory = xriver_let_to_num(WP_MEMORY_LIMIT);
		if ($memory < 100663296) {
			$default_text .= '
			<mark class="error">' . sprintf(esc_html__('%s - We recommend setting memory to at least 96MB. %s.', "groser"), size_format($memory), '
				<a href="' . esc_url('//www.wpbeginner.com/wp-tutorials/fix-wordpress-memory-exhausted-error-increase-php-memory/') . '" target="_blank">' . esc_html__('More info', "groser") . '</a>') . '
			</mark>';
		} else {
			$default_text .= '
			<mark class="green">' . size_format($memory) . '</mark>';
		}

		$default_text .= '
			</td>
		</tr>
		<tr>
			<td>' . esc_html__('PHP Max Input Vars', "groser") . '</td>
			<td>';

		$max_input = ini_get('max_input_vars');
		if ($max_input < 3000) {
			$default_text .= '
			<mark class="error">' . sprintf(wp_kses(__( '%s - We recommend setting PHP max_input_vars to at least 3000. See:
				<a href="%s" target="_blank">Increasing the PHP max vars limit</a>', "groser"), ['a' => ['href' => [], 'target' => []]]), $max_input, 'https://jannah.helpscoutdocs.com/article/7-how-to-increase-the-php-max-input-vars') . '
			</mark>';
		} else {
			$default_text .= '
			<mark class="green">' . $max_input . '</mark>';
		}

		$default_text .= ' </td>
		</tr>
		<tr>
			<td>' . esc_html__('PHP Version', "groser") . ' </td>
			<td>';

		$mayo_php = phpversion();
		if (version_compare($mayo_php, '7.2', '<')) {
			$default_text .= sprintf('
			<mark class="error"> %s </mark> - We recommend using PHP version 7.2 or above for greater performance and security.', esc_html($mayo_php), '');
		} else {
			$default_text .= '
			<mark class="green">' . esc_html($mayo_php) . '</mark>';
		}

		$default_text .= ' </td>
		</tr>
		<tr>
			<td>' . esc_html__('Server Info', "groser") . ' </td>
			<td>' . esc_html($_SERVER['SERVER_SOFTWARE']) . '</td>
		</tr>
		<tr>
			<td>' . esc_html__('Secure Connection(HTTPS)', "groser") . ' </td>
			<td>' . (esc_attr($ssl_check) ? $green_mark : '<mark class="error">Your site is not using a secure connection (HTTPS).</mark>') . '</td>
		</tr>
		</tbody>
	</table>
	</div> ';

	return $default_text;

}
add_filter( 'pt-ocdi/plugin_intro_text', 'ocdi_plugin_intro_text' );