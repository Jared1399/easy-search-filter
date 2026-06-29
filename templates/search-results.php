<?php
defined('ABSPATH') || exit;

if (empty($group) || empty($query)) {
    return;
}

$has_sort = !empty($group->display['sort_options']) && esf_fs()->can_use_premium_code();
$current_orderby = sanitize_text_field($_GET['esp_orderby'] ?? '');
$sort_labels = [
    'date-desc'  => __('Newest First', 'easy-search-filter'),
    'date-asc'   => __('Oldest First', 'easy-search-filter'),
    'title-asc'  => __('Title A-Z', 'easy-search-filter'),
    'title-desc' => __('Title Z-A', 'easy-search-filter'),
];
?>
<div class="esp-results" data-group-id="<?php echo esc_attr($group->ID); ?>">
    <div class="esp-results-header">
        <div class="esp-results-count">
            <?php printf(
                esc_html(_n('%d result found', '%d results found', $query->found_posts, 'easy-search-filter')),
                $query->found_posts
            ); ?>
        </div>

        <?php if ($has_sort): ?>
            <div class="esp-sort-control">
                <label for="esp-sort-select"><?php esc_html_e('Sort:', 'easy-search-filter'); ?></label>
                <select id="esp-sort-select" name="esp_orderby" class="esp-sort-select">
                    <option value=""><?php esc_html_e('Default', 'easy-search-filter'); ?></option>
                    <?php foreach ($group->display['sort_options'] as $sort_key): ?>
                        <?php
                        $label = $sort_labels[$sort_key] ?? $sort_key;
                        if (strpos($sort_key, 'meta_') === 0) {
                            $parts = explode('_', $sort_key, 3);
                            if (count($parts) === 3) {
                                $meta_label = '';
                                foreach ($group->meta_fields as $f) {
                                    if ($f['key'] === $parts[1]) {
                                        $meta_label = $f['label'] . ' ' . ($parts[2] === 'asc' ? '↑' : '↓');
                                        break;
                                    }
                                }
                                $label = $meta_label ?: $label;
                            }
                        }
                        ?>
                        <option value="<?php echo esc_attr($sort_key); ?>" <?php selected($current_orderby, $sort_key); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($query->have_posts()): ?>

        <div class="esp-results-grid esp-layout-<?php echo esc_attr($group->display['layout'] ?? 'grid'); ?>"
             style="--esp-columns: <?php echo esc_attr($group->display['columns'] ?? 3); ?>">

            <?php while ($query->have_posts()): $query->the_post(); ?>
                <article class="esp-result-item">
                    <?php if (has_post_thumbnail()): ?>
                        <div class="esp-result-thumb">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium'); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="esp-result-body">
                        <h3 class="esp-result-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <div class="esp-result-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                        <a href="<?php the_permalink(); ?>" class="esp-result-link">
                            <?php esc_html_e('View details', 'easy-search-filter'); ?>
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <?php if ($query->max_num_pages > 1): ?>
            <div class="esp-pagination">
                <?php
                $current = absint($args['paged'] ?? 1);
                if (empty($pag_url)) {
                    $pag_url = add_query_arg('esp_paged', '%#%');
                }
                echo paginate_links([
                    'base'      => $pag_url,
                    'format'    => '',
                    'current'   => $current,
                    'total'     => $query->max_num_pages,
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                ]);
                ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <p class="esp-no-results">
            <?php esc_html_e('No results found.', 'easy-search-filter'); ?>
        </p>
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>
</div>
