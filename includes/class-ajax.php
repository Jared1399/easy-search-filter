<?php
namespace EasySearchFilter;

defined('ABSPATH') || exit;

class Ajax {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_esp_get_child_terms', [$this, 'get_child_terms']);
        add_action('wp_ajax_nopriv_esp_get_child_terms', [$this, 'get_child_terms']);

        if (esf_fs()->can_use_premium_code__premium_only()) {
            add_action('wp_ajax_esp_search', [$this, 'search_results']);
            add_action('wp_ajax_nopriv_esp_search', [$this, 'search_results']);
            add_action('wp_ajax_esp_indexer_counts', [$this, 'indexer_counts']);
            add_action('wp_ajax_nopriv_esp_indexer_counts', [$this, 'indexer_counts']);
            add_action('wp_ajax_esp_rebuild_index', [$this, 'rebuild_index']);
        }
    }

    public function get_child_terms() {
        $taxonomy = sanitize_text_field($_GET['taxonomy'] ?? '');
        $parent = absint($_GET['parent'] ?? 0);

        if (!$taxonomy) {
            wp_send_json_error(['message' => __('Taxonomy is required.', 'easy-search-filter')]);
        }

        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'parent'     => $parent,
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);

        if (is_wp_error($terms)) {
            wp_send_json_error(['message' => $terms->get_error_message()]);
        }

        $result = [];
        foreach ($terms as $term) {
            $children = get_terms([
                'taxonomy'   => $taxonomy,
                'parent'     => $term->term_id,
                'hide_empty' => false,
                'fields'     => 'ids',
            ]);

            $result[] = [
                'term_id'       => $term->term_id,
                'name'          => $term->name,
                'slug'          => $term->slug,
                'has_children'  => !empty($children) && !is_wp_error($children),
                'depth'         => $this->get_term_depth($term->term_id, $taxonomy),
            ];
        }

        wp_send_json_success($result);
    }

    public function search_results() {
        if (!wp_verify_nonce($_POST['esp_nonce'] ?? '', 'esp_search_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'easy-search-filter')]);
        }

        $group_id = absint($_POST['esp_group_id'] ?? 0);

        if (!$group_id) {
            wp_send_json_error(['message' => __('Invalid filter group.', 'easy-search-filter')]);
        }

        $params = $_POST;
        $query_args = Query_Builder::instance()->build_query($group_id, $params);

        if (is_wp_error($query_args)) {
            wp_send_json_error(['message' => $query_args->get_error_message()]);
        }

        $query = new \WP_Query($query_args);
        $group = Filter_Group::instance()->get_group($group_id);

        $pag_url = sanitize_text_field($_POST['esp_pag_url'] ?? '');
        if (!$pag_url) {
            $pag_url = add_query_arg('esp_paged', '%#%');
        }
        $html = Template::instance()->render_string('search-results.php', [
            'group'     => $group,
            'query'     => $query,
            'args'      => $query_args,
            'pag_url'   => $pag_url,
        ]);

        wp_send_json_success([
            'html'      => $html,
            'total'     => $query->found_posts,
            'page'      => absint($params['esp_paged'] ?? 1),
            'max_page'  => $query->max_num_pages,
        ]);
    }

    public function indexer_counts() {
        $group_id = absint($_POST['esp_group_id'] ?? 0);
        if (!$group_id) {
            wp_send_json_error(['message' => __('Invalid filter group.', 'easy-search-filter')]);
        }

        $active_filters = [
            'tax'  => [],
            'meta' => [],
        ];

        $group = \EasySearchFilter\Filter_Group::instance()->get_group($group_id);
        if (!$group) {
            wp_send_json_error(['message' => __('Filter group not found.', 'easy-search-filter')]);
        }

        foreach ($group->taxonomies as $tax_config) {
            $slug = $tax_config['slug'];
            $val = sanitize_text_field($_POST['esp_tax_' . $slug] ?? '');
            if ($val) {
                $active_filters['tax'][$slug] = $val;
            }
        }

        foreach ($group->meta_fields as $field_config) {
            $key = $field_config['key'];
            if ($field_config['type'] === 'checkbox') {
                $chk = sanitize_text_field($_POST['esp_meta_' . $key . '_chk'] ?? '');
                if ($chk) {
                    $active_filters['meta'][$key] = '1';
                }
            } else {
                $val = sanitize_text_field($_POST['esp_meta_' . $key] ?? '');
                if ($val) {
                    $active_filters['meta'][$key] = $val;
                }
            }
        }

        $search_val = sanitize_text_field($_POST['esp_search'] ?? '');
        if ($search_val) {
            $active_filters['search'] = $search_val;
        }

        $indexer = \EasySearchFilter\Indexer::instance();
        $counts = $indexer->get_group_counts($group_id, $active_filters);
        $total = $indexer->get_total_count($group_id, $active_filters);

        wp_send_json_success([
            'counts' => $counts,
            'total'  => $total,
        ]);
    }

    public function rebuild_index() {
        check_ajax_referer('esp_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'easy-search-filter')]);
        }

        \EasySearchFilter\Indexer::instance()->rebuild_all();
        wp_send_json_success(['message' => __('Index rebuilt successfully.', 'easy-search-filter')]);
    }

    private function get_term_depth($term_id, $taxonomy) {
        $ancestors = get_ancestors($term_id, $taxonomy);
        return count($ancestors);
    }
}
