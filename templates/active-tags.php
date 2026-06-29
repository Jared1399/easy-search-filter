<?php
defined('ABSPATH') || exit;

if (empty($group)) return;

$tags = [];
$taxonomy_manager = \EasySearchFilter\Taxonomy_Manager::instance();

foreach ($group->taxonomies as $tax_config) {
    $param_key = 'esp_tax_' . $tax_config['slug'];
    $val = $_GET[$param_key] ?? '';
    if ($val) {
        $term = get_term_by('slug', $val, $tax_config['slug']);
        $label = $tax_config['label'] ?: $tax_config['slug'];
        $tags[] = [
            'label' => $term ? $term->name : $val,
            'remove_url' => remove_query_arg([$param_key, 'esp_paged']),
        ];
    }
}

foreach ($group->meta_fields as $field_config) {
    $key = $field_config['key'];
    $label = $field_config['label'] ?: $key;

    if ($field_config['type'] === 'range') {
        $min = $_GET['esp_meta_' . $key . '_min'] ?? '';
        $max = $_GET['esp_meta_' . $key . '_max'] ?? '';
        if ($min !== '' || $max !== '') {
            $display = ($min ?: '0') . ' – ' . ($max ?: '∞');
            $tags[] = [
                'label' => $label . ': ' . $display,
                'remove_url' => remove_query_arg(['esp_meta_' . $key . '_min', 'esp_meta_' . $key . '_max', 'esp_paged']),
            ];
        }
    } elseif (in_array($field_config['type'], ['select', 'text'])) {
        $val = $_GET['esp_meta_' . $key] ?? '';
        if ($val !== '') {
            $display = $val;
            foreach ($field_config['options'] as $opt) {
                if ($opt['value'] === $val) { $display = $opt['label']; break; }
            }
            $tags[] = [
                'label' => $label . ': ' . $display,
                'remove_url' => remove_query_arg(['esp_meta_' . $key, 'esp_paged']),
            ];
        }
    } elseif ($field_config['type'] === 'checkbox') {
        if (!empty($_GET['esp_meta_' . $key . '_chk'])) {
            $tags[] = [
                'label' => $label,
                'remove_url' => remove_query_arg(['esp_meta_' . $key . '_chk', 'esp_paged']),
            ];
        }
    }
}

if (empty($tags)) return;
?>
<div class="esp-active-tags">
    <span class="esp-active-tags-label"><?php esc_html_e('Filters:', 'easy-search-filter'); ?></span>
    <?php foreach ($tags as $tag): ?>
        <a href="<?php echo esc_url($tag['remove_url']); ?>" class="esp-tag">
            <span class="esp-tag-text"><?php echo esc_html($tag['label']); ?></span>
            <span class="esp-tag-remove">&times;</span>
        </a>
    <?php endforeach; ?>
    <a href="<?php echo esc_url(remove_query_arg(array_merge(
        ['esp_paged', 'esp_orderby'],
        array_map(function($t) { return 'esp_tax_' . $t['slug']; }, $group->taxonomies),
        array_reduce($group->meta_fields, function($c, $f) {
            $c[] = 'esp_meta_' . $f['key'];
            $c[] = 'esp_meta_' . $f['key'] . '_min';
            $c[] = 'esp_meta_' . $f['key'] . '_max';
            $c[] = 'esp_meta_' . $f['key'] . '_chk';
            return $c;
        }, [])
    ))); ?>" class="esp-tag esp-tag-clear"><?php esc_html_e('Clear all', 'easy-search-filter'); ?></a>
</div>
