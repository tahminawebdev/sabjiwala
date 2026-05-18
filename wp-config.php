<?php
/**
 * WordPress configuration — composer-managed setup.
 *
 * Reads config from environment variables provided by docker-compose. Secrets
 * (auth keys, DB password) have NO fallback defaults — the site fails closed
 * if the env vars are not set. Non-secret values (DB name, host, table prefix)
 * keep sensible defaults so the file is still runnable for read-only wp-cli
 * commands in unusual contexts.
 *
 * @package Shobjiwala
 */

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

if ( ! function_exists( 'shobjiwala_env' ) ) {
	/**
	 * Resolve an env var with an optional fallback.
	 *
	 * @param string $key      Environment variable name.
	 * @param mixed  $fallback Returned when the env var is unset or empty. Null = no fallback.
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

if ( ! function_exists( 'shobjiwala_env_required' ) ) {
	/**
	 * Resolve a REQUIRED env var. Throws RuntimeException if unset or empty.
	 * Use for secrets and DB credentials where shipping a default is a security hole.
	 *
	 * @param string $key Environment variable name.
	 * @return string
	 */
	function shobjiwala_env_required( $key ) {
		$value = getenv( $key );
		if ( false === $value || '' === $value ) {
			throw new RuntimeException(
				sprintf( 'Required environment variable %s is not set. Refusing to boot with a fallback default.', $key )
			);
		}
		return $value;
	}
}

define( 'DB_NAME', shobjiwala_env( 'WORDPRESS_DB_NAME', 'shobjiwala' ) );
define( 'DB_USER', shobjiwala_env( 'WORDPRESS_DB_USER', 'wp' ) );
define( 'DB_PASSWORD', shobjiwala_env_required( 'WORDPRESS_DB_PASSWORD' ) );
define( 'DB_HOST', shobjiwala_env( 'WORDPRESS_DB_HOST', 'db' ) );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- WP requires this global to set the table prefix.
$table_prefix = shobjiwala_env( 'WORDPRESS_TABLE_PREFIX', 'KAW_' );

define( 'AUTH_KEY',         shobjiwala_env_required( 'WORDPRESS_AUTH_KEY' ) );
define( 'SECURE_AUTH_KEY',  shobjiwala_env_required( 'WORDPRESS_SECURE_AUTH_KEY' ) );
define( 'LOGGED_IN_KEY',    shobjiwala_env_required( 'WORDPRESS_LOGGED_IN_KEY' ) );
define( 'NONCE_KEY',        shobjiwala_env_required( 'WORDPRESS_NONCE_KEY' ) );
define( 'AUTH_SALT',        shobjiwala_env_required( 'WORDPRESS_AUTH_SALT' ) );
define( 'SECURE_AUTH_SALT', shobjiwala_env_required( 'WORDPRESS_SECURE_AUTH_SALT' ) );
define( 'LOGGED_IN_SALT',   shobjiwala_env_required( 'WORDPRESS_LOGGED_IN_SALT' ) );
define( 'NONCE_SALT',       shobjiwala_env_required( 'WORDPRESS_NONCE_SALT' ) );

$wp_home    = shobjiwala_env( 'WP_HOME', 'http://localhost:8080' );
$wp_siteurl = shobjiwala_env( 'WP_SITEURL', $wp_home );
define( 'WP_HOME', $wp_home );
define( 'WP_SITEURL', $wp_siteurl );

define( 'WP_DEBUG', filter_var( shobjiwala_env( 'WP_DEBUG', 'false' ), FILTER_VALIDATE_BOOLEAN ) );
// Do not write debug output to wp-content/debug.log (was web-readable).
// PHP errors still flow through Apache to docker logs; read with
// `docker compose logs -f wordpress`.
define( 'WP_DEBUG_LOG', false );
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
