<?php
namespace EasySearchFilter;

defined('ABSPATH') || exit;

class Filter_Group {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    public static function register_post_type() {
        register_post_type('esp_filter', [
            'label'               => __('Filter Groups', 'easy-search-filter'),
            'public'              => false,
            'show_ui'             => false,
            'show_in_menu'        => false,
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'supports'            => ['title'],
        ]);
    }

    public function get_groups() {
        $posts = get_posts([
            'post_type'      => 'esp_filter',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'any',
        ]);

        $groups = [];
        foreach ($posts as $post) {
            $groups[] = $this->hydrate_group($post);
        }
        return $groups;
    }

    public function get_group($id) {
        $post = get_post($id);
        if (!$post || $post->post_type !== 'esp_filter') {
            return null;
        }
        return $this->hydrate_group($post);
    }

    public function save_group($data, $group_id = 0) {
        if (empty($data['name'])) {
            return new \WP_Error('missing_name', __('Group name is required.', 'easy-search-filter'));
        }

        $post_data = [
            'post_title'  => $data['name'],
            'post_type'   => 'esp_filter',
            'post_status' => 'publish',
        ];

        $meta = [
            'post_type'   => $data['post_type'] ?? 'post',
            'taxonomies'  => $data['taxonomies'] ?? [],
            'meta_fields' => $data['meta_fields'] ?? [],
            'display'     => $data['display'] ?? [
                'layout'          => 'grid',
                'columns'         => 3,
                'per_page'        => 12,
                'orderby'         => 'date',
                'order'               => 'DESC',
                'enable_indexer'      => false,
                'hide_zero_count'     => false,
                'enable_search'       => false,
                'search_placeholder'  => 'Search...',
                'sort_options'    => ['date-desc', 'date-asc', 'title-asc', 'title-desc', 'price-asc', 'price-desc'],
            ],
        ];

        if ($group_id) {
            $post_data['ID'] = $group_id;
            wp_update_post($post_data);
        } else {
            $group_id = wp_insert_post($post_data);
        }

        update_post_meta($group_id, '_esp_config', $meta);

        return $group_id;
    }

    public function delete_group($id) {
        return wp_delete_post($id, true);
    }

    public function get_config($id) {
        $meta = get_post_meta($id, '_esp_config', true);
        return $meta ?: [];
    }

    private function hydrate_group($post) {
        $meta = get_post_meta($post->ID, '_esp_config', true) ?: [];

        return (object) [
            'ID'         => $post->ID,
            'name'       => $post->post_title,
            'post_type'  => $meta['post_type'] ?? 'post',
            'taxonomies' => $meta['taxonomies'] ?? [],
            'meta_fields' => $meta['meta_fields'] ?? [],
            'display'    => $meta['display'] ?? [
                'layout'          => 'grid',
                'columns'         => 3,
                'per_page'        => 12,
                'orderby'         => 'date',
                'order'               => 'DESC',
                'enable_indexer'      => false,
                'hide_zero_count'     => false,
                'enable_search'       => false,
                'search_placeholder'  => 'Search...',
                'sort_options'    => ['date-desc', 'date-asc', 'title-asc', 'title-desc', 'price-asc', 'price-desc'],
            ],
        ];
    }
}
