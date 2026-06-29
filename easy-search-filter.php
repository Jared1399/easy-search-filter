<?php
/**
 * Plugin Name:     Easy Search & Filter
 * Plugin URI:      https://easysearchfilter.com
 * Description:     Universal hierarchical search and filter system for WordPress. Create dynamic taxonomies, cascading dropdowns, and custom meta field filters — no code required.
 * Version:         1.0.0
 * Author:          Jared Quiero
 * Author URI:      https://yourwebsite.com
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     easy-search-filter
 * Domain Path:     /languages
 * Requires PHP:    7.4
 * Requires WP:     5.8
 * WC requires at least: 5.0
 * WC tested up to: 9.0
 */

if ( ! function_exists( 'esf_fs' ) ) {
    function esf_fs() {
        global $esf_fs;

        if ( ! isset( $esf_fs ) ) {
            require_once dirname( __FILE__ ) . '/vendor/freemius/start.php';

            $esf_fs = fs_dynamic_init( array(
                'id'                  => '33097',
                'slug'                => 'easy-search-filter',
                'type'                => 'plugin',
                'public_key'          => 'pk_faa517023367349066bdda672cb9f',
                'is_premium'          => true,
                'premium_suffix'      => 'Business',
                'has_premium_version' => true,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'is_org_compliant'    => true,
                'wp_org_gatekeeper'   => 'OA7#BoRiBNqdf52FvzEf!!074aRLPs8fspif$7K1#4u4Csys1fQlCecVcUTOs2mcpeVHi#C2j9d09fOTvbC0HloPT7fFee5WdS3G',
                'trial'               => array(
                    'days'               => 7,
                    'is_require_payment' => false,
                ),
                'menu'                => array(
                    'slug'           => 'easy-search-filter',
                ),
            ) );
        }

        return $esf_fs;
    }
}

defined('ABSPATH') || exit;

define('ESP_VERSION', '1.0.0');
define('ESP_FILE', __FILE__);
define('ESP_PATH', plugin_dir_path(__FILE__));
define('ESP_URL', plugin_dir_url(__FILE__));
define('ESP_BASENAME', plugin_basename(__FILE__));

spl_autoload_register(function ($class) {
    $prefix = 'EasySearchFilter\\';
    $base_dir = ESP_PATH . 'includes/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relative_class = substr($class, strlen($prefix));
    $file = $base_dir . 'class-' . str_replace('_', '-', strtolower($relative_class)) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

register_activation_hook(__FILE__, ['EasySearchFilter\Activator', 'activate']);
register_deactivation_hook(__FILE__, ['EasySearchFilter\Deactivator', 'deactivate']);

function esp() {
    return EasySearchFilter\Main::instance();
}

add_action('esf_fs_loaded', 'esp');
esf_fs();
do_action('esf_fs_loaded');
