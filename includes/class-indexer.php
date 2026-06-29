<?php
namespace EasySearchFilter;

defined('ABSPATH') || exit;

class Indexer {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('save_post', [$this, 'on_save_post'], 10, 3);
        add_action('delete_post', [$this, 'on_delete_post']);
        add_action('created_term', [$this, 'on_term_change'], 10, 3);
        add_action('edited_term', [$this, 'on_term_change'], 10, 3);
        add_action('delete_term', [$this, 'on_term_change'], 10, 3);
        add_action('updated_post_meta', [$this, 'on_meta_update'], 10, 4);
        add_action('added_post_meta', [$this, 'on_meta_update'], 10, 4);
        add_action('deleted_post_meta', [$this, 'on_meta_delete'], 10, 4);
    }

    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'esp_index';
    }

    public static function create_table() {
        global $wpdb;
        $table = self::get_table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table (
            post_id BIGINT UNSIGNED NOT NULL,
            filter_group_id BIGINT UNSIGNED NOT NULL,
            filter_type VARCHAR(20) NOT NULL,
            filter_name VARCHAR(255) NOT NULL,
            filter_value VARCHAR(255) NOT NULL,
            PRIMARY KEY (post_id, filter_group_id, filter_type(20), filter_name(100), filter_value(100)),
            KEY idx_group_filter (filter_group_id, filter_type, filter_name, filter_value)
        ) ENGINE=InnoDB $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public function rebuild_all() {
        global $wpdb;
        $table = self::get_table_name();
        $wpdb->query("TRUNCATE $table");

        $groups = Filter_Group::instance()->get_groups();
        foreach ($groups as $group) {
            $this->build_for_group($group->ID);
        }
    }

    public function build_for_group($group_id) {
        $group = Filter_Group::instance()->get_group($group_id);
        if (!$group) {
            return;
        }

        $posts = get_posts([
            'post_type'      => $group->post_type,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);

        foreach ($posts as $post_id) {
            $this->index_post($group_id, $group, $post_id);
        }
    }

    public function index_post($group_id, $group, $post_id) {
        global $wpdb;
        $table = self::get_table_name();

        $wpdb->delete($table, [
            'post_id'         => $post_id,
            'filter_group_id' => $group_id,
        ]);

        $rows = [];

        foreach ($group->taxonomies as $tax_config) {
            $terms = wp_get_post_terms($post_id, $tax_config['slug']);
            if (is_wp_error($terms) || empty($terms)) {
                continue;
            }
            $seen = [];
            foreach ($terms as $term) {
                $ancestors = get_ancestors($term->term_id, $tax_config['slug']);
                $all_slugs = array_merge([$term->slug], array_map(function($id) use ($tax_config) {
                    $t = get_term($id, $tax_config['slug']);
                    return $t ? $t->slug : '';
                }, $ancestors));

                foreach ($all_slugs as $slug) {
                    if (!$slug || isset($seen[$slug])) continue;
                    $seen[$slug] = true;
                    $rows[] = [
                        'post_id'         => $post_id,
                        'filter_group_id' => $group_id,
                        'filter_type'     => 'tax',
                        'filter_name'     => $tax_config['slug'],
                        'filter_value'    => $slug,
                    ];
                }
            }
        }

        foreach ($group->meta_fields as $field_config) {
            $meta_value = get_post_meta($post_id, $field_config['key'], true);
            if ($meta_value === '' || $meta_value === null) {
                continue;
            }
            $rows[] = [
                'post_id'         => $post_id,
                'filter_group_id' => $group_id,
                'filter_type'     => 'meta',
                'filter_name'     => $field_config['key'],
                'filter_value'    => (string) $meta_value,
            ];
        }

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $wpdb->replace($table, $row);
            }
        }
    }

    public function get_group_counts($group_id, $active_filters = []) {
        global $wpdb;
        $table = self::get_table_name();

        $base_ids = $this->get_matching_post_ids($group_id, $active_filters);
        $id_list = !empty($base_ids) ? implode(',', array_map('intval', $base_ids)) : '0';

        $results = [];

        $group = Filter_Group::instance()->get_group($group_id);

        foreach ($group->taxonomies as $tax_config) {
            $slug = $tax_config['slug'];
            $counts = $wpdb->get_results($wpdb->prepare(
                "SELECT filter_value, COUNT(*) as cnt
                 FROM $table
                 WHERE filter_group_id = %d
                   AND filter_type = 'tax'
                   AND filter_name = %s
                   AND post_id IN ($id_list)
                 GROUP BY filter_value",
                $group_id, $slug
            ));

            $results["tax_$slug"] = [];
            foreach ($counts as $row) {
                $results["tax_$slug"][$row->filter_value] = (int) $row->cnt;
            }
        }

        foreach ($group->meta_fields as $field_config) {
            $key = $field_config['key'];
            if ($field_config['type'] === 'range') {
                continue;
            }

            $counts = $wpdb->get_results($wpdb->prepare(
                "SELECT filter_value, COUNT(*) as cnt
                 FROM $table
                 WHERE filter_group_id = %d
                   AND filter_type = 'meta'
                   AND filter_name = %s
                   AND post_id IN ($id_list)
                 GROUP BY filter_value",
                $group_id, $key
            ));

            $results["meta_$key"] = [];
            foreach ($counts as $row) {
                $results["meta_$key"][$row->filter_value] = (int) $row->cnt;
            }
        }

        return $results;
    }

    public function get_total_count($group_id, $active_filters = []) {
        $ids = $this->get_matching_post_ids($group_id, $active_filters);
        return count($ids);
    }

    private function get_matching_post_ids($group_id, $active_filters = []) {
        global $wpdb;
        $table = self::get_table_name();

        $conditions = [];
        $params = [$group_id];
        $param_placeholders = [];

        foreach ($active_filters as $type => $filters) {
            foreach ($filters as $name => $value) {
                if ($value === '' || $value === null) {
                    continue;
                }
                $conditions[] = "(filter_type = %s AND filter_name = %s AND filter_value = %s)";
                $params[] = $type;
                $params[] = $name;
                $params[] = $value;
            }
        }

        if (empty($conditions)) {
            $all_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT post_id FROM $table WHERE filter_group_id = %d",
                $group_id
            ));
            return array_map('intval', $all_ids);
        }

        $placeholders = implode(' OR ', $conditions);
        $having = "HAVING COUNT(*) = " . count($conditions);

        $sql = "SELECT post_id FROM $table
                WHERE filter_group_id = %d AND ($placeholders)
                GROUP BY post_id $having";

        $ids = $wpdb->get_col($wpdb->prepare($sql, $params));
        return array_map('intval', $ids);
    }

    public function on_save_post($post_id, $post, $update) {
        if ($post->post_status !== 'publish') {
            return;
        }
        $groups = Filter_Group::instance()->get_groups();
        foreach ($groups as $group) {
            if ($group->post_type === $post->post_type) {
                $this->index_post($group->ID, $group, $post_id);
            }
        }
    }

    public function on_delete_post($post_id) {
        global $wpdb;
        $table = self::get_table_name();
        $wpdb->delete($table, ['post_id' => $post_id]);
    }

    public function on_term_change($term_id, $tt_id, $taxonomy) {
        $groups = Filter_Group::instance()->get_groups();
        foreach ($groups as $group) {
            foreach ($group->taxonomies as $tax_config) {
                if ($tax_config['slug'] === $taxonomy) {
                    $posts = get_posts([
                        'post_type'      => $group->post_type,
                        'posts_per_page' => -1,
                        'fields'         => 'ids',
                        'tax_query'      => [[
                            'taxonomy' => $taxonomy,
                            'terms'    => $term_id,
                            'field'    => 'term_id',
                        ]],
                    ]);
                    foreach ($posts as $post_id) {
                        $this->index_post($group->ID, $group, $post_id);
                    }
                }
            }
        }
    }

    public function on_meta_update($meta_id, $post_id, $meta_key, $meta_value) {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        $groups = Filter_Group::instance()->get_groups();
        foreach ($groups as $group) {
            foreach ($group->meta_fields as $field_config) {
                if ($field_config['key'] === $meta_key) {
                    $this->index_post($group->ID, $group, $post_id);
                    return;
                }
            }
        }
    }

    public function on_meta_delete($meta_ids, $post_id, $meta_key, $meta_value) {
        $this->on_meta_update(0, $post_id, $meta_key, $meta_value);
    }
}
