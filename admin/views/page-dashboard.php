<div class="wrap esp-admin-wrap">
    <div class="esp-admin-header">
        <span class="dashicons dashicons-filter" style="font-size: 32px; width: 32px; height: 32px; color: #2271b1;"></span>
        <h1><?php esc_html_e('Easy Search & Filter', 'easy-search-filter'); ?></h1>
    </div>

    <div class="esp-stats-grid">
        <div class="esp-stat-card">
            <h3><?php esc_html_e('Taxonomies', 'easy-search-filter'); ?></h3>
            <p class="stat-number"><?php echo esc_html($tax_count); ?></p>
        </div>
        <div class="esp-stat-card">
            <h3><?php esc_html_e('Filter Groups', 'easy-search-filter'); ?></h3>
            <p class="stat-number"><?php echo esc_html($group_count); ?></p>
        </div>
        <div class="esp-stat-card">
            <h3><?php esc_html_e('Registered Taxonomies', 'easy-search-filter'); ?></h3>
            <p class="stat-number"><?php echo esc_html(count($registered)); ?></p>
        </div>
    </div>

    <div class="esp-quick-start">
        <h2><?php esc_html_e('Quick Start', 'easy-search-filter'); ?></h2>
        <ol>
            <li>
                <strong><?php esc_html_e('Create Taxonomies', 'easy-search-filter'); ?>:</strong>
                <?php esc_html_e('Go to', 'easy-search-filter'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=esp-taxonomies')); ?>"><?php esc_html_e('Taxonomies', 'easy-search-filter'); ?></a>
                <?php esc_html_e('to create your hierarchical classification system (e.g., Location, Category, Brand).', 'easy-search-filter'); ?>
            </li>
            <li>
                <strong><?php esc_html_e('Add Terms', 'easy-search-filter'); ?>:</strong>
                <?php esc_html_e('Populate each taxonomy with terms. For hierarchical taxonomies, set parent-child relationships.', 'easy-search-filter'); ?>
            </li>
            <li>
                <strong><?php esc_html_e('Create a Filter Group', 'easy-search-filter'); ?>:</strong>
                <?php esc_html_e('Go to', 'easy-search-filter'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=esp-filter-groups')); ?>"><?php esc_html_e('Filter Groups', 'easy-search-filter'); ?></a>
                <?php esc_html_e('to combine taxonomies and meta fields into a search form.', 'easy-search-filter'); ?>
            </li>
            <li>
                <strong><?php esc_html_e('Add to Your Site', 'easy-search-filter'); ?>:</strong>
                <?php esc_html_e('Use the shortcode', 'easy-search-filter'); ?>
                <code>[esp_form id="1"]</code> <?php esc_html_e('for the form and', 'easy-search-filter'); ?>
                <code>[esp_results id="1"]</code> <?php esc_html_e('for results, or use the Gutenberg block.', 'easy-search-filter'); ?>
            </li>
        </ol>
    </div>

    <?php if (!esf_fs()->can_use_premium_code()): ?>
    <div class="notice notice-info is-dismissible">
        <p>
            <?php esc_html_e('You are using the free version. Upgrade to Pro for AJAX search, Gutenberg blocks, unlimited taxonomies and more!', 'easy-search-filter'); ?>
            <a href="<?php echo esc_url(esf_fs()->get_upgrade_url()); ?>" class="button button-primary" style="margin-left: 8px;">
                <?php esc_html_e('Upgrade to Pro', 'easy-search-filter'); ?>
            </a>
        </p>
    </div>
    <?php endif; ?>

    <?php if (!did_action('woocommerce_init')): ?>
    <div class="notice notice-info">
        <p><?php esc_html_e('WooCommerce not detected. The plugin works with any post type, but WooCommerce integration (price filters, product support) is available when WooCommerce is active.', 'easy-search-filter'); ?></p>
    </div>
    <?php endif; ?>
</div>
