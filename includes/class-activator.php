<?php
namespace EasySearchFilter;

defined('ABSPATH') || exit;

class Activator {
    public static function activate() {
        Filter_Group::register_post_type();

        if (class_exists('\EasySearchFilter\Indexer')) {
            Indexer::create_table();
        }

        flush_rewrite_rules();

        if (!get_option('esp_settings')) {
            update_option('esp_settings', [
                'enable_auto_detect' => true,
                'default_per_page'   => 12,
                'default_columns'    => 3,
            ]);
        }

        if (!get_option('esp_taxonomies')) {
            update_option('esp_taxonomies', []);
        }

        if (!get_option('esp_version')) {
            add_option('esp_version', ESP_VERSION);
        } else {
            update_option('esp_version', ESP_VERSION);
        }
    }
}
