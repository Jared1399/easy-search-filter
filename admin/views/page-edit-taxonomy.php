<div class="wrap esp-admin-wrap">
    <div class="esp-admin-header">
        <span class="dashicons dashicons-category" style="font-size: 32px; width: 32px; height: 32px; color: #2271b1;"></span>
        <h1><?php echo $tax ? esc_html__('Edit Taxonomy', 'easy-search-filter') : esc_html__('Add New Taxonomy', 'easy-search-filter'); ?></h1>
        <a href="<?php echo esc_url(admin_url('admin.php?page=esp-taxonomies')); ?>" class="page-title-action"><?php esc_html_e('Back to List', 'easy-search-filter'); ?></a>
    </div>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('esp_save_taxonomy'); ?>
        <input type="hidden" name="action" value="esp_save_taxonomy">
        <?php if ($tax): ?>
            <input type="hidden" name="original_slug" value="<?php echo esc_attr($tax['slug']); ?>">
        <?php endif; ?>

        <table class="form-table esp-form-table">
            <tr>
                <th><label for="esp-tax-name"><?php esc_html_e('Name', 'easy-search-filter'); ?></label></th>
                <td>
                    <input type="text" id="esp-tax-name" name="name" value="<?php echo $tax ? esc_attr($tax['name']) : ''; ?>" required class="regular-text">
                    <p class="description"><?php esc_html_e('e.g., Location, Category, Brand', 'easy-search-filter'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="esp-tax-singular"><?php esc_html_e('Singular Name', 'easy-search-filter'); ?></label></th>
                <td>
                    <input type="text" id="esp-tax-singular" name="singular_name" value="<?php echo $tax ? esc_attr($tax['singular_name']) : ''; ?>" required class="regular-text">
                    <p class="description"><?php esc_html_e('e.g., Location, Category, Brand (singular form)', 'easy-search-filter'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="esp-tax-slug"><?php esc_html_e('Slug', 'easy-search-filter'); ?></label></th>
                <td>
                    <input type="text" id="esp-tax-slug" name="slug" value="<?php echo $tax ? esc_attr($tax['slug']) : ''; ?>" required class="regular-text" pattern="[a-z0-9_-]+">
                    <p class="description"><?php esc_html_e('URL-friendly name (lowercase, no spaces). e.g., location, brand', 'easy-search-filter'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="esp-tax-depth"><?php esc_html_e('Max Hierarchy Depth', 'easy-search-filter'); ?></label></th>
                <td>
                    <input type="number" id="esp-tax-depth" name="max_depth" value="<?php echo $tax ? esc_attr($tax['max_depth']) : '0'; ?>" min="0" max="10" class="small-text">
                    <p class="description"><?php esc_html_e('0 = unlimited levels. Maximum number of nested levels in the cascading dropdown.', 'easy-search-filter'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="esp-tax-post-types"><?php esc_html_e('Attach to Post Types', 'easy-search-filter'); ?></label></th>
                <td>
                    <select id="esp-tax-post-types" name="post_types[]" multiple style="width: 100%; max-width: 400px; height: 120px;">
                        <?php foreach ($post_types as $pt_slug => $pt_label): ?>
                            <option value="<?php echo esc_attr($pt_slug); ?>"
                                <?php echo ($tax && in_array($pt_slug, $tax['post_types'] ?? [])) ? 'selected' : ''; ?>>
                                <?php echo esc_html($pt_label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('Hold Ctrl (Cmd) to select multiple. Terms will be available for these post types.', 'easy-search-filter'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="esp-tax-desc"><?php esc_html_e('Description', 'easy-search-filter'); ?></label></th>
                <td>
                    <textarea id="esp-tax-desc" name="description" rows="3" class="regular-text"><?php echo $tax ? esc_textarea($tax['description'] ?? '') : ''; ?></textarea>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary">
                <?php echo $tax ? esc_html__('Update Taxonomy', 'easy-search-filter') : esc_html__('Create Taxonomy', 'easy-search-filter'); ?>
            </button>
        </p>
    </form>
</div>
