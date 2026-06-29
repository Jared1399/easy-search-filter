<div class="wrap esp-admin-wrap">
    <div class="esp-admin-header">
        <span class="dashicons dashicons-layout" style="font-size: 32px; width: 32px; height: 32px; color: #2271b1;"></span>
        <h1><?php echo $group ? esc_html__('Edit Filter Group', 'easy-search-filter') : esc_html__('Add New Filter Group', 'easy-search-filter'); ?></h1>
        <a href="<?php echo esc_url(admin_url('admin.php?page=esp-filter-groups')); ?>" class="page-title-action"><?php esc_html_e('Back to List', 'easy-search-filter'); ?></a>
    </div>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('esp_save_filter_group'); ?>
        <input type="hidden" name="action" value="esp_save_filter_group">
        <input type="hidden" name="group_id" value="<?php echo $group ? esc_attr($group->ID) : '0'; ?>">

        <div id="esp-editor-tabs" style="margin-top: 16px;">
            <h2 class="nav-tab-wrapper">
                <a href="#esp-tab-general" class="nav-tab nav-tab-active"><?php esc_html_e('General', 'easy-search-filter'); ?></a>
                <a href="#esp-tab-taxonomies" class="nav-tab"><?php esc_html_e('Taxonomies', 'easy-search-filter'); ?></a>
                <a href="#esp-tab-meta" class="nav-tab"><?php esc_html_e('Meta Fields', 'easy-search-filter'); ?></a>
                <a href="#esp-tab-display" class="nav-tab"><?php esc_html_e('Display', 'easy-search-filter'); ?></a>
            </h2>

            <div id="esp-tab-general" class="esp-tab-content" style="margin-top: 16px;">
                <table class="form-table esp-form-table">
                    <tr>
                        <th><label for="esp-group-name"><?php esc_html_e('Group Name', 'easy-search-filter'); ?></label></th>
                        <td>
                            <input type="text" id="esp-group-name" name="name" value="<?php echo $group ? esc_attr($group->name) : ''; ?>" required class="regular-text">
                            <p class="description"><?php esc_html_e('A descriptive name for this filter group (internal use).', 'easy-search-filter'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="esp-group-post-type"><?php esc_html_e('Target Post Type', 'easy-search-filter'); ?></label></th>
                        <td>
                            <select id="esp-group-post-type" name="post_type" required>
                                <?php foreach ($post_types as $pt_slug => $pt_label): ?>
                                    <option value="<?php echo esc_attr($pt_slug); ?>" <?php selected($group ? $group->post_type : 'post', $pt_slug); ?>>
                                        <?php echo esc_html($pt_label); ?> (<?php echo esc_html($pt_slug); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html_e('The post type that this filter group will search.', 'easy-search-filter'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <div id="esp-tab-taxonomies" class="esp-tab-content" style="margin-top: 16px; display: none;">
                <p><?php esc_html_e('Select which taxonomies to include as filters. Order determines the cascading hierarchy (N-level).', 'easy-search-filter'); ?></p>

                <div id="esp-taxonomies-container">
                    <?php
                    $saved_taxonomies = $group ? $group->taxonomies : [];
                    if (!empty($saved_taxonomies)):
                        foreach ($saved_taxonomies as $i => $tax): ?>
                            <div class="esp-repeater-row" data-index="<?php echo esc_attr($i); ?>">
                                <span class="row-handle dashicons dashicons-menu"></span>
                                <div class="row-fields">
                                    <input type="hidden" name="taxonomies[<?php echo esc_attr($i); ?>][order]" value="<?php echo esc_attr($i); ?>">
                                    <div class="row-field">
                                        <label><?php esc_html_e('Taxonomy', 'easy-search-filter'); ?></label>
                                        <select name="taxonomies[<?php echo esc_attr($i); ?>][slug]" required>
                                            <option value=""><?php esc_html_e('Select...', 'easy-search-filter'); ?></option>
                                            <?php foreach ($available_taxonomies as $at): ?>
                                                <option value="<?php echo esc_attr($at['slug']); ?>" <?php selected($tax['slug'], $at['slug']); ?>>
                                                    <?php echo esc_html($at['name']); ?> (<?php echo esc_html($at['slug']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="row-field">
                                        <label><?php esc_html_e('Display Label', 'easy-search-filter'); ?></label>
                                        <input type="text" name="taxonomies[<?php echo esc_attr($i); ?>][label]" value="<?php echo esc_attr($tax['label'] ?? ''); ?>" placeholder="<?php esc_attr_e('e.g., Location', 'easy-search-filter'); ?>">
                                    </div>
                                    <div class="row-actions">
                                        <button type="button" class="button button-small esp-remove-row"><?php esc_html_e('Remove', 'easy-search-filter'); ?></button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach;
                    endif; ?>
                </div>

                <button type="button" class="button" id="esp-add-taxonomy-row"><?php esc_html_e('Add Taxonomy', 'easy-search-filter'); ?></button>

                <?php if (empty($available_taxonomies)): ?>
                    <div class="notice notice-warning" style="margin-top: 12px;">
                        <p><?php esc_html_e('No taxonomies created yet.', 'easy-search-filter'); ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=esp-taxonomies&action=add')); ?>"><?php esc_html_e('Create one first', 'easy-search-filter'); ?></a>.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <div id="esp-tab-meta" class="esp-tab-content" style="margin-top: 16px; display: none;">
                <p><?php esc_html_e('Define custom meta field filters. These let users filter by custom fields (e.g., price, color, size).', 'easy-search-filter'); ?></p>

                <div id="esp-meta-fields-container">
                    <?php
                    $saved_meta = $group ? $group->meta_fields : [];
                    if (!empty($saved_meta)):
                        foreach ($saved_meta as $i => $field): ?>
                            <div class="esp-repeater-row" data-index="<?php echo esc_attr($i); ?>">
                                <span class="row-handle dashicons dashicons-menu"></span>
                                <div class="row-fields">
                                    <input type="hidden" name="meta_fields[<?php echo esc_attr($i); ?>][order]" value="<?php echo esc_attr($i); ?>">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                        <div class="row-field">
                                            <label><?php esc_html_e('Meta Key', 'easy-search-filter'); ?></label>
                                            <input type="text" name="meta_fields[<?php echo esc_attr($i); ?>][key]" value="<?php echo esc_attr($field['key']); ?>" required placeholder="<?php esc_attr_e('e.g., _price, color', 'easy-search-filter'); ?>">
                                        </div>
                                        <div class="row-field">
                                            <label><?php esc_html_e('Display Label', 'easy-search-filter'); ?></label>
                                            <input type="text" name="meta_fields[<?php echo esc_attr($i); ?>][label]" value="<?php echo esc_attr($field['label'] ?? ''); ?>" placeholder="<?php esc_attr_e('e.g., Price, Color', 'easy-search-filter'); ?>">
                                        </div>
                                        <div class="row-field">
                                            <label><?php esc_html_e('Field Type', 'easy-search-filter'); ?></label>
                                            <select name="meta_fields[<?php echo esc_attr($i); ?>][type]">
                                                <option value="select" <?php selected($field['type'], 'select'); ?>><?php esc_html_e('Select (Dropdown)', 'easy-search-filter'); ?></option>
                                                <option value="number" <?php selected($field['type'], 'number'); ?>><?php esc_html_e('Number (Single)', 'easy-search-filter'); ?></option>
                                                <option value="text" <?php selected($field['type'], 'text'); ?>><?php esc_html_e('Text Input', 'easy-search-filter'); ?></option>
                                                <option value="range" <?php selected($field['type'], 'range'); ?>><?php esc_html_e('Number Range (Min/Max)', 'easy-search-filter'); ?></option>
                                                <option value="checkbox" <?php selected($field['type'], 'checkbox'); ?>><?php esc_html_e('Checkbox', 'easy-search-filter'); ?></option>
                                            </select>
                                        </div>
                                        <div class="row-field">
                                            <label><?php esc_html_e('Compare Operator', 'easy-search-filter'); ?></label>
                                            <select name="meta_fields[<?php echo esc_attr($i); ?>][compare]">
                                                <option value="=" <?php selected($field['compare'], '='); ?>><?php esc_html_e('Equals (=)', 'easy-search-filter'); ?></option>
                                                <option value="!=" <?php selected($field['compare'], '!='); ?>><?php esc_html_e('Not equals (!=)', 'easy-search-filter'); ?></option>
                                                <option value=">" <?php selected($field['compare'], '>'); ?>><?php esc_html_e('Greater than (>)', 'easy-search-filter'); ?></option>
                                                <option value=">=" <?php selected($field['compare'], '>='); ?>><?php esc_html_e('Greater or equal (>=)', 'easy-search-filter'); ?></option>
                                                <option value="<" <?php selected($field['compare'], '<'); ?>><?php esc_html_e('Less than (<)', 'easy-search-filter'); ?></option>
                                                <option value="<=" <?php selected($field['compare'], '<='); ?>><?php esc_html_e('Less or equal (<=)', 'easy-search-filter'); ?></option>
                                                <option value="BETWEEN" <?php selected($field['compare'], 'BETWEEN'); ?>><?php esc_html_e('Between (BETWEEN)', 'easy-search-filter'); ?></option>
                                                <option value="LIKE" <?php selected($field['compare'], 'LIKE'); ?>><?php esc_html_e('Contains (LIKE)', 'easy-search-filter'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row-field">
                                        <label>
                                            <input type="checkbox" name="meta_fields[<?php echo esc_attr($i); ?>][auto_detect]" value="1" <?php checked(!empty($field['auto_detect'])); ?>>
                                            <?php esc_html_e('Auto-detect options from existing content', 'easy-search-filter'); ?>
                                        </label>
                                        <button type="button" class="button button-small esp-detect-values" data-index="<?php echo esc_attr($i); ?>"><?php esc_html_e('Scan Values', 'easy-search-filter'); ?></button>
                                        <span class="esp-detect-status" style="margin-left: 8px; color: #646970;"></span>
                                    </div>
                                    <div class="row-field">
                                        <label><?php esc_html_e('Options (for Select type)', 'easy-search-filter'); ?></label>
                                        <div class="esp-options-repeater" data-index="<?php echo esc_attr($i); ?>">
                                            <?php if (!empty($field['options'])): ?>
                                                <?php foreach ($field['options'] as $j => $opt): ?>
                                                    <div class="esp-option-row">
                                                        <input type="text" name="meta_fields[<?php echo esc_attr($i); ?>][options][<?php echo esc_attr($j); ?>][value]" value="<?php echo esc_attr($opt['value']); ?>" placeholder="<?php esc_attr_e('Value', 'easy-search-filter'); ?>">
                                                        <input type="text" name="meta_fields[<?php echo esc_attr($i); ?>][options][<?php echo esc_attr($j); ?>][label]" value="<?php echo esc_attr($opt['label'] ?? ''); ?>" placeholder="<?php esc_attr_e('Label', 'easy-search-filter'); ?>">
                                                        <button type="button" class="button button-small esp-remove-option">&times;</button>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            <button type="button" class="button button-small esp-add-option"><?php esc_html_e('+ Add Option', 'easy-search-filter'); ?></button>
                                        </div>
                                    </div>
                                    <div class="row-actions">
                                        <button type="button" class="button button-small esp-remove-row"><?php esc_html_e('Remove', 'easy-search-filter'); ?></button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach;
                    endif; ?>
                </div>

                <button type="button" class="button" id="esp-add-meta-field"><?php esc_html_e('Add Meta Field', 'easy-search-filter'); ?></button>
            </div>

            <div id="esp-tab-display" class="esp-tab-content" style="margin-top: 16px; display: none;">
                <?php $display = $group ? $group->display : []; ?>
                <table class="form-table esp-form-table">
                    <tr>
                        <th><label for="esp-display-layout"><?php esc_html_e('Results Layout', 'easy-search-filter'); ?></label></th>
                        <td>
                            <select id="esp-display-layout" name="display_layout">
                                <option value="grid" <?php selected($display['layout'] ?? 'grid', 'grid'); ?>><?php esc_html_e('Grid', 'easy-search-filter'); ?></option>
                                <option value="list" <?php selected($display['layout'] ?? 'grid', 'list'); ?>><?php esc_html_e('List', 'easy-search-filter'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="esp-display-columns"><?php esc_html_e('Grid Columns', 'easy-search-filter'); ?></label></th>
                        <td>
                            <input type="number" id="esp-display-columns" name="display_columns" value="<?php echo esc_attr($display['columns'] ?? 3); ?>" min="1" max="6" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="esp-display-per-page"><?php esc_html_e('Results Per Page', 'easy-search-filter'); ?></label></th>
                        <td>
                            <input type="number" id="esp-display-per-page" name="display_per_page" value="<?php echo esc_attr($display['per_page'] ?? 12); ?>" min="1" max="100" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="esp-display-orderby"><?php esc_html_e('Default Order By', 'easy-search-filter'); ?></label></th>
                        <td>
                            <select id="esp-display-orderby" name="display_orderby">
                                <option value="date" <?php selected($display['orderby'] ?? 'date', 'date'); ?>><?php esc_html_e('Date', 'easy-search-filter'); ?></option>
                                <option value="title" <?php selected($display['orderby'] ?? '', 'title'); ?>><?php esc_html_e('Title', 'easy-search-filter'); ?></option>
                                <option value="ID" <?php selected($display['orderby'] ?? '', 'ID'); ?>><?php esc_html_e('ID', 'easy-search-filter'); ?></option>
                                <option value="rand" <?php selected($display['orderby'] ?? '', 'rand'); ?>><?php esc_html_e('Random', 'easy-search-filter'); ?></option>
                                <option value="meta_value" <?php selected($display['orderby'] ?? '', 'meta_value'); ?>><?php esc_html_e('Meta Value', 'easy-search-filter'); ?></option>
                                <option value="meta_value_num" <?php selected($display['orderby'] ?? '', 'meta_value_num'); ?>><?php esc_html_e('Meta Value (Numeric)', 'easy-search-filter'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="esp-display-order"><?php esc_html_e('Order', 'easy-search-filter'); ?></label></th>
                        <td>
                            <select id="esp-display-order" name="display_order">
                                <option value="DESC" <?php selected($display['order'] ?? 'DESC', 'DESC'); ?>><?php esc_html_e('Descending', 'easy-search-filter'); ?></option>
                                <option value="ASC" <?php selected($display['order'] ?? '', 'ASC'); ?>><?php esc_html_e('Ascending', 'easy-search-filter'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <h3 style="margin-top: 24px; margin-bottom: 8px;"><?php esc_html_e('Indexer (Pro)', 'easy-search-filter'); ?></h3>
                <p style="color: #646970; margin-top: 0;"><?php esc_html_e('Show real-time result counts next to each filter option. Requires rebuilding the index after filter group changes.', 'easy-search-filter'); ?></p>
                <table class="form-table esp-form-table">
                    <tr>
                        <th><label for="esp-enable-indexer"><?php esc_html_e('Enable Indexer', 'easy-search-filter'); ?></label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="esp-enable-indexer" name="enable_indexer" value="1" <?php checked(!empty($display['enable_indexer'])); ?>>
                                <?php esc_html_e('Show counts next to filter options (e.g., "Norte (3)")', 'easy-search-filter'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="esp-hide-zero"><?php esc_html_e('Hide Zero-Count Options', 'easy-search-filter'); ?></label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="esp-hide-zero" name="hide_zero_count" value="1" <?php checked(!empty($display['hide_zero_count'])); ?>>
                                <?php esc_html_e('Hide filter options that have zero matching results', 'easy-search-filter'); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <h3 style="margin-top: 24px; margin-bottom: 8px;"><?php esc_html_e('Text Search', 'easy-search-filter'); ?></h3>
                <p style="color: #646970; margin-top: 0;"><?php esc_html_e('Add a text search input to filter results by keyword.', 'easy-search-filter'); ?></p>
                <table class="form-table esp-form-table">
                    <tr>
                        <th><label for="esp-enable-search"><?php esc_html_e('Enable Search', 'easy-search-filter'); ?></label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="esp-enable-search" name="enable_search" value="1" <?php checked(!empty($display['enable_search'])); ?>>
                                <?php esc_html_e('Show a text search field in the filter form', 'easy-search-filter'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="esp-search-placeholder"><?php esc_html_e('Placeholder Text', 'easy-search-filter'); ?></label></th>
                        <td>
                            <input type="text" id="esp-search-placeholder" name="search_placeholder" value="<?php echo esc_attr($display['search_placeholder'] ?? 'Search...'); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>

                <h3 style="margin-top: 24px; margin-bottom: 8px;"><?php esc_html_e('Sort Options (Pro)', 'easy-search-filter'); ?></h3>
                <p style="color: #646970; margin-top: 0;"><?php esc_html_e('Select which sorting options appear in the results dropdown.', 'easy-search-filter'); ?></p>
                <table class="form-table esp-form-table">
                    <tr>
                        <th><?php esc_html_e('Sort By', 'easy-search-filter'); ?></th>
                        <td>
                            <?php
                            $sort_opts = [
                                'date-desc'  => __('Newest First', 'easy-search-filter'),
                                'date-asc'   => __('Oldest First', 'easy-search-filter'),
                                'title-asc'  => __('Title A-Z', 'easy-search-filter'),
                                'title-desc' => __('Title Z-A', 'easy-search-filter'),
                            ];

                            $meta_sort_fields = [];
                            foreach (($group ? $group->meta_fields : []) as $f) {
                                if (in_array($f['type'], ['number', 'range'])) {
                                    $key = 'meta_' . $f['key'] . '_asc';
                                    $meta_sort_fields[$key] = $f['label'] . ' ↑';
                                    $key = 'meta_' . $f['key'] . '_desc';
                                    $meta_sort_fields[$key] = $f['label'] . ' ↓';
                                }
                            }
                            $sort_opts = array_merge($sort_opts, $meta_sort_fields);

                            $enabled = $display['sort_options'] ?? array_keys($sort_opts);
                            ?>
                            <?php foreach ($sort_opts as $val => $label): ?>
                                <label style="display: block; margin-bottom: 4px;">
                                    <input type="checkbox" name="sort_options[]" value="<?php echo esc_attr($val); ?>"
                                        <?php echo in_array($val, $enabled) ? 'checked' : ''; ?>>
                                    <?php echo esc_html($label); ?>
                                </label>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table>

                <?php if ($group): ?>
                <div style="background: #f6f7f7; border: 1px solid #dcdcde; padding: 16px; border-radius: 6px; margin-top: 20px;">
                    <h3><?php esc_html_e('Usage', 'easy-search-filter'); ?></h3>
                    <p><?php esc_html_e('Add these shortcodes to any page or post:', 'easy-search-filter'); ?></p>
                    <p>
                        <strong><?php esc_html_e('Filter Form:', 'easy-search-filter'); ?></strong>
                        <code>[esp_form id="<?php echo esc_attr($group->ID); ?>"]</code>
                    </p>
                    <p>
                        <strong><?php esc_html_e('Results:', 'easy-search-filter'); ?></strong>
                        <code>[esp_results id="<?php echo esc_attr($group->ID); ?>"]</code>
                    </p>
                    <p>
                        <strong><?php esc_html_e('Both combined:', 'easy-search-filter'); ?></strong>
                        <code>[esp_search id="<?php echo esc_attr($group->ID); ?>"]</code>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <p class="submit" style="margin-top: 24px;">
            <button type="submit" class="button button-primary button-large">
                <?php echo $group ? esc_html__('Update Filter Group', 'easy-search-filter') : esc_html__('Create Filter Group', 'easy-search-filter'); ?>
            </button>
        </p>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const postType = document.getElementById('esp-group-post-type');
    let metaIndex = <?php echo $group ? max(array_keys($saved_meta ?? [])) + 1 : 0; ?>;
    let taxIndex = <?php echo $group ? max(array_keys($saved_taxonomies ?? [])) + 1 : 0; ?>;

    // Tab switching
    document.querySelectorAll('.nav-tab-wrapper a').forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.nav-tab-wrapper a').forEach(function(t) {
                t.classList.remove('nav-tab-active');
            });
            document.querySelectorAll('.esp-tab-content').forEach(function(c) {
                c.style.display = 'none';
            });
            this.classList.add('nav-tab-active');
            document.querySelector(this.getAttribute('href')).style.display = 'block';
        });
    });

    // Add taxonomy row
    document.getElementById('esp-add-taxonomy-row')?.addEventListener('click', function() {
        addTaxonomyRow();
    });

    // Add meta field row
    document.getElementById('esp-add-meta-field')?.addEventListener('click', function() {
        addMetaFieldRow();
    });

    // Remove row handler (delegated)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('esp-remove-row')) {
            e.target.closest('.esp-repeater-row').remove();
        }
    });

    // Add option handler (delegated)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('esp-add-option')) {
            var container = e.target.closest('.esp-options-repeater');
            var index = container.dataset.index;
            var optionCount = container.querySelectorAll('.esp-option-row').length;
            var row = document.createElement('div');
            row.className = 'esp-option-row';
            row.innerHTML =
                '<input type="text" name="meta_fields[' + index + '][options][' + optionCount + '][value]" placeholder="<?php esc_attr_e('Value', 'easy-search-filter'); ?>">' +
                '<input type="text" name="meta_fields[' + index + '][options][' + optionCount + '][label]" placeholder="<?php esc_attr_e('Label', 'easy-search-filter'); ?>">' +
                '<button type="button" class="button button-small esp-remove-option">&times;</button>';
            container.insertBefore(row, e.target);
        }

        if (e.target.classList.contains('esp-remove-option')) {
            e.target.closest('.esp-option-row').remove();
        }
    });

    // Auto-detect meta values
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('esp-detect-values')) {
            var row = e.target.closest('.esp-repeater-row');
            var index = row.dataset.index;
            var metaKey = row.querySelector('input[name*="[key]"]')?.value;
            var statusEl = row.querySelector('.esp-detect-status');

            if (!metaKey) {
                statusEl.textContent = '<?php esc_attr_e('Enter a meta key first.', 'easy-search-filter'); ?>';
                statusEl.style.color = '#d63638';
                return;
            }

            statusEl.textContent = '<?php esc_attr_e('Scanning...', 'easy-search-filter'); ?>';
            statusEl.style.color = '#646970';

            jQuery.post(espAdmin.ajaxUrl, {
                action: 'esp_detect_meta_values',
                nonce: espAdmin.nonce,
                meta_key: metaKey,
                post_type: postType.value
            }, function(response) {
                if (response.success && response.data.options.length > 0) {
                    var optionsContainer = row.querySelector('.esp-options-repeater');
                    var existingOptions = optionsContainer.querySelectorAll('.esp-option-row');
                    existingOptions.forEach(function(opt) { opt.remove(); });

                    response.data.options.forEach(function(opt, j) {
                        var optRow = document.createElement('div');
                        optRow.className = 'esp-option-row';
                        optRow.innerHTML =
                            '<input type="text" name="meta_fields[' + index + '][options][' + j + '][value]" value="' + escHtml(opt.value) + '" placeholder="<?php esc_attr_e('Value', 'easy-search-filter'); ?>">' +
                            '<input type="text" name="meta_fields[' + index + '][options][' + j + '][label]" value="' + escHtml(opt.label) + '" placeholder="<?php esc_attr_e('Label', 'easy-search-filter'); ?>">' +
                            '<button type="button" class="button button-small esp-remove-option">&times;</button>';
                        optionsContainer.insertBefore(optRow, optionsContainer.querySelector('.esp-add-option'));
                    });

                    statusEl.textContent = '<?php esc_attr_e('Found', 'easy-search-filter'); ?> ' + response.data.options.length + ' <?php esc_attr_e('values', 'easy-search-filter'); ?>';
                    statusEl.style.color = '#46b450';
                } else {
                    statusEl.textContent = '<?php esc_attr_e('No values found.', 'easy-search-filter'); ?>';
                    statusEl.style.color = '#d63638';
                }
            });
        }
    });

    function addTaxonomyRow() {
        var container = document.getElementById('esp-taxonomies-container');
        var index = taxIndex++;
        var html = '<div class="esp-repeater-row" data-index="' + index + '">' +
            '<span class="row-handle dashicons dashicons-menu"></span>' +
            '<div class="row-fields">' +
            '<input type="hidden" name="taxonomies[' + index + '][order]" value="' + index + '">' +
            '<div class="row-field">' +
            '<label><?php esc_attr_e('Taxonomy', 'easy-search-filter'); ?></label>' +
            '<select name="taxonomies[' + index + '][slug]" required>' +
            '<option value=""><?php esc_attr_e('Select...', 'easy-search-filter'); ?></option>' +
            <?php foreach ($available_taxonomies as $at): ?>
            '<option value="<?php echo esc_attr($at['slug']); ?>"><?php echo esc_attr($at['name']); ?> (<?php echo esc_attr($at['slug']); ?>)</option>' +
            <?php endforeach; ?>
            '</select>' +
            '</div>' +
            '<div class="row-field">' +
            '<label><?php esc_attr_e('Display Label', 'easy-search-filter'); ?></label>' +
            '<input type="text" name="taxonomies[' + index + '][label]" placeholder="<?php esc_attr_e('e.g., Location', 'easy-search-filter'); ?>">' +
            '</div>' +
            '<div class="row-actions"><button type="button" class="button button-small esp-remove-row"><?php esc_attr_e('Remove', 'easy-search-filter'); ?></button></div>' +
            '</div></div>';
        container.insertAdjacentHTML('beforeend', html);
    }

    function addMetaFieldRow() {
        var container = document.getElementById('esp-meta-fields-container');
        var index = metaIndex++;
        var html = '<div class="esp-repeater-row" data-index="' + index + '">' +
            '<span class="row-handle dashicons dashicons-menu"></span>' +
            '<div class="row-fields">' +
            '<input type="hidden" name="meta_fields[' + index + '][order]" value="' + index + '">' +
            '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">' +
            '<div class="row-field"><label><?php esc_attr_e('Meta Key', 'easy-search-filter'); ?></label>' +
            '<input type="text" name="meta_fields[' + index + '][key]" required placeholder="<?php esc_attr_e('e.g., _price, color', 'easy-search-filter'); ?>"></div>' +
            '<div class="row-field"><label><?php esc_attr_e('Display Label', 'easy-search-filter'); ?></label>' +
            '<input type="text" name="meta_fields[' + index + '][label]" placeholder="<?php esc_attr_e('e.g., Price, Color', 'easy-search-filter'); ?>"></div>' +
            '<div class="row-field"><label><?php esc_attr_e('Field Type', 'easy-search-filter'); ?></label>' +
            '<select name="meta_fields[' + index + '][type]">' +
            '<option value="select"><?php esc_attr_e('Select (Dropdown)', 'easy-search-filter'); ?></option>' +
            '<option value="number"><?php esc_attr_e('Number (Single)', 'easy-search-filter'); ?></option>' +
            '<option value="text"><?php esc_attr_e('Text Input', 'easy-search-filter'); ?></option>' +
            '<option value="range"><?php esc_attr_e('Number Range (Min/Max)', 'easy-search-filter'); ?></option>' +
            '<option value="checkbox"><?php esc_attr_e('Checkbox', 'easy-search-filter'); ?></option>' +
            '</select></div>' +
            '<div class="row-field"><label><?php esc_attr_e('Compare Operator', 'easy-search-filter'); ?></label>' +
            '<select name="meta_fields[' + index + '][compare]">' +
            '<option value="="><?php esc_attr_e('Equals (=)', 'easy-search-filter'); ?></option>' +
            '<option value="!="><?php esc_attr_e('Not equals (!=)', 'easy-search-filter'); ?></option>' +
            '<option value=">"><?php esc_attr_e('Greater than (>)', 'easy-search-filter'); ?></option>' +
            '<option value=">="><?php esc_attr_e('Greater or equal (>=)', 'easy-search-filter'); ?></option>' +
            '<option value="<"><?php esc_attr_e('Less than (<)', 'easy-search-filter'); ?></option>' +
            '<option value="<="><?php esc_attr_e('Less or equal (<=)', 'easy-search-filter'); ?></option>' +
            '<option value="BETWEEN"><?php esc_attr_e('Between (BETWEEN)', 'easy-search-filter'); ?></option>' +
            '<option value="LIKE"><?php esc_attr_e('Contains (LIKE)', 'easy-search-filter'); ?></option>' +
            '</select></div>' +
            '</div>' +
            '<div class="row-field">' +
            '<label><input type="checkbox" name="meta_fields[' + index + '][auto_detect]" value="1"> <?php esc_attr_e('Auto-detect options from existing content', 'easy-search-filter'); ?></label>' +
            ' <button type="button" class="button button-small esp-detect-values" data-index="' + index + '"><?php esc_attr_e('Scan Values', 'easy-search-filter'); ?></button>' +
            '<span class="esp-detect-status" style="margin-left: 8px; color: #646970;"></span>' +
            '</div>' +
            '<div class="row-field">' +
            '<label><?php esc_attr_e('Options (for Select type)', 'easy-search-filter'); ?></label>' +
            '<div class="esp-options-repeater" data-index="' + index + '">' +
            '<button type="button" class="button button-small esp-add-option"><?php esc_attr_e('+ Add Option', 'easy-search-filter'); ?></button>' +
            '</div></div>' +
            '<div class="row-actions"><button type="button" class="button button-small esp-remove-row"><?php esc_attr_e('Remove', 'easy-search-filter'); ?></button></div>' +
            '</div></div>';
        container.insertAdjacentHTML('beforeend', html);
    }

    function escHtml(str) {
        var div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }
});
</script>
