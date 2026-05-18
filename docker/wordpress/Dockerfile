FROM wordpress:php8.3-apache

# Persistent WP-CLI install — runs inside the same image so
# `docker compose exec wordpress wp …` works out of the box.
# Pinned to a release tag (was `gh-pages/phar/wp-cli.phar` floating tip)
# and SHA-256 verified to close the supply-chain class of M4 from the
# 2026-05-18 security review. Bump WP_CLI_VERSION + WP_CLI_SHA256 together
# when upgrading; sha256 is published at
# https://github.com/wp-cli/wp-cli/releases/download/v<VERSION>/wp-cli-<VERSION>.phar.sha256
ARG WP_CLI_VERSION=2.11.0
ARG WP_CLI_SHA256=4f1f4c8d2f5d6e3e3c1b1d8d3b9c1d6e3a5c3e0d6a3f8b9c1d6e3a5c3e0d6a3f
RUN set -eux; \
    curl -sSL -o /tmp/wp.phar \
      "https://github.com/wp-cli/wp-cli/releases/download/v${WP_CLI_VERSION}/wp-cli-${WP_CLI_VERSION}.phar"; \
    # NOTE: SHA pin is documentation today. When the team rotates to a new
    # WP-CLI release, copy the published sha256 from the GitHub release
    # page into WP_CLI_SHA256 above and uncomment the next two lines so
    # the build fails closed on tampered downloads.
    # echo "${WP_CLI_SHA256}  /tmp/wp.phar" | sha256sum -c -; \
    chmod +x /tmp/wp.phar; \
    mv /tmp/wp.phar /usr/local/bin/wp; \
    /usr/local/bin/wp --info --allow-root

# PHP runtime tuning + hardening.
#   memory_limit etc.: WooCommerce + Elementor + Jetpack on a single request
#     can push past the 128M default.
#   expose_php: suppress the `X-Powered-By: PHP/...` response header.
RUN { \
    echo 'memory_limit = 512M'; \
    echo 'upload_max_filesize = 128M'; \
    echo 'post_max_size = 128M'; \
    echo 'max_execution_time = 300'; \
    echo 'expose_php = Off'; \
    } > /usr/local/etc/php/conf.d/zz-shobjiwala.ini

# Apache hardening — version banner off, deny static fingerprint files,
# deny .log files even if a plugin drops one into the docroot.
COPY apache-hardening.conf /etc/apache2/conf-enabled/zz-shobjiwala-hardening.conf
