FROM wordpress:php8.3-apache

# Persistent WP-CLI install — runs inside the same image so `docker compose exec wordpress wp ...` works out of the box.
RUN set -eux; \
    curl -sSL -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar; \
    chmod +x /usr/local/bin/wp; \
    /usr/local/bin/wp --info --allow-root

# Bump PHP memory limit — WooCommerce + Elementor + Jetpack on a single request can push past the 128M default.
RUN { \
    echo 'memory_limit = 512M'; \
    echo 'upload_max_filesize = 128M'; \
    echo 'post_max_size = 128M'; \
    echo 'max_execution_time = 300'; \
    } > /usr/local/etc/php/conf.d/zz-shobjiwala.ini
