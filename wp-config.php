<?php
/**
 * WordPress configuration — composer-managed setup.
 *
 * Reads config from environment variables provided by docker-compose.
 * Falls back to sensible defaults for direct/CLI usage.
 *
 * @package Shobjiwala
 */

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

if ( ! function_exists( 'shobjiwala_env' ) ) {
	/**
	 * Resolve an env var with a fallback default.
	 *
	 * @param string $key      Environment variable name.
	 * @param mixed  $fallback Returned when the env var is unset or empty.
	 * @return mixed
	 */
	function shobjiwala_env( $key, $fallback = null ) {
		$value = getenv( $key );
		if ( false === $value || '' === $value ) {
			return $fallback;
		}
		return $value;
	}
}

define( 'DB_NAME', shobjiwala_env( 'WORDPRESS_DB_NAME', 'shobjiwala' ) );
define( 'DB_USER', shobjiwala_env( 'WORDPRESS_DB_USER', 'wp' ) );
define( 'DB_PASSWORD', shobjiwala_env( 'WORDPRESS_DB_PASSWORD', 'wp_password' ) );
define( 'DB_HOST', shobjiwala_env( 'WORDPRESS_DB_HOST', 'db' ) );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- WP requires this global to set the table prefix.
$table_prefix = shobjiwala_env( 'WORDPRESS_TABLE_PREFIX', 'KAW_' );

define( 'AUTH_KEY', shobjiwala_env( 'WORDPRESS_AUTH_KEY', 'change-me-auth-key' ) );
define( 'SECURE_AUTH_KEY', shobjiwala_env( 'WORDPRESS_SECURE_AUTH_KEY', 'change-me-secure-auth-key' ) );
define( 'LOGGED_IN_KEY', shobjiwala_env( 'WORDPRESS_LOGGED_IN_KEY', 'change-me-logged-in-key' ) );
define( 'NONCE_KEY', shobjiwala_env( 'WORDPRESS_NONCE_KEY', 'change-me-nonce-key' ) );
define( 'AUTH_SALT', shobjiwala_env( 'WORDPRESS_AUTH_SALT', 'change-me-auth-salt' ) );
define( 'SECURE_AUTH_SALT', shobjiwala_env( 'WORDPRESS_SECURE_AUTH_SALT', 'change-me-secure-auth-salt' ) );
define( 'LOGGED_IN_SALT', shobjiwala_env( 'WORDPRESS_LOGGED_IN_SALT', 'change-me-logged-in-salt' ) );
define( 'NONCE_SALT', shobjiwala_env( 'WORDPRESS_NONCE_SALT', 'change-me-nonce-salt' ) );

$wp_home    = shobjiwala_env( 'WP_HOME', 'http://localhost:8080' );
$wp_siteurl = shobjiwala_env( 'WP_SITEURL', $wp_home );
define( 'WP_HOME', $wp_home );
define( 'WP_SITEURL', $wp_siteurl );

define( 'WP_DEBUG', filter_var( shobjiwala_env( 'WP_DEBUG', 'true' ), FILTER_VALIDATE_BOOLEAN ) );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', false );

define( 'DISALLOW_FILE_EDIT', true );
define( 'WP_POST_REVISIONS', 5 );
define( 'AUTOMATIC_UPDATER_DISABLED', true );

if ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
	$_SERVER['HTTPS'] = 'on';
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
