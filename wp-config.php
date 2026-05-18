<?php
/**
 * WordPress configuration — composer-managed setup.
 *
 * Reads config from environment variables provided by docker-compose.
 * Falls back to sensible defaults for direct/CLI usage.
 */

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$envOrDefault = static function (string $key, $default = null) {
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
};

define('DB_NAME',     $envOrDefault('WORDPRESS_DB_NAME', 'shobjiwala'));
define('DB_USER',     $envOrDefault('WORDPRESS_DB_USER', 'wp'));
define('DB_PASSWORD', $envOrDefault('WORDPRESS_DB_PASSWORD', 'wp_password'));
define('DB_HOST',     $envOrDefault('WORDPRESS_DB_HOST', 'db'));
define('DB_CHARSET',  'utf8mb4');
define('DB_COLLATE',  '');

$table_prefix = $envOrDefault('WORDPRESS_TABLE_PREFIX', 'KAW_');

define('AUTH_KEY',         $envOrDefault('WORDPRESS_AUTH_KEY',         'change-me-auth-key'));
define('SECURE_AUTH_KEY',  $envOrDefault('WORDPRESS_SECURE_AUTH_KEY',  'change-me-secure-auth-key'));
define('LOGGED_IN_KEY',    $envOrDefault('WORDPRESS_LOGGED_IN_KEY',    'change-me-logged-in-key'));
define('NONCE_KEY',        $envOrDefault('WORDPRESS_NONCE_KEY',        'change-me-nonce-key'));
define('AUTH_SALT',        $envOrDefault('WORDPRESS_AUTH_SALT',        'change-me-auth-salt'));
define('SECURE_AUTH_SALT', $envOrDefault('WORDPRESS_SECURE_AUTH_SALT', 'change-me-secure-auth-salt'));
define('LOGGED_IN_SALT',   $envOrDefault('WORDPRESS_LOGGED_IN_SALT',   'change-me-logged-in-salt'));
define('NONCE_SALT',       $envOrDefault('WORDPRESS_NONCE_SALT',       'change-me-nonce-salt'));

$wpHome    = $envOrDefault('WP_HOME', 'http://localhost:8080');
$wpSiteurl = $envOrDefault('WP_SITEURL', $wpHome);
define('WP_HOME',    $wpHome);
define('WP_SITEURL', $wpSiteurl);

define('WP_DEBUG',         filter_var($envOrDefault('WP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN));
define('WP_DEBUG_LOG',     true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG',     false);

define('DISALLOW_FILE_EDIT', true);
define('WP_POST_REVISIONS', 5);
define('AUTOMATIC_UPDATER_DISABLED', true);

if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
