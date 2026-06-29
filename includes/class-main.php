<?php
namespace EasySearchFilter;

defined('ABSPATH') || exit;

final class Main {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('plugins_loaded', [$this, 'load_modules']);
        add_action('init', [$this, 'init']);
        add_action('before_woocommerce_init', [$this, 'declare_wc_compatibility']);
    }

    public function load_modules() {
        I18n::instance();

        if (is_admin()) {
            Admin::instance();
        }

        Taxonomy_Manager::instance();
        Filter_Group::instance();
        Shortcodes::instance();
        Ajax::instance();
        Template::instance();
        Frontend::instance();

        if (esf_fs()->can_use_premium_code__premium_only()) {
            Block::instance();
            Indexer::instance();
        }
    }

    public function init() {
        Filter_Group::register_post_type();
    }

    public function declare_wc_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', ESP_FILE, true);
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', ESP_FILE, false);
        }
    }
}
