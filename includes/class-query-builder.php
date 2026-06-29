<?php
namespace EasySearchFilter;

defined('ABSPATH') || exit;

class Query_Builder {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    public function build_query($group_id, $params = []) {
        $group = Filter_Group::instance()->get_group($group_id);
        if (!$group) {
            return new \WP_Error('invalid_group', __('Filter group not found.', 'easy-search-filter'));
        }

        $args = [
            'post_type'      => $group->post_type,
            'post_status'    => 'publish',
            'posts_per_page' => $group->display['per_page'] ?? 12,
            'orderby'        => $group->display['orderby'] ?? 'date',
            'order'          => $group->display['order'] ?? 'DESC',
        ];

        $meta_query = ['relation' => 'AND'];
        $tax_query = ['relation' => 'AND'];

        $this->apply_search($group, $params, $args);
        $this->apply_taxonomy_filters($group, $params, $tax_query);
        $this->apply_meta_filters($group, $params, $meta_query);

        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }
        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }

        if (!empty($params['esp_paged'])) {
            $args['paged'] = absint($params['esp_paged']);
        }

        $this->apply_sorting($group, $params, $args);

        return $args;
    }

    private function apply_sorting($group, $params, &$args) {
        if (empty($params['esp_orderby'])) {
            return;
        }

        $sort_map = [
            'date-desc'  => ['orderby' => 'date', 'order' => 'DESC'],
            'date-asc'   => ['orderby' => 'date', 'order' => 'ASC'],
            'title-asc'  => ['orderby' => 'title', 'order' => 'ASC'],
            'title-desc' => ['orderby' => 'title', 'order' => 'DESC'],
        ];

        $sort_key = sanitize_text_field($params['esp_orderby']);

        if (isset($sort_map[$sort_key])) {
            $args['orderby'] = $sort_map[$sort_key]['orderby'];
            $args['order'] = $sort_map[$sort_key]['order'];
            return;
        }

        if (strpos($sort_key, 'meta_') === 0) {
            $parts = explode('_', $sort_key, 3);
            if (count($parts) === 3) {
                $meta_key = $parts[1];
                $dir = strtoupper($parts[2]);
                if (in_array($dir, ['ASC', 'DESC'])) {
                    $args['orderby'] = 'meta_value_num';
                    $args['meta_key'] = $meta_key;
                    $args['order'] = $dir;
                }
            }
        }
    }

    private function apply_search($group, $params, &$args) {
        if (!empty($group->display['enable_search']) && !empty($params['esp_search'])) {
            $args['s'] = sanitize_text_field($params['esp_search']);
        }
    }

    private function apply_taxonomy_filters($group, $params, &$tax_query) {
        foreach ($group->taxonomies as $tax_config) {
            $slug = $tax_config['slug'];
            $param_key = 'esp_tax_' . $slug;

            if (empty($params[$param_key])) {
                continue;
            }

            $term_slug = sanitize_text_field($params[$param_key]);

            $tax_query[] = [
                'taxonomy' => $slug,
                'field'    => 'slug',
                'terms'    => $term_slug,
                'include_children' => true,
            ];
        }
    }

    private function apply_meta_filters($group, $params, &$meta_query) {
        foreach ($group->meta_fields as $field_config) {
            $key = $field_config['key'];
            $param_min = 'esp_meta_' . $key . '_min';
            $param_max = 'esp_meta_' . $key . '_max';
            $param_val = 'esp_meta_' . $key;
            $param_chk = 'esp_meta_' . $key . '_chk';
            $compare = $field_config['compare'] ?? '=';
            $type = $field_config['type'] ?? 'text';

            switch ($type) {
                case 'range':
                    $min = isset($params[$param_min]) && $params[$param_min] !== '' ? floatval($params[$param_min]) : null;
                    $max = isset($params[$param_max]) && $params[$param_max] !== '' ? floatval($params[$param_max]) : null;

                    if ($min !== null && $max !== null) {
                        $meta_query[] = [
                            'key'     => $key,
                            'value'   => [$min, $max],
                            'compare' => 'BETWEEN',
                            'type'    => 'NUMERIC',
                        ];
                    } elseif ($min !== null) {
                        $meta_query[] = [
                            'key'     => $key,
                            'value'   => $min,
                            'compare' => '>=',
                            'type'    => 'NUMERIC',
                        ];
                    } elseif ($max !== null) {
                        $meta_query[] = [
                            'key'     => $key,
                            'value'   => $max,
                            'compare' => '<=',
                            'type'    => 'NUMERIC',
                        ];
                    }
                    break;

                case 'number':
                    if (isset($params[$param_val]) && $params[$param_val] !== '') {
                        $val = floatval($params[$param_val]);
                        $meta_query[] = [
                            'key'     => $key,
                            'value'   => $val,
                            'compare' => $compare,
                            'type'    => 'NUMERIC',
                        ];
                    }
                    break;

                case 'checkbox':
                    if (!empty($params[$param_chk])) {
                        $meta_query[] = [
                            'key'     => $key,
                            'compare' => $compare,
                            'value'   => '1',
                        ];
                    }
                    break;

                case 'select':
                case 'text':
                default:
                    if (isset($params[$param_val]) && $params[$param_val] !== '') {
                        $val = sanitize_text_field($params[$param_val]);

                        if ($compare === 'BETWEEN') {
                            $parts = explode(',', $val);
                            if (count($parts) === 2) {
                                $meta_query[] = [
                                    'key'     => $key,
                                    'value'   => [trim($parts[0]), trim($parts[1])],
                                    'compare' => 'BETWEEN',
                                    'type'    => 'NUMERIC',
                                ];
                            }
                        } else {
                            $meta_query[] = [
                                'key'     => $key,
                                'value'   => $val,
                                'compare' => $compare,
                            ];
                        }
                    }
                    break;
            }
        }
    }
}
