<?php

class Gv_Splat_Shortcode {
    const CACHE_PREFIX = 'gv_splat_data_';
    const CACHE_EXPIRATION = 1800; // 30 minutes
    const UPLOAD_DIR = 'splats'; // Subdirectory in wp-content/uploads

    /**
     * Initialize the shortcode
     */
    public static function init() {
        add_shortcode('splat_shortcode', array(__CLASS__, 'render_splat_shortcode'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_splat_scripts'));

        // Hooks for cache clearing
        add_action('gv_splat_updated', array(__CLASS__, 'clear_cache'));
        add_action('gv_splat_deleted', array(__CLASS__, 'clear_cache'));
    }

    /**
     * Get or create the upload directory for splats
     */
    private static function get_upload_dir()
    {
        $wp_upload_dir = wp_upload_dir();
        $splat_dir = $wp_upload_dir['basedir'] . '/' . self::UPLOAD_DIR;

        // Create directory if it doesn't exist
        if (!file_exists($splat_dir)) {
            wp_mkdir_p($splat_dir);

            // Create .htaccess to protect directory
            $htaccess = $splat_dir . '/.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, 'Options -Indexes');
            }
        }

        return [
            'path' => $splat_dir,
            'url' => $wp_upload_dir['baseurl'] . '/' . self::UPLOAD_DIR
        ];
    }

    /**
     * Download and store splat file
     */
    private static function download_splat_file($url, $splat_id)
    {
        $upload_dir = self::get_upload_dir();
        $file_name = "splat_{$splat_id}.splat";
        $file_path = $upload_dir['path'] . '/' . $file_name;
        $file_url = $upload_dir['url'] . '/' . $file_name;

        // Check if file already exists
        if (file_exists($file_path)) {
            return $file_url;
        }

        // Download file
        $response = wp_remote_get($url, [
            'timeout' => 60,
            'stream' => true,
            'filename' => $file_path
        ]);

        if (is_wp_error($response)) {
            error_log('Splat download failed: ' . $response->get_error_message());
            return false;
        }

        if (wp_remote_retrieve_response_code($response) !== 200) {
            error_log('Splat download failed: HTTP ' . wp_remote_retrieve_response_code($response));
            return false;
        }

        return $file_url;
    }

    /**
     * Get Splat data with caching and local storage
     */
    private static function get_cached_splat($splat_id)
    {
        // Generate cache keys
        $cache_key = self::CACHE_PREFIX . $splat_id;
        $local_cache_key = self::CACHE_PREFIX . 'local_' . $splat_id;

        // Check for locally stored file first
        $local_path = get_transient($local_cache_key);
        if ($local_path !== false) {
            return [
                'success' => true,
                'responseObject' => [
                    'storage_url' => $local_path
                ]
            ];
        }

        // Try to get API data from cache
        $cached_data = get_transient($cache_key);
        if ($cached_data !== false) {
            // Download and store the file locally
            if (!empty($cached_data['responseObject']['storage_url'])) {
                $local_url = self::download_splat_file(
                    $cached_data['responseObject']['storage_url'],
                    $splat_id
                );
                if ($local_url) {
                    $cached_data['responseObject']['storage_url'] = $local_url;
                    set_transient($local_cache_key, $local_url, self::CACHE_EXPIRATION);
                }
            }
            return $cached_data;
        }

        // If not in cache, fetch from API
        $splat_data = Gv_Splat_HTTP::get_splat($splat_id);

        // Only cache successful responses
        if (isset($splat_data['success']) && $splat_data['success']) {
            set_transient($cache_key, $splat_data, self::CACHE_EXPIRATION);

            // Download and store the file locally
            if (!empty($splat_data['responseObject']['storage_url'])) {
                $local_url = self::download_splat_file(
                    $splat_data['responseObject']['storage_url'],
                    $splat_id
                );
                if ($local_url) {
                    $splat_data['responseObject']['storage_url'] = $local_url;
                    set_transient($local_cache_key, $local_url, self::CACHE_EXPIRATION);
                }
            }
        }

        return $splat_data;
    }

    /**
     * Render the Splat shortcode
     */
    public static function render_splat_shortcode($atts)
    {
        // Set default attributes
        $atts = shortcode_atts(array(
            'id' => 0,
            'width' => '100%',
            'height' => '100dvh',
            'animate' => false,
            'class' => '',
            'style' => '',
        ), $atts, 'splat_shortcode');

        $splat_id = intval($atts['id']);

        if (!$splat_id) {
            return '<div class="notice notice-error">No valid Splat ID provided.</div>';
        }

        // Fetch the Splat data with caching and local storage
        $splat_data = self::get_cached_splat($splat_id);

        if (!isset($splat_data['success']) || !$splat_data['success']) {
            return sprintf(
                '<div class="notice notice-error">Error fetching Splat data (ID: %d): %s</div>',
                $splat_id,
                isset($splat_data['message']) ? esc_html($splat_data['message']) : 'Unknown error'
            );
        }

        // Build the container style
        $container_style = sprintf(
            'width:%s;height:%s;%s',
            esc_attr($atts['width']),
            esc_attr($atts['height']),
            esc_attr($atts['style'])
        );

        $output = '';

        if (!empty($splat_data['responseObject']['storage_url'])) {
            $output .= sprintf('<div style="%s">', $container_style);
            $output .= '<cds-splat';
            $output .= sprintf(' src="%s"', esc_url($splat_data['responseObject']['storage_url']));

            if (!empty($atts['animate'])) {
                $output .= ' animate';
            }

            if (!empty($splat_data['responseObject']['thumbnail_url'])) {
                $output .= sprintf(' thumbnail="%s"', esc_url($splat_data['responseObject']['thumbnail_url']));
            } else {
                $output .= ' thumbnail="https://staging.green-view.nl/logo.png"';
            }

            if (!empty($atts['class'])) {
                $output .= sprintf(' class="%s"', esc_attr($atts['class']));
            }

            $output .= '></cds-splat>';
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Clear cache and local files for a specific splat
     */
    public static function clear_cache($splat_id)
    {
        delete_transient(self::CACHE_PREFIX . $splat_id);
        delete_transient(self::CACHE_PREFIX . 'local_' . $splat_id);

        // Delete local file
        $upload_dir = self::get_upload_dir();
        $file_path = $upload_dir['path'] . "/splat_{$splat_id}.splat";
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    /**
     * Clear all splat caches and local files
     */
    public static function clear_all_caches()
    {
        global $wpdb;

        // Clear all transients
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_' . self::CACHE_PREFIX . '%',
                '_transient_' . self::CACHE_PREFIX . 'local_%'
            )
        );

        // Clear all local files
        $upload_dir = self::get_upload_dir();
        array_map('unlink', glob($upload_dir['path'] . "/splat_*.splat"));
    }

    /**
     * Enqueue necessary scripts for the web component
     */
    public static function enqueue_splat_scripts()
    {
        // Enqueue the web component script
        wp_enqueue_script(
            'cds-splat',
            plugin_dir_url(dirname(__FILE__)) . 'public/js/cds-splat.iife.js',
            array(),
            '1.0.0',
            true
        );

        // Add process.env definition
        wp_add_inline_script('cds-splat', 'window.process = { env: { NODE_ENV: "production" } };', 'before');

        // Add required CSS
        wp_add_inline_style('cds-splat', '
            cds-splat {
                display: block;
                width: 100%;
                height: 100%;
            }
        ');
    }
}
