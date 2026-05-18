<?php
/**
 * Plugin Name: Shobjiwala — Security hardening
 * Description: Closes unauthenticated user-enumeration vectors (REST + author archive), disables XML-RPC, and turns off pingbacks. Loaded as a must-use plugin so it cannot be deactivated from the admin UI. Tracks findings H1–H3 from docs/superpowers/specs/2026-05-18-security-review.md.
 * Author: Shobjiwala
 * Version: 1.0.0
 *
 * @package Shobjiwala
 */

defined( 'ABSPATH' ) || exit;

/**
 * H3 — short-circuit XML-RPC before any method dispatches.
 *
 * The `xmlrpc_methods` filter only covers the user-facing method registry on
 * `wp_xmlrpc_server`; the `system.*` introspection methods live on the IXR
 * parent class and bypass it. xmlrpc.php defines `XMLRPC_REQUEST` before
 * loading WordPress, so we can refuse the request at mu-plugin load time —
 * earlier than any filter, before any method runs.
 */
if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
	http_response_code( 403 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	exit( "XML-RPC is disabled on this site.\n" );
}

/**
 * H1 — block the `users` REST endpoint for unauthenticated callers.
 *
 * Returning a 401 from the `users` collection / single endpoints stops the
 * /wp-json/wp/v2/users disclosure without breaking the rest of the REST API
 * (which other plugins, the block editor, and the WC store all rely on).
 */
add_filter(
	'rest_endpoints',
	function ( $endpoints ) {
		foreach ( array( '/wp/v2/users', '/wp/v2/users/(?P<id>[\\d]+)' ) as $route ) {
			if ( isset( $endpoints[ $route ] ) ) {
				unset( $endpoints[ $route ] );
			}
		}
		return $endpoints;
	}
);

/**
 * H2 — disable author archives.
 *
 * `/?author=N` and `/author/<slug>/` both 302 to the homepage. Removes the
 * second public user-enumeration vector. Authors who need a public profile
 * page should publish one through a regular post / page.
 */
// Priority 0 so we beat redirect_canonical (priority 10), which would
// otherwise emit a `Location: /author/<slug>/` and leak the slug.
add_action(
	'template_redirect',
	function () {
		if ( is_author() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	},
	0
);

// Catch /?author=N before WP parses the request so the slug never resolves.
// Gated to frontend requests — wp-admin uses ?author=N for post-filter UIs
// and the REST API for author filters on the posts collection.
add_action(
	'init',
	function () {
		if ( is_admin() ) {
			return;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return;
		}
		if ( isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	},
	0
);

// Defense-in-depth — keep the filters in place so any code path that *does*
// reach a filter still sees XML-RPC as disabled.
add_filter( 'xmlrpc_enabled', '__return_false' );

add_filter(
	'xmlrpc_methods',
	function () {
		return array();
	},
	PHP_INT_MAX
);

// Drop the X-Pingback response header so the endpoint isn't advertised in
// link headers / discovery, even though the early exit above would have
// blocked any incoming pingback.
add_filter(
	'wp_headers',
	function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);

remove_action( 'wp_head', 'rsd_link' );
