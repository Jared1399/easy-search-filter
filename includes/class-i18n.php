<?php
namespace EasySearchFilter;

defined('ABSPATH') || exit;

class I18n {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'easy-search-filter',
            false,
            dirname(ESP_BASENAME) . '/languages'
        );
    }
}
