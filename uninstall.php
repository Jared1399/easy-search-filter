<?php
defined('WP_UNINSTALL_PLUGIN') || exit;

$settings = get_option('esp_settings');

delete_option('esp_settings');
delete_option('esp_taxonomies');
delete_option('esp_version');

$groups = get_posts([
    'post_type'      => 'esp_filter',
    'posts_per_page' => -1,
    'post_status'    => 'any',
]);

foreach ($groups as $group) {
    wp_delete_post($group->ID, true);
}

$registered_taxonomies = get_option('esp_taxonomies', []);
if (!empty($registered_taxonomies)) {
    foreach ($registered_taxonomies as $tax) {
        $terms = get_terms(['taxonomy' => $tax['slug'], 'hide_empty' => false, 'fields' => 'ids']);
        foreach ($terms as $term_id) {
            wp_delete_term($term_id, $tax['slug']);
        }
    }
}

flush_rewrite_rules();
