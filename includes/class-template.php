<?php
namespace EasySearchFilter;

defined('ABSPATH') || exit;

class Template {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    public function locate($template_name) {
        $theme_file = get_stylesheet_directory() . '/esp/' . $template_name;

        if (file_exists($theme_file)) {
            return $theme_file;
        }

        $plugin_file = ESP_PATH . 'templates/' . $template_name;

        if (file_exists($plugin_file)) {
            return $plugin_file;
        }

        return false;
    }

    public function render($template_name, $data = []) {
        $template_path = $this->locate($template_name);

        if (!$template_path) {
            printf(
                '<p>' . esc_html__('Template %s not found.', 'easy-search-filter') . '</p>',
                esc_html($template_name)
            );
            return;
        }

        extract($data);
        include $template_path;
    }

    public function render_string($template_name, $data = []) {
        ob_start();
        $this->render($template_name, $data);
        return ob_get_clean();
    }
}
