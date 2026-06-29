<?php
namespace EasySearchFilter;

defined('ABSPATH') || exit;

class Frontend {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('esp-nouislider', ESP_URL . 'public/vendor/nouislider/nouislider.min.css', [], '15.8.0');
        wp_enqueue_style('esp-frontend', ESP_URL . 'public/css/esp-frontend.css', ['esp-nouislider'], ESP_VERSION);
        wp_enqueue_script('esp-nouislider', ESP_URL . 'public/vendor/nouislider/nouislider.min.js', [], '15.8.0', true);
        wp_enqueue_script('esp-frontend', ESP_URL . 'public/js/esp-frontend.js', ['jquery', 'esp-nouislider'], ESP_VERSION, true);

        wp_localize_script('esp-frontend', 'espData', [
            'ajaxUrl'   => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce('esp_frontend_nonce'),
            'isPremium' => esf_fs()->can_use_premium_code(),
            'i18n'      => [
                'loading'     => __('Loading...', 'easy-search-filter'),
                'select'      => __('Select...', 'easy-search-filter'),
                'noResults'   => __('No results found.', 'easy-search-filter'),
                'min'         => __('Min', 'easy-search-filter'),
                'max'         => __('Max', 'easy-search-filter'),
                'search'      => __('Search', 'easy-search-filter'),
                'clear'       => __('Clear', 'easy-search-filter'),
            ],
        ]);
    }
}
