<?php
/**
 * PHPStan bootstrap — declares constants/globals defined at runtime by
 * docker-compose env or wp-config so PHPStan does not flag them.
 */

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', false);
}
if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', __DIR__ . '/wp-content');
}
