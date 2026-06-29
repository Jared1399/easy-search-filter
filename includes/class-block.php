<?php
namespace EasySearchFilter;

defined('ABSPATH') || exit;

class Block {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register_blocks']);
    }

    public function register_blocks() {
        if (!esf_fs()->can_use_premium_code__premium_only()) {
            return;
        }

        wp_register_script(
            'esp-block-editor',
            ESP_URL . 'admin/js/block.js',
            ['wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-i18n'],
            ESP_VERSION,
            true
        );

        $groups = Filter_Group::instance()->get_groups();
        $options = [];
        foreach ($groups as $group) {
            $options[] = [
                'label' => $group->name . ' (ID: ' . $group->ID . ')',
                'value' => $group->ID,
            ];
        }

        wp_localize_script('esp-block-editor', 'espBlockData', [
            'groups' => $options,
        ]);

        register_block_type('esp/search-form', [
            'editor_script'   => 'esp-block-editor',
            'render_callback' => [$this, 'render_form_block'],
            'attributes'      => [
                'groupId' => [
                    'type' => 'number',
                    'default' => 0,
                ],
                'showResults' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
            ],
        ]);

        register_block_type('esp/search-results', [
            'editor_script'   => 'esp-block-editor',
            'render_callback' => [$this, 'render_results_block'],
            'attributes'      => [
                'groupId' => [
                    'type' => 'number',
                    'default' => 0,
                ],
            ],
        ]);
    }

    public function render_form_block($attributes) {
        if (!esf_fs()->can_use_premium_code__premium_only()) {
            return '';
        }
        $group_id = absint($attributes['groupId'] ?? 0);
        if (!$group_id) {
            return '<p>' . esc_html__('Select a filter group in the block settings.', 'easy-search-filter') . '</p>';
        }
        return do_shortcode('[esp_form id="' . $group_id . '"]');
    }

    public function render_results_block($attributes) {
        if (!esf_fs()->can_use_premium_code__premium_only()) {
            return '';
        }
        $group_id = absint($attributes['groupId'] ?? 0);
        if (!$group_id) {
            return '<p>' . esc_html__('Select a filter group in the block settings.', 'easy-search-filter') . '</p>';
        }
        return do_shortcode('[esp_results id="' . $group_id . '"]');
    }
}
