<?php
namespace EasySearchFilter;

defined('ABSPATH') || exit;

class Shortcodes {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode('esp_form', [$this, 'render_form']);
        add_shortcode('esp_results', [$this, 'render_results']);
        add_shortcode('esp_search', [$this, 'render_search']);
    }

    public function render_form($atts) {
        $atts = shortcode_atts(['id' => 0], $atts);
        $group_id = absint($atts['id']);

        if (!$group_id) {
            return '<p>' . esc_html__('Invalid filter group ID.', 'easy-search-filter') . '</p>';
        }

        $group = Filter_Group::instance()->get_group($group_id);
        if (!$group) {
            return '<p>' . esc_html__('Filter group not found.', 'easy-search-filter') . '</p>';
        }

        $data = [
            'group'  => $group,
            'taxonomy_manager' => Taxonomy_Manager::instance(),
        ];

        return Template::instance()->render_string('search-form.php', $data);
    }

    public function render_results($atts) {
        $atts = shortcode_atts(['id' => 0], $atts);
        $group_id = absint($atts['id']);

        if (!$group_id) {
            return '<p>' . esc_html__('Invalid filter group ID.', 'easy-search-filter') . '</p>';
        }

        $group = Filter_Group::instance()->get_group($group_id);
        if (!$group) {
            return '<p>' . esc_html__('Filter group not found.', 'easy-search-filter') . '</p>';
        }

        $params = array_merge($_GET, $_POST);
        $query_args = Query_Builder::instance()->build_query($group_id, $params);

        if (is_wp_error($query_args)) {
            return '<p>' . esc_html($query_args->get_error_message()) . '</p>';
        }

        $query = new \WP_Query($query_args);

        $data = [
            'group' => $group,
            'query' => $query,
            'args'  => $query_args,
        ];

        return Template::instance()->render_string('search-results.php', $data);
    }

    public function render_search($atts) {
        return $this->render_form($atts) . $this->render_results($atts);
    }
}
