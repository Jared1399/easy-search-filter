<div class="wrap esp-admin-wrap">
    <div class="esp-admin-header">
        <span class="dashicons dashicons-layout" style="font-size: 32px; width: 32px; height: 32px; color: #2271b1;"></span>
        <h1><?php esc_html_e('Filter Groups', 'easy-search-filter'); ?></h1>
        <a href="<?php echo esc_url(admin_url('admin.php?page=esp-filter-groups&action=add')); ?>" class="page-title-action"><?php esc_html_e('Add New', 'easy-search-filter'); ?></a>
    </div>

    <?php if (isset($_GET['message'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                $msg = sanitize_text_field($_GET['message']);
                $messages = [
                    'saved' => __('Filter group saved successfully.', 'easy-search-filter'),
                    'deleted' => __('Filter group deleted successfully.', 'easy-search-filter'),
                ];
                echo esc_html($messages[$msg] ?? __('Operation completed.', 'easy-search-filter'));
                ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (empty($groups)): ?>
        <div class="esp-quick-start">
            <h2><?php esc_html_e('No filter groups yet', 'easy-search-filter'); ?></h2>
            <p><?php esc_html_e('A Filter Group ties together taxonomies and meta fields into a searchable form. Create one to get started.', 'easy-search-filter'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=esp-filter-groups&action=add')); ?>" class="button button-primary"><?php esc_html_e('Create Your First Filter Group', 'easy-search-filter'); ?></a>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Name', 'easy-search-filter'); ?></th>
                    <th><?php esc_html_e('Post Type', 'easy-search-filter'); ?></th>
                    <th><?php esc_html_e('Taxonomies', 'easy-search-filter'); ?></th>
                    <th><?php esc_html_e('Meta Fields', 'easy-search-filter'); ?></th>
                    <th><?php esc_html_e('Shortcode', 'easy-search-filter'); ?></th>
                    <th><?php esc_html_e('Actions', 'easy-search-filter'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $group): ?>
                    <tr>
                        <td><strong><?php echo esc_html($group->name); ?></strong></td>
                        <td>
                            <?php
                            $pt_obj = get_post_type_object($group->post_type);
                            echo $pt_obj ? esc_html($pt_obj->label) : esc_html($group->post_type);
                            ?>
                        </td>
                        <td>
                            <?php
                            $tax_names = [];
                            foreach ($group->taxonomies as $tax) {
                                $tax_obj = \EasySearchFilter\Taxonomy_Manager::instance()->get_taxonomy($tax['slug']);
                                $tax_names[] = $tax_obj ? $tax_obj['name'] : $tax['slug'];
                            }
                            echo $tax_names ? esc_html(implode(', ', $tax_names)) : '—';
                            ?>
                        </td>
                        <td><?php echo count($group->meta_fields); ?></td>
                        <td>
                            <code>[esp_form id="<?php echo esc_attr($group->ID); ?>"]</code><br>
                            <code>[esp_results id="<?php echo esc_attr($group->ID); ?>"]</code>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=esp-filter-groups&action=edit&group=' . $group->ID)); ?>"><?php esc_html_e('Edit', 'easy-search-filter'); ?></a>
                            |
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=esp_delete_filter_group&group=' . $group->ID), 'esp_delete_filter_group')); ?>"
                                onclick="return confirm('<?php esc_attr_e('Delete this filter group?', 'easy-search-filter'); ?>')">
                                <?php esc_html_e('Delete', 'easy-search-filter'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
