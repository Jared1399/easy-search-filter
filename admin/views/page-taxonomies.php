<div class="wrap esp-admin-wrap">
    <div class="esp-admin-header">
        <span class="dashicons dashicons-category" style="font-size: 32px; width: 32px; height: 32px; color: #2271b1;"></span>
        <h1><?php esc_html_e('Taxonomies', 'easy-search-filter'); ?></h1>
        <a href="<?php echo esc_url(admin_url('admin.php?page=esp-taxonomies&action=add')); ?>" class="page-title-action"><?php esc_html_e('Add New', 'easy-search-filter'); ?></a>
    </div>

    <?php if (isset($_GET['message'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                $msg = sanitize_text_field($_GET['message']);
                $messages = [
                    'saved' => __('Taxonomy saved successfully.', 'easy-search-filter'),
                    'deleted' => __('Taxonomy deleted successfully.', 'easy-search-filter'),
                ];
                echo esc_html($messages[$msg] ?? __('Operation completed.', 'easy-search-filter'));
                ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html(sanitize_text_field($_GET['error'])); ?></p>
        </div>
    <?php endif; ?>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Name', 'easy-search-filter'); ?></th>
                <th><?php esc_html_e('Slug', 'easy-search-filter'); ?></th>
                <th><?php esc_html_e('Post Types', 'easy-search-filter'); ?></th>
                <th><?php esc_html_e('Max Depth', 'easy-search-filter'); ?></th>
                <th><?php esc_html_e('Actions', 'easy-search-filter'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($taxonomies)): ?>
                <tr>
                    <td colspan="5"><?php esc_html_e('No taxonomies found. Create your first one!', 'easy-search-filter'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($taxonomies as $tax): ?>
                    <tr>
                        <td><strong><?php echo esc_html($tax['name']); ?></strong></td>
                        <td><code><?php echo esc_html($tax['slug']); ?></code></td>
                        <td>
                            <?php
                            $post_types = $tax['post_types'] ?? ['post'];
                            $labels = [];
                            foreach ($post_types as $pt) {
                                $obj = get_post_type_object($pt);
                                $labels[] = $obj ? $obj->label : $pt;
                            }
                            echo esc_html(implode(', ', $labels));
                            ?>
                        </td>
                        <td><?php echo esc_html($tax['max_depth'] ? sprintf(__('%d levels', 'easy-search-filter'), $tax['max_depth']) : __('Unlimited', 'easy-search-filter')); ?></td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=esp-taxonomies&action=edit&taxonomy=' . urlencode($tax['slug']))); ?>"><?php esc_html_e('Edit', 'easy-search-filter'); ?></a>
                            |
                            <a href="<?php echo esc_url(admin_url('admin.php?page=esp-taxonomies&action=terms&taxonomy=' . urlencode($tax['slug']))); ?>"><?php esc_html_e('Manage Terms', 'easy-search-filter'); ?></a>
                            |
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=esp_delete_taxonomy&taxonomy=' . urlencode($tax['slug'])), 'esp_delete_taxonomy')); ?>" onclick="return confirm('<?php esc_attr_e('Delete this taxonomy and all its terms?', 'easy-search-filter'); ?>')"><?php esc_html_e('Delete', 'easy-search-filter'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
