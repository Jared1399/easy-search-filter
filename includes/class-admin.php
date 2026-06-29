<?php
namespace EasySearchFilter;

defined('ABSPATH') || exit;

class Admin {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_post_esp_save_taxonomy', [$this, 'handle_save_taxonomy']);
        add_action('admin_post_esp_delete_taxonomy', [$this, 'handle_delete_taxonomy']);
        add_action('admin_post_esp_add_term', [$this, 'handle_add_term']);
        add_action('admin_post_esp_edit_term', [$this, 'handle_edit_term']);
        add_action('admin_post_esp_delete_term', [$this, 'handle_delete_term']);
        add_action('admin_post_esp_save_filter_group', [$this, 'handle_save_filter_group']);
        add_action('admin_post_esp_delete_filter_group', [$this, 'handle_delete_filter_group']);
        add_action('wp_ajax_esp_detect_meta_values', [$this, 'ajax_detect_meta_values']);
    }

    public function register_menu() {
        add_menu_page(
            __('Easy Search & Filter', 'easy-search-filter'),
            __('Easy Search & Filter', 'easy-search-filter'),
            'manage_options',
            'easy-search-filter',
            [$this, 'render_dashboard'],
            'dashicons-filter',
            30
        );

        add_submenu_page(
            'easy-search-filter',
            __('Dashboard', 'easy-search-filter'),
            __('Dashboard', 'easy-search-filter'),
            'manage_options',
            'easy-search-filter',
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            'easy-search-filter',
            __('Taxonomies', 'easy-search-filter'),
            __('Taxonomies', 'easy-search-filter'),
            'manage_options',
            'esp-taxonomies',
            [$this, 'render_taxonomies']
        );

        add_submenu_page(
            'easy-search-filter',
            __('Filter Groups', 'easy-search-filter'),
            __('Filter Groups', 'easy-search-filter'),
            'manage_options',
            'esp-filter-groups',
            [$this, 'render_filter_groups']
        );

        add_submenu_page(
            'easy-search-filter',
            __('Settings', 'easy-search-filter'),
            __('Settings', 'easy-search-filter'),
            'manage_options',
            'esp-settings',
            [$this, 'render_settings']
        );
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, 'easy-search-filter') === false && strpos($hook, 'esp-') === false) {
            return;
        }

        wp_enqueue_style('esp-admin', ESP_URL . 'admin/css/admin.css', [], ESP_VERSION);
        wp_enqueue_script('esp-admin', ESP_URL . 'admin/js/admin.js', ['jquery'], ESP_VERSION, true);

        wp_localize_script('esp-admin', 'espAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('esp_admin_nonce'),
        ]);
    }

    public function render_dashboard() {
        $tax_count = count(Taxonomy_Manager::instance()->get_taxonomies());
        $group_count = wp_count_posts('esp_filter');
        $group_count = ($group_count->publish ?? 0) + ($group_count->draft ?? 0);
        $registered = get_option('esp_taxonomies', []);

        include ESP_PATH . 'admin/views/page-dashboard.php';
    }

    public function render_taxonomies() {
        $action = $_GET['action'] ?? 'list';
        $taxonomy_manager = Taxonomy_Manager::instance();

        if ($action === 'edit' || $action === 'add') {
            $tax_slug = sanitize_text_field($_GET['taxonomy'] ?? '');
            $tax = $tax_slug ? $taxonomy_manager->get_taxonomy($tax_slug) : null;
            $post_types = $taxonomy_manager->get_available_post_types();
            include ESP_PATH . 'admin/views/page-edit-taxonomy.php';
        } elseif ($action === 'terms') {
            $tax_slug = sanitize_text_field($_GET['taxonomy'] ?? '');
            $tax = $taxonomy_manager->get_taxonomy($tax_slug);
            $terms = $tax ? $taxonomy_manager->get_terms($tax_slug, 0) : [];
            $tree_terms = $tax ? $taxonomy_manager->get_terms_tree($tax_slug) : [];
            $tree_options = $tax ? $taxonomy_manager->get_terms_tree_options($tax_slug) : [];
            include ESP_PATH . 'admin/views/page-terms.php';
        } else {
            $taxonomies = $taxonomy_manager->get_taxonomies();
            include ESP_PATH . 'admin/views/page-taxonomies.php';
        }
    }

    public function render_filter_groups() {
        $action = $_GET['action'] ?? 'list';

        if ($action === 'edit' || $action === 'add') {
            $group_id = absint($_GET['group'] ?? 0);
            $group = $group_id ? Filter_Group::instance()->get_group($group_id) : null;
            $taxonomy_manager = Taxonomy_Manager::instance();
            $available_taxonomies = $taxonomy_manager->get_taxonomies();
            $post_types = $taxonomy_manager->get_available_post_types();
            include ESP_PATH . 'admin/views/page-edit-group.php';
        } else {
            $groups = Filter_Group::instance()->get_groups();
            include ESP_PATH . 'admin/views/page-filter-groups.php';
        }
    }

    public function register_settings() {
        register_setting('esp_settings', 'esp_settings', [
            'sanitize_callback' => function($input) {
                return [
                    'enable_auto_detect' => !empty($input['enable_auto_detect']),
                    'default_per_page'   => absint($input['default_per_page'] ?? 12),
                    'default_columns'    => absint($input['default_columns'] ?? 3),
                ];
            },
        ]);
    }

    public function render_settings() {
        $settings = get_option('esp_settings', []);
        include ESP_PATH . 'admin/views/page-settings.php';
    }

    public function handle_save_taxonomy() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'esp_save_taxonomy')) {
            wp_die(__('Security check failed.', 'easy-search-filter'));
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'easy-search-filter'));
        }

        $result = Taxonomy_Manager::instance()->save_taxonomy($_POST);

        if (is_wp_error($result)) {
            wp_redirect(add_query_arg([
                'page' => 'esp-taxonomies',
                'action' => 'edit',
                'taxonomy' => sanitize_text_field($_POST['original_slug'] ?? $_POST['slug'] ?? ''),
                'error' => urlencode($result->get_error_message()),
            ], admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg([
                'page' => 'esp-taxonomies',
                'message' => 'saved',
            ], admin_url('admin.php')));
        }
        exit;
    }

    public function handle_delete_taxonomy() {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'esp_delete_taxonomy')) {
            wp_die(__('Security check failed.', 'easy-search-filter'));
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'easy-search-filter'));
        }

        $slug = sanitize_text_field($_GET['taxonomy'] ?? '');
        Taxonomy_Manager::instance()->delete_taxonomy($slug);

        wp_redirect(add_query_arg([
            'page' => 'esp-taxonomies',
            'message' => 'deleted',
        ], admin_url('admin.php')));
        exit;
    }

    public function handle_add_term() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'esp_add_term')) {
            wp_die(__('Security check failed.', 'easy-search-filter'));
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'easy-search-filter'));
        }

        $tax_slug = sanitize_text_field($_POST['taxonomy'] ?? '');
        $taxonomy_manager = Taxonomy_Manager::instance();
        $taxonomy_manager->add_term(
            $tax_slug,
            sanitize_text_field($_POST['name']),
            sanitize_title($_POST['slug'] ?? ''),
            absint($_POST['parent'] ?? 0),
            sanitize_textarea_field($_POST['description'] ?? '')
        );

        wp_redirect(add_query_arg([
            'page' => 'esp-taxonomies',
            'action' => 'terms',
            'taxonomy' => $tax_slug,
            'message' => 'term_added',
        ], admin_url('admin.php')));
        exit;
    }

    public function handle_edit_term() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'esp_edit_term')) {
            wp_die(__('Security check failed.', 'easy-search-filter'));
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'easy-search-filter'));
        }

        $tax_slug = sanitize_text_field($_POST['taxonomy'] ?? '');
        $term_id = absint($_POST['term_id']);
        $taxonomy_manager = Taxonomy_Manager::instance();

        $taxonomy_manager->update_term($term_id, $tax_slug, [
            'name'        => sanitize_text_field($_POST['name']),
            'slug'        => sanitize_title($_POST['slug'] ?? ''),
            'parent'      => absint($_POST['parent'] ?? 0),
            'description' => sanitize_textarea_field($_POST['description'] ?? ''),
        ]);

        wp_redirect(add_query_arg([
            'page' => 'esp-taxonomies',
            'action' => 'terms',
            'taxonomy' => $tax_slug,
            'message' => 'term_updated',
        ], admin_url('admin.php')));
        exit;
    }

    public function handle_delete_term() {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'esp_delete_term')) {
            wp_die(__('Security check failed.', 'easy-search-filter'));
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'easy-search-filter'));
        }

        $tax_slug = sanitize_text_field($_GET['taxonomy'] ?? '');
        $term_id = absint($_GET['term_id'] ?? 0);

        Taxonomy_Manager::instance()->delete_term($term_id, $tax_slug);

        wp_redirect(add_query_arg([
            'page' => 'esp-taxonomies',
            'action' => 'terms',
            'taxonomy' => $tax_slug,
            'message' => 'term_deleted',
        ], admin_url('admin.php')));
        exit;
    }

    public function handle_save_filter_group() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'esp_save_filter_group')) {
            wp_die(__('Security check failed.', 'easy-search-filter'));
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'easy-search-filter'));
        }

        $group_id = absint($_POST['group_id'] ?? 0);
        $data = $this->parse_filter_group_data($_POST);

        $result = Filter_Group::instance()->save_group($data, $group_id);

        if (is_wp_error($result)) {
            $redirect_args = [
                'page' => 'esp-filter-groups',
                'action' => 'edit',
                'group' => $group_id,
                'error' => urlencode($result->get_error_message()),
            ];
        } else {
            $redirect_args = [
                'page' => 'esp-filter-groups',
                'message' => 'saved',
            ];
            if (!$group_id) {
                $redirect_args['group'] = $result;
            }
        }

        wp_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
        exit;
    }

    public function handle_delete_filter_group() {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'esp_delete_filter_group')) {
            wp_die(__('Security check failed.', 'easy-search-filter'));
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'easy-search-filter'));
        }

        $group_id = absint($_GET['group'] ?? 0);
        Filter_Group::instance()->delete_group($group_id);

        wp_redirect(add_query_arg([
            'page' => 'esp-filter-groups',
            'message' => 'deleted',
        ], admin_url('admin.php')));
        exit;
    }

    public function ajax_detect_meta_values() {
        check_ajax_referer('esp_admin_nonce', 'nonce');

        $meta_key = sanitize_text_field($_POST['meta_key'] ?? '');
        $post_type = sanitize_text_field($_POST['post_type'] ?? '');

        if (!$meta_key || !$post_type) {
            wp_send_json_error(['message' => __('Missing meta key or post type.', 'easy-search-filter')]);
        }

        global $wpdb;

        $values = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT pm.meta_value
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = %s
             AND p.post_type = %s
             AND p.post_status = 'publish'
             AND pm.meta_value != ''
             ORDER BY pm.meta_value ASC
             LIMIT 200",
            $meta_key,
            $post_type
        ));

        $options = [];
        foreach ($values as $value) {
            $options[] = [
                'value' => $value,
                'label' => $value,
            ];
        }

        wp_send_json_success(['options' => $options]);
    }

    private function parse_filter_group_data($post) {
        $taxonomies = [];
        if (!empty($post['taxonomies']) && is_array($post['taxonomies'])) {
            foreach ($post['taxonomies'] as $i => $tax) {
                if (!empty($tax['slug'])) {
                    $taxonomies[] = [
                        'slug'  => sanitize_text_field($tax['slug']),
                        'label' => sanitize_text_field($tax['label'] ?: $tax['slug']),
                        'order' => absint($tax['order'] ?? $i),
                    ];
                }
            }
        }

        $meta_fields = [];
        if (!empty($post['meta_fields']) && is_array($post['meta_fields'])) {
            foreach ($post['meta_fields'] as $i => $field) {
                if (!empty($field['key'])) {
                    $options = [];
                    if (!empty($field['options']) && is_array($field['options'])) {
                        foreach ($field['options'] as $opt) {
                            if (isset($opt['value'])) {
                                $options[] = [
                                    'value' => sanitize_text_field($opt['value']),
                                    'label' => sanitize_text_field($opt['label'] ?: $opt['value']),
                                ];
                            }
                        }
                    }

                    $meta_fields[] = [
                        'key'          => sanitize_text_field($field['key']),
                        'label'        => sanitize_text_field($field['label'] ?: $field['key']),
                        'type'         => in_array($field['type'] ?? '', ['select', 'number', 'text', 'range', 'checkbox']) ? $field['type'] : 'text',
                        'compare'      => sanitize_text_field($field['compare'] ?? '='),
                        'options'      => $options,
                        'auto_detect'  => !empty($field['auto_detect']),
                        'order'        => absint($field['order'] ?? $i),
                    ];
                }
            }
        }

        return [
            'name'       => sanitize_text_field($post['name'] ?? ''),
            'post_type'  => sanitize_text_field($post['post_type'] ?? 'post'),
            'taxonomies' => $taxonomies,
            'meta_fields' => $meta_fields,
            'display'    => [
                'layout'          => in_array($post['display_layout'] ?? '', ['grid', 'list']) ? $post['display_layout'] : 'grid',
                'columns'         => absint($post['display_columns'] ?? 3),
                'per_page'        => absint($post['display_per_page'] ?? 12),
                'orderby'         => sanitize_text_field($post['display_orderby'] ?? 'date'),
                'order'           => in_array($post['display_order'] ?? '', ['ASC', 'DESC']) ? $post['display_order'] : 'DESC',
                'enable_indexer'  => !empty($post['enable_indexer']),
                'hide_zero_count' => !empty($post['hide_zero_count']),
                'sort_options'    => !empty($post['sort_options']) ? array_map('sanitize_text_field', (array) $post['sort_options']) : ['date-desc', 'date-asc'],
            ],
        ];
    }
}
