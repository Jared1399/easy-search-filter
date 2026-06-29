<?php
defined('ABSPATH') || exit;

if (empty($group)) {
    return;
}

$taxonomy_manager = $taxonomy_manager ?? Taxonomy_Manager::instance();
$current_url = remove_query_arg('esp_paged');
$has_indexer = !empty($group->display['enable_indexer']) && esf_fs()->can_use_premium_code();
$hide_zero = !empty($group->display['hide_zero_count']);
?>
<div class="esp-search-form" data-group-id="<?php echo esc_attr($group->ID); ?>"
     data-esp-ajax="<?php echo esf_fs()->can_use_premium_code() ? '1' : '0'; ?>"
     data-has-indexer="<?php echo $has_indexer ? '1' : '0'; ?>"
     data-hide-zero="<?php echo $hide_zero ? '1' : '0'; ?>">
    <form class="esp-form" method="get" action="<?php echo esc_url($current_url); ?>">
        <input type="hidden" name="esp_group_id" value="<?php echo esc_attr($group->ID); ?>">
        <input type="hidden" name="esp_nonce" value="<?php echo esc_attr(wp_create_nonce('esp_search_nonce')); ?>">

        <?php foreach ($group->taxonomies as $tax_config):
            $tax_data = $taxonomy_manager->get_taxonomy($tax_config['slug']);
            if (!$tax_data) continue;

            $top_terms = $taxonomy_manager->get_terms($tax_config['slug'], 0);
            $param_key = 'esp_tax_' . $tax_config['slug'];
            $selected_slug = isset($_GET[$param_key]) ? sanitize_text_field($_GET[$param_key]) : '';
            $label = $tax_config['label'] ?: $tax_data['name'];
        ?>
            <div class="esp-field esp-taxonomy-field"
                 data-taxonomy="<?php echo esc_attr($tax_config['slug']); ?>"
                 data-filter-key="tax_<?php echo esc_attr($tax_config['slug']); ?>">
                <span class="esp-field-label"><?php echo esc_html($label); ?></span>
                <input type="hidden" id="<?php echo esc_attr($param_key); ?>" name="<?php echo esc_attr($param_key); ?>" value="<?php echo esc_attr($selected_slug); ?>" class="esp-tax-input">
                <div class="esp-cascading-container" role="group" aria-label="<?php echo esc_attr($label); ?>">
                    <select class="esp-cascade-level" name="<?php echo esc_attr($param_key); ?>_level_0" data-level="0" aria-label="<?php echo esc_attr($label); ?>">
                        <option value="" data-esp-count=""><?php esc_html_e('Select...', 'easy-search-filter'); ?></option>
                        <?php foreach ($top_terms as $term): ?>
                            <option value="<?php echo esc_attr($term->slug); ?>" data-term-id="<?php echo esc_attr($term->term_id); ?>"
                                data-esp-count="" <?php selected($selected_slug, $term->slug); ?>>
                                <?php echo esc_html($term->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="esp-cascade-children"></div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php foreach ($group->meta_fields as $field_config):
            $param_val = 'esp_meta_' . $field_config['key'];
            $param_min = 'esp_meta_' . $field_config['key'] . '_min';
            $param_max = 'esp_meta_' . $field_config['key'] . '_max';
            $param_chk = 'esp_meta_' . $field_config['key'] . '_chk';
            $label = $field_config['label'] ?: $field_config['key'];
        ?>
            <div class="esp-field esp-meta-field"
                 data-key="<?php echo esc_attr($field_config['key']); ?>"
                 data-type="<?php echo esc_attr($field_config['type']); ?>"
                 data-filter-key="meta_<?php echo esc_attr($field_config['key']); ?>">

                <?php if ($field_config['type'] === 'select'): ?>
                    <span class="esp-field-label"><?php echo esc_html($label); ?></span>
                    <select name="<?php echo esc_attr($param_val); ?>">
                        <option value="" data-esp-count=""><?php esc_html_e('Select...', 'easy-search-filter'); ?></option>
                        <?php foreach ($field_config['options'] as $opt): ?>
                            <option value="<?php echo esc_attr($opt['value']); ?>"
                                data-esp-count="" <?php selected($_GET[$param_val] ?? '', $opt['value']); ?>>
                                <?php echo esc_html($opt['label'] ?: $opt['value']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                <?php elseif ($field_config['type'] === 'number'): ?>
                    <span class="esp-field-label"><?php echo esc_html($label); ?></span>
                    <input type="number" name="<?php echo esc_attr($param_val); ?>" value="<?php echo isset($_GET[$param_val]) ? esc_attr($_GET[$param_val]) : ''; ?>" step="any">

                <?php elseif ($field_config['type'] === 'range'): 
                    global $wpdb;
                    $range_min = $range_max = null;
                    $range_key = $field_config['key'];
                    $range_post_type = $group->post_type;
                    $range_min = $wpdb->get_var($wpdb->prepare(
                        "SELECT MIN(CAST(pm.meta_value AS DECIMAL(20,2))) FROM {$wpdb->postmeta} pm
                         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                         WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish'",
                        $range_key, $range_post_type
                    ));
                    $range_max = $wpdb->get_var($wpdb->prepare(
                        "SELECT MAX(CAST(pm.meta_value AS DECIMAL(20,2))) FROM {$wpdb->postmeta} pm
                         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                         WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish'",
                        $range_key, $range_post_type
                    ));
                    $range_min = $range_min !== null ? floor(floatval($range_min)) : 0;
                    $range_max = $range_max !== null ? ceil(floatval($range_max)) : 1000;
                    $range_step = $range_max > 10000 ? 1000 : ($range_max > 1000 ? 100 : ($range_max > 100 ? 10 : 1));
                    $current_min = isset($_GET[$param_min]) ? floatval($_GET[$param_min]) : 0;
                    $current_max = isset($_GET[$param_max]) ? floatval($_GET[$param_max]) : $range_max;
                ?>
                    <div class="esp-field esp-meta-field esp-range-field"
                         data-key="<?php echo esc_attr($field_config['key']); ?>"
                         data-type="range"
                         data-filter-key="range_<?php echo esc_attr($field_config['key']); ?>">
                        <span class="esp-field-label"><?php echo esc_html($label); ?></span>
                        <input type="hidden" name="<?php echo esc_attr($param_min); ?>" value="<?php echo esc_attr($current_min); ?>">
                        <input type="hidden" name="<?php echo esc_attr($param_max); ?>" value="<?php echo esc_attr($current_max); ?>">
                        <div class="esp-range-slider"
                             data-min="0"
                             data-max="<?php echo esc_attr($range_max); ?>"
                             data-start-min="<?php echo esc_attr($current_min); ?>"
                             data-start-max="<?php echo esc_attr($current_max); ?>"
                             data-step="<?php echo esc_attr($range_step); ?>"></div>
                        <div class="esp-range-values">
                            <span class="esp-range-val-min"><?php echo esc_html(number_format($current_min)); ?></span>
                            <span class="esp-range-sep">—</span>
                            <span class="esp-range-val-max"><?php echo esc_html(number_format($current_max)); ?></span>
                        </div>
                    </div>

                <?php elseif ($field_config['type'] === 'checkbox'): ?>
                    <label>
                        <input type="checkbox" name="<?php echo esc_attr($param_chk); ?>" value="1" <?php checked(!empty($_GET[$param_chk])); ?>>
                        <?php echo esc_html($label); ?>
                    </label>

                <?php else: ?>
                    <span class="esp-field-label"><?php echo esc_html($label); ?></span>
                    <input type="text" name="<?php echo esc_attr($param_val); ?>" value="<?php echo isset($_GET[$param_val]) ? esc_attr($_GET[$param_val]) : ''; ?>">
                <?php endif; ?>

            </div>
        <?php endforeach; ?>

        <div class="esp-form-actions">
            <button type="submit" class="esp-search-btn">
                <?php esc_html_e('Search', 'easy-search-filter'); ?>
            </button>
            <a href="<?php echo esc_url(remove_query_arg(array_merge(['esp_group_id', 'esp_paged', 'esp_orderby'], array_map(function($t) { return 'esp_tax_' . $t['slug']; }, $group->taxonomies), array_reduce($group->meta_fields, function($carry, $f) {
                $carry[] = 'esp_meta_' . $f['key'];
                $carry[] = 'esp_meta_' . $f['key'] . '_min';
                $carry[] = 'esp_meta_' . $f['key'] . '_max';
                $carry[] = 'esp_meta_' . $f['key'] . '_chk';
                return $carry;
            }, [])))); ?>" class="esp-clear-btn">
                <?php esc_html_e('Clear', 'easy-search-filter'); ?>
            </a>
        </div>
    </form>
</div>
