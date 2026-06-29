<div class="wrap esp-admin-wrap">
    <div class="esp-admin-header">
        <span class="dashicons dashicons-list-view" style="font-size: 32px; width: 32px; height: 32px; color: #2271b1;"></span>
        <h1><?php echo esc_html(sprintf(__('Terms: %s', 'easy-search-filter'), $tax['name'] ?? '')); ?></h1>
        <a href="<?php echo esc_url(admin_url('admin.php?page=esp-taxonomies')); ?>" class="page-title-action"><?php esc_html_e('Back to Taxonomies', 'easy-search-filter'); ?></a>
    </div>

    <?php if (isset($_GET['message'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                $msg = sanitize_text_field($_GET['message']);
                $messages = [
                    'term_added' => __('Term added successfully.', 'easy-search-filter'),
                    'term_updated' => __('Term updated successfully.', 'easy-search-filter'),
                    'term_deleted' => __('Term deleted successfully.', 'easy-search-filter'),
                ];
                echo esc_html($messages[$msg] ?? __('Operation completed.', 'easy-search-filter'));
                ?>
            </p>
        </div>
    <?php endif; ?>

    <details class="esp-add-term-panel" <?php echo empty($tree_terms) ? 'open' : ''; ?>>
        <summary class="esp-add-term-summary">
            <span class="dashicons dashicons-plus-alt2"></span>
            <?php esc_html_e('Add New Term', 'easy-search-filter'); ?>
        </summary>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="esp-term-form">
            <?php wp_nonce_field('esp_add_term'); ?>
            <input type="hidden" name="action" value="esp_add_term">
            <input type="hidden" name="taxonomy" value="<?php echo esc_attr($tax['slug']); ?>">

            <p>
                <label for="esp-term-name"><?php esc_html_e('Name', 'easy-search-filter'); ?> *</label>
                <input type="text" id="esp-term-name" name="name" required class="regular-text" style="width: 100%;">
            </p>
            <p>
                <label for="esp-term-slug"><?php esc_html_e('Slug', 'easy-search-filter'); ?></label>
                <input type="text" id="esp-term-slug" name="slug" class="regular-text" style="width: 100%;">
            </p>
            <p>
                <label for="esp-term-parent"><?php esc_html_e('Parent', 'easy-search-filter'); ?></label>
                <select id="esp-term-parent" name="parent" style="width: 100%;">
                    <option value="0"><?php esc_html_e('None (top level)', 'easy-search-filter'); ?></option>
                    <?php foreach ($tree_options as $opt): ?>
                        <option value="<?php echo esc_attr($opt['term_id']); ?>">
                            <?php echo esc_html($opt['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label for="esp-term-desc"><?php esc_html_e('Description', 'easy-search-filter'); ?></label>
                <textarea id="esp-term-desc" name="description" rows="2" style="width: 100%;"></textarea>
            </p>
            <p>
                <button type="submit" class="button button-primary"><?php esc_html_e('Add Term', 'easy-search-filter'); ?></button>
            </p>
        </form>
    </details>

    <div class="esp-terms-list-col">
        <h2>
            <?php esc_html_e('Existing Terms', 'easy-search-filter'); ?>
            <?php if (!empty($tree_terms)): ?>
                <span class="esp-count-badge"><?php echo count($tree_terms); ?></span>
            <?php endif; ?>
        </h2>

        <?php if (empty($tree_terms)): ?>
            <p><?php esc_html_e('No terms yet.', 'easy-search-filter'); ?></p>
        <?php else: ?>
            <div class="esp-tree-toolbar">
                <button type="button" class="button button-small" id="esp-expand-all"><?php esc_html_e('Expand All', 'easy-search-filter'); ?></button>
                <button type="button" class="button button-small" id="esp-collapse-all"><?php esc_html_e('Collapse All', 'easy-search-filter'); ?></button>
            </div>
            <table class="wp-list-table widefat fixed striped esp-terms-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Hierarchy', 'easy-search-filter'); ?></th>
                        <th><?php esc_html_e('Slug', 'easy-search-filter'); ?></th>
                        <th><?php esc_html_e('Actions', 'easy-search-filter'); ?></th>
                    </tr>
                </thead>
                <tbody id="esp-terms-tbody">
                    <?php foreach ($tree_terms as $i => $t):
                        $depth = $t->esp_depth ?? 0;
                        $next = $tree_terms[$i + 1] ?? null;
                        $has_children = $next && ($next->esp_depth ?? 0) > $depth;
                    ?>
                    <tr class="esp-term-row esp-depth-<?php echo esc_attr($depth); ?>"
                        data-term-id="<?php echo esc_attr($t->term_id); ?>"
                        data-parent-id="<?php echo esc_attr($t->parent); ?>"
                        data-depth="<?php echo esc_attr($depth); ?>"
                        <?php echo $depth > 0 ? 'style="display:none"' : ''; ?>>
                        <td class="esp-term-name-cell">
                            <div class="esp-tree-row" style="padding-left: <?php echo esc_attr($depth * 28); ?>px">
                                <?php if ($has_children): ?>
                                    <span class="esp-toggle-icon">&#9654;</span>
                                <?php else: ?>
                                    <span class="esp-toggle-spacer"></span>
                                <?php endif; ?>
                                <span class="esp-term-dot"></span>
                                <span class="esp-term-name"><?php echo esc_html($t->name); ?></span>
                            </div>
                        </td>
                        <td><code><?php echo esc_html($t->slug); ?></code></td>
                        <td class="esp-term-actions">
                            <button type="button" class="button button-small esp-edit-term-btn"
                                data-term-id="<?php echo esc_attr($t->term_id); ?>"
                                data-name="<?php echo esc_attr($t->name); ?>"
                                data-slug="<?php echo esc_attr($t->slug); ?>"
                                data-parent="<?php echo esc_attr($t->parent); ?>"
                                data-desc="<?php echo esc_attr($t->description); ?>">
                                <?php esc_html_e('Edit', 'easy-search-filter'); ?>
                            </button>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=esp_delete_term&taxonomy=' . urlencode($tax['slug']) . '&term_id=' . $t->term_id), 'esp_delete_term')); ?>"
                                class="button button-small"
                                onclick="return confirm('<?php esc_attr_e('Delete this term?', 'easy-search-filter'); ?>')">
                                <?php esc_html_e('Delete', 'easy-search-filter'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div id="esp-edit-term-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
        <div style="background:#fff; max-width:500px; margin:10% auto; padding:24px; border-radius:8px; position:relative;">
            <button type="button" id="esp-close-modal" style="position:absolute; top:12px; right:16px; background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
            <h2><?php esc_html_e('Edit Term', 'easy-search-filter'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('esp_edit_term'); ?>
                <input type="hidden" name="action" value="esp_edit_term">
                <input type="hidden" name="taxonomy" value="<?php echo esc_attr($tax['slug']); ?>">
                <input type="hidden" id="esp-edit-term-id" name="term_id" value="">

                <p>
                    <label for="esp-edit-name"><?php esc_html_e('Name', 'easy-search-filter'); ?> *</label>
                    <input type="text" id="esp-edit-name" name="name" required class="regular-text" style="width:100%;">
                </p>
                <p>
                    <label for="esp-edit-slug"><?php esc_html_e('Slug', 'easy-search-filter'); ?></label>
                    <input type="text" id="esp-edit-slug" name="slug" class="regular-text" style="width:100%;">
                </p>
                <p>
                    <label for="esp-edit-parent"><?php esc_html_e('Parent', 'easy-search-filter'); ?></label>
                    <select id="esp-edit-parent" name="parent" style="width:100%;">
                        <option value="0"><?php esc_html_e('None (top level)', 'easy-search-filter'); ?></option>
                        <?php foreach ($tree_options as $opt): ?>
                            <option value="<?php echo esc_attr($opt['term_id']); ?>">
                                <?php echo esc_html($opt['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p>
                    <label for="esp-edit-desc"><?php esc_html_e('Description', 'easy-search-filter'); ?></label>
                    <textarea id="esp-edit-desc" name="description" rows="2" style="width:100%;"></textarea>
                </p>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Update Term', 'easy-search-filter'); ?></button>
                    <button type="button" class="button" id="esp-cancel-edit"><?php esc_html_e('Cancel', 'easy-search-filter'); ?></button>
                </p>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var tbody = document.getElementById('esp-terms-tbody');
        if (!tbody) return;

        // --- Edit term modal ---
        var editButtons = document.querySelectorAll('.esp-edit-term-btn');
        var modal = document.getElementById('esp-edit-term-modal');
        var closeBtn = document.getElementById('esp-close-modal');
        var cancelBtn = document.getElementById('esp-cancel-edit');

        editButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('esp-edit-term-id').value = this.dataset.termId;
                document.getElementById('esp-edit-name').value = this.dataset.name;
                document.getElementById('esp-edit-slug').value = this.dataset.slug;
                document.getElementById('esp-edit-parent').value = this.dataset.parent;
                document.getElementById('esp-edit-desc').value = this.dataset.desc;
                modal.style.display = 'block';
            });
        });

        function closeModal() {
            modal.style.display = 'none';
        }
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });

        // --- Tree expand/collapse ---
        var rows = Array.from(tbody.querySelectorAll('tr.esp-term-row'));

        function getChildren(parentRow) {
            var parentDepth = parseInt(parentRow.dataset.depth);
            var parentIndex = rows.indexOf(parentRow);
            var children = [];
            for (var j = parentIndex + 1; j < rows.length; j++) {
                var d = parseInt(rows[j].dataset.depth);
                if (d <= parentDepth) break;
                children.push(rows[j]);
            }
            return children;
        }

        function expand(parentRow) {
            var icon = parentRow.querySelector('.esp-toggle-icon');
            if (icon) icon.innerHTML = '&#9660;';
            var children = getChildren(parentRow);
            children.forEach(function(r) { r.style.display = ''; });
        }

        function collapse(parentRow) {
            var icon = parentRow.querySelector('.esp-toggle-icon');
            if (icon) icon.innerHTML = '&#9654;';
            var children = getChildren(parentRow);
            children.forEach(function(r) { r.style.display = 'none'; });
        }

        function isExpanded(parentRow) {
            var icon = parentRow.querySelector('.esp-toggle-icon');
            return icon && icon.innerHTML.indexOf('9660') !== -1;
        }

        // Toggle on click (delegated)
        tbody.addEventListener('click', function(e) {
            var toggle = e.target.closest('.esp-toggle-icon');
            if (!toggle) return;
            var row = toggle.closest('tr');
            if (isExpanded(row)) {
                collapse(row);
            } else {
                expand(row);
            }
        });

        // Expand All
        document.getElementById('esp-expand-all').addEventListener('click', function() {
            rows.forEach(function(r) {
                r.style.display = '';
                var icon = r.querySelector('.esp-toggle-icon');
                if (icon) icon.innerHTML = '&#9660;';
            });
        });

        // Collapse All (only depth 0 visible)
        document.getElementById('esp-collapse-all').addEventListener('click', function() {
            rows.forEach(function(r) {
                var depth = parseInt(r.dataset.depth);
                if (depth > 0) {
                    r.style.display = 'none';
                } else {
                    r.style.display = '';
                }
                var icon = r.querySelector('.esp-toggle-icon');
                if (icon) icon.innerHTML = '&#9654;';
            });
        });
    });
    </script>
</div>
