<div class="wrap esp-admin-wrap">
    <div class="esp-admin-header">
        <span class="dashicons dashicons-admin-settings" style="font-size: 32px; width: 32px; height: 32px; color: #2271b1;"></span>
        <h1><?php esc_html_e('Settings', 'easy-search-filter'); ?></h1>
    </div>

    <form method="post" action="options.php">
        <?php settings_fields('esp_settings'); ?>
        <?php $settings = get_option('esp_settings', []); ?>

        <table class="form-table esp-form-table">
            <tr>
                <th><label for="esp-default-per-page"><?php esc_html_e('Default Results Per Page', 'easy-search-filter'); ?></label></th>
                <td>
                    <input type="number" id="esp-default-per-page" name="esp_settings[default_per_page]" value="<?php echo esc_attr($settings['default_per_page'] ?? 12); ?>" min="1" max="100" class="small-text">
                </td>
            </tr>
            <tr>
                <th><label for="esp-default-columns"><?php esc_html_e('Default Grid Columns', 'easy-search-filter'); ?></label></th>
                <td>
                    <input type="number" id="esp-default-columns" name="esp_settings[default_columns]" value="<?php echo esc_attr($settings['default_columns'] ?? 3); ?>" min="1" max="6" class="small-text">
                </td>
            </tr>
            <tr>
                <th><label for="esp-enable-auto-detect"><?php esc_html_e('Enable Auto-Detect Meta Values', 'easy-search-filter'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="esp-enable-auto-detect" name="esp_settings[enable_auto_detect]" value="1" <?php checked(!empty($settings['enable_auto_detect'])); ?>>
                        <?php esc_html_e('Automatically detect existing meta values when configuring select-type filters', 'easy-search-filter'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary"><?php esc_html_e('Save Settings', 'easy-search-filter'); ?></button>
        </p>
    </form>

    <?php if (esf_fs()->can_use_premium_code__premium_only()): ?>
    <hr style="margin: 24px 0;">
    <h2><?php esc_html_e('Indexer', 'easy-search-filter'); ?></h2>
    <p><?php esc_html_e('The indexer pre-computes filter counts for faster performance. Rebuild after adding/editing filter groups or importing content.', 'easy-search-filter'); ?></p>
    <button type="button" class="button" id="esp-rebuild-index"><?php esc_html_e('Rebuild Index', 'easy-search-filter'); ?></button>
    <span id="esp-rebuild-status" style="margin-left: 8px; color: #646970;"></span>

    <script>
    document.getElementById('esp-rebuild-index')?.addEventListener('click', function() {
        var btn = this;
        var status = document.getElementById('esp-rebuild-status');
        btn.disabled = true;
        status.textContent = '<?php esc_attr_e('Rebuilding...', 'easy-search-filter'); ?>';

        jQuery.post(espAdmin.ajaxUrl, {
            action: 'esp_rebuild_index',
            nonce: espAdmin.nonce
        }, function(response) {
            btn.disabled = false;
            if (response.success) {
                status.textContent = '<?php esc_attr_e('Index rebuilt successfully!', 'easy-search-filter'); ?>';
                status.style.color = '#46b450';
            } else {
                status.textContent = '<?php esc_attr_e('Failed to rebuild index.', 'easy-search-filter'); ?>';
                status.style.color = '#d63638';
            }
        });
    });
    </script>
    <?php endif; ?>
</div>
