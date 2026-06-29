<?php
namespace EasySearchFilter;

defined('ABSPATH') || exit;

class Taxonomy_Manager {
    private static $instance = null;
    private $stored_taxonomies = [];

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register_taxonomies'], 5);
    }

    public function register_taxonomies() {
        $this->stored_taxonomies = get_option('esp_taxonomies', []);

        if (empty($this->stored_taxonomies)) {
            return;
        }

        foreach ($this->stored_taxonomies as $tax) {
            if (taxonomy_exists($tax['slug'])) {
                continue;
            }

            $post_types = !empty($tax['post_types']) ? $tax['post_types'] : ['post'];

            register_taxonomy($tax['slug'], $post_types, [
                'label'              => $tax['name'],
                'labels'             => [
                    'name'              => $tax['name'],
                    'singular_name'     => $tax['singular_name'],
                    'search_items'      => sprintf(__('Search %s', 'easy-search-filter'), $tax['name']),
                    'all_items'         => sprintf(__('All %s', 'easy-search-filter'), $tax['name']),
                    'edit_item'         => sprintf(__('Edit %s', 'easy-search-filter'), $tax['singular_name']),
                    'update_item'       => sprintf(__('Update %s', 'easy-search-filter'), $tax['singular_name']),
                    'add_new_item'      => sprintf(__('Add New %s', 'easy-search-filter'), $tax['singular_name']),
                    'new_item_name'     => sprintf(__('New %s Name', 'easy-search-filter'), $tax['singular_name']),
                    'parent_item'       => __('Parent', 'easy-search-filter'),
                    'parent_item_colon' => __('Parent:', 'easy-search-filter'),
                ],
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => false,
                'show_in_menu'       => false,
                'show_in_rest'       => true,
                'hierarchical'       => true,
                'rewrite'            => ['slug' => $tax['slug']],
            ]);
        }
    }

    public function get_taxonomies() {
        return get_option('esp_taxonomies', []);
    }

    public function get_taxonomy($slug) {
        $taxonomies = $this->get_taxonomies();
        foreach ($taxonomies as $tax) {
            if ($tax['slug'] === $slug) {
                return $tax;
            }
        }
        return null;
    }

    public function save_taxonomy($data) {
        $taxonomies = $this->get_taxonomies();

        $slug = sanitize_title($data['slug']);
        $is_new = !isset($data['original_slug']) || $data['original_slug'] !== $slug;

        if ($is_new) {
            foreach ($taxonomies as $existing) {
                if ($existing['slug'] === $slug) {
                    return new \WP_Error('duplicate_slug', __('A taxonomy with this slug already exists.', 'easy-search-filter'));
                }
            }
        }

        $entry = [
            'slug'           => $slug,
            'name'           => sanitize_text_field($data['name']),
            'singular_name'  => sanitize_text_field($data['singular_name']),
            'description'    => sanitize_textarea_field($data['description'] ?? ''),
            'hierarchical'   => true,
            'max_depth'      => absint($data['max_depth'] ?? 0),
            'post_types'     => !empty($data['post_types']) ? array_map('sanitize_text_field', (array) $data['post_types']) : ['post'],
        ];

        if ($is_new) {
            $taxonomies[] = $entry;
        } else {
            foreach ($taxonomies as &$existing) {
                if ($existing['slug'] === $data['original_slug']) {
                    $existing = $entry;
                    break;
                }
            }
        }

        update_option('esp_taxonomies', $taxonomies);

        if ($is_new || $slug !== $data['original_slug']) {
            if ($is_new) {
                $this->register_single_taxonomy($entry);
            }
            flush_rewrite_rules();
        }

        return true;
    }

    public function delete_taxonomy($slug) {
        $taxonomies = $this->get_taxonomies();
        $found = false;

        foreach ($taxonomies as $key => $tax) {
            if ($tax['slug'] === $slug) {
                $terms = get_terms(['taxonomy' => $slug, 'hide_empty' => false, 'fields' => 'ids']);
                foreach ($terms as $term_id) {
                    wp_delete_term($term_id, $slug);
                }
                unset($taxonomies[$key]);
                $found = true;
                break;
            }
        }

        if (!$found) {
            return new \WP_Error('not_found', __('Taxonomy not found.', 'easy-search-filter'));
        }

        update_option('esp_taxonomies', array_values($taxonomies));
        flush_rewrite_rules();
        return true;
    }

    public function get_terms($taxonomy_slug, $parent = 0) {
        return get_terms([
            'taxonomy'   => $taxonomy_slug,
            'parent'     => $parent,
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);
    }

    public function add_term($taxonomy_slug, $name, $slug = '', $parent = 0, $description = '') {
        $result = wp_insert_term(
            sanitize_text_field($name),
            $taxonomy_slug,
            [
                'slug'        => $slug ? sanitize_title($slug) : '',
                'parent'      => absint($parent),
                'description' => sanitize_textarea_field($description),
            ]
        );
        return $result;
    }

    public function update_term($term_id, $taxonomy_slug, $data) {
        $args = [];
        if (isset($data['name'])) {
            $args['name'] = sanitize_text_field($data['name']);
        }
        if (isset($data['slug'])) {
            $args['slug'] = sanitize_title($data['slug']);
        }
        if (isset($data['parent'])) {
            $args['parent'] = absint($data['parent']);
        }
        if (isset($data['description'])) {
            $args['description'] = sanitize_textarea_field($data['description']);
        }

        return wp_update_term($term_id, $taxonomy_slug, $args);
    }

    public function delete_term($term_id, $taxonomy_slug) {
        return wp_delete_term($term_id, $taxonomy_slug);
    }

    public function get_term_hierarchy($taxonomy_slug, $parent = 0, $depth = 0) {
        $terms = $this->get_terms($taxonomy_slug, $parent);
        $result = [];

        foreach ($terms as $term) {
            $item = [
                'term_id'  => $term->term_id,
                'name'     => $term->name,
                'slug'     => $term->slug,
                'depth'    => $depth,
                'children' => [],
            ];

            $children = $this->get_term_hierarchy($taxonomy_slug, $term->term_id, $depth + 1);
            if (!empty($children)) {
                $item['children'] = $children;
            }

            $result[] = $item;
        }

        return $result;
    }

    public function get_terms_tree($taxonomy_slug, $parent = 0, $depth = 0) {
        $terms = $this->get_terms($taxonomy_slug, $parent);
        $result = [];

        foreach ($terms as $term) {
            $term->esp_depth = $depth;
            $result[] = $term;
            $children = $this->get_terms_tree($taxonomy_slug, $term->term_id, $depth + 1);
            $result = array_merge($result, $children);
        }

        return $result;
    }

    public function get_terms_tree_options($taxonomy_slug, $parent = 0, $depth = 0, $exclude_id = 0) {
        $terms = $this->get_terms($taxonomy_slug, $parent);
        $options = [];

        foreach ($terms as $term) {
            if ($term->term_id === $exclude_id) {
                continue;
            }
            $options[] = [
                'term_id' => $term->term_id,
                'name'    => str_repeat('— ', $depth) . $term->name,
                'depth'   => $depth,
            ];
            $children = $this->get_terms_tree_options($taxonomy_slug, $term->term_id, $depth + 1, $exclude_id);
            $options = array_merge($options, $children);
        }

        return $options;
    }

    public function get_available_post_types() {
        $types = get_post_types(['public' => true], 'objects');
        $exclude = ['attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation', 'user_request', 'esp_filter'];
        $options = [];

        foreach ($types as $type) {
            if (in_array($type->name, $exclude, true)) {
                continue;
            }
            $options[$type->name] = $type->label;
        }

        return $options;
    }

    private function register_single_taxonomy($tax) {
        if (taxonomy_exists($tax['slug'])) {
            return;
        }

        register_taxonomy($tax['slug'], $tax['post_types'], [
            'label'              => $tax['name'],
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => false,
            'show_in_menu'       => false,
            'show_in_rest'       => true,
            'hierarchical'       => true,
            'rewrite'            => ['slug' => $tax['slug']],
        ]);
    }
}
