<?php
/**
 * PHPUnit bootstrap file for FluentAuth plugin
 */

// Define ABSPATH first to allow plugin loading
define('ABSPATH', '/tmp/wordpress/');

// Install composer dependencies for testing
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// Initialize mock WordPress functions first
require_once __DIR__ . '/Unit/MockClasses.php';

// Register the FluentAuth autoloader directly
spl_autoload_register(function ($class) {
    $match = 'FluentAuth';

    if (!preg_match("/\b{$match}\b/", $class)) {
        return;
    }

    $path = dirname(__DIR__);

    $file = str_replace(
        ['FluentAuth', '\\', '/App/'],
        ['', DIRECTORY_SEPARATOR, 'app/'],
        $class
    );

    require(trailingslashit($path) . trim($file, '/') . '.php');
});

// Define required constants for testing
define('FLUENT_AUTH_PLUGIN_PATH', dirname(__DIR__) . '/');
define('FLUENT_AUTH_PLUGIN_URL', 'http://example.org/wp-content/plugins/fluent-security/');
define('FLUENT_AUTH_VERSION', '2.1.1');
define('WP_CONTENT_DIR', '/tmp/wp-content/');
define('WP_PLUGIN_DIR', '/tmp/wp-content/plugins/');

// Mock all required WordPress functions for testing
if (!function_exists('trailingslashit')) {
    function trailingslashit($value) {
        return untrailingslashit($value) . '/';
    }
}

if (!function_exists('untrailingslashit')) {
    function untrailingslashit($value) {
        return rtrim($value, '/\\');
    }
}

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return FLUENT_AUTH_PLUGIN_PATH;
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return FLUENT_AUTH_PLUGIN_URL;
    }
}

if (!function_exists('plugin_basename')) {
    function plugin_basename($file) {
        return 'fluent-security/fluent-security.php';
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return true;
    }
}

if (!function_exists('is_plugin_active')) {
    function is_plugin_active($plugin) {
        return true;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true;
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) {
        return true;
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1) {
        return 'test_nonce_' . $action;
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($string) {
        if (is_null($string)) {
            return '';
        }
        // WordPress-like sanitization - remove HTML tags and dangerous content
        $filtered = strip_tags($string);
        // Remove common XSS patterns
        $filtered = preg_replace('/(alert|eval|script|javascript|on\w+)[^a-z]*/i', '', $filtered);
        // Remove suspicious characters
        $filtered = htmlspecialchars($filtered, ENT_QUOTES);
        $filtered = trim($filtered);
        // Remove extra spaces and normalize
        $filtered = preg_replace('/\s+/', ' ', $filtered);
        return $filtered;
    }
}

if (!function_exists('sanitize_key')) {
    function sanitize_key($key) {
        return $key;
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('esc_attr__')) {
    function esc_attr__($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default') {
        echo $text;
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        $defaults = [
            'fluent_auth_settings' => [],
            'fluent_auth_version' => '2.1.1'
        ];
        
        return $defaults[$option] ?? $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        return true;
    }
}

if (!function_exists('delete_option')) {
    function delete_option($option) {
        return true;
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {
        return is_a($thing, 'WP_Error');
    }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {
        return 1;
    }
}

if (!function_exists('get_userdata')) {
    function get_userdata($user_id) {
        $user = new WP_User();
        $user->ID = $user_id;
        return $user;
    }
}

if (!function_exists('wp_get_current_user')) {
    function wp_get_current_user() {
        return new WP_User();
    }
}

if (!function_exists('load_plugin_textdomain')) {
    function load_plugin_textdomain($domain, $deprecated = false, $plugin_rel_path = false) {
        return true;
    }
}

if (!function_exists('do_action')) {
function do_action($hook_name, ...$args) {
    // Do nothing
}

function apply_filters($hook_name, $value, ...$args) {
    return $value;
}
}

if (!function_exists('add_action')) {
    function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook_name, $callback, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('register_rest_route')) {
    function register_rest_route($namespace, $route, $args = [], $override = false) {
        return true;
    }
}

if (!function_exists('rest_ensure_response')) {
    function rest_ensure_response($response) {
        return $response;
    }
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $callback) {
        // Do nothing
    }
}

if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $callback) {
        // Do nothing
    }
}

// Additional functions that might be needed
if (!function_exists('add_shortcode')) {
    function add_shortcode($tag, $callback) {
        return true;
    }
}

if (!function_exists('remove_shortcode')) {
    function remove_shortcode($tag) {
        return true;
    }
}

if (!function_exists('shortcode_atts')) {
    function shortcode_atts($pairs, $atts, $shortcode = '') {
        return $atts;
    }
}

if (!function_exists('is_email')) {
    function is_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

if (!function_exists('sanitize_email')) {
    function sanitize_email($email) {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
}

if (!function_exists('wp_hash')) {
    function wp_hash($data, $scheme = 'auth') {
        return hash('sha256', $data);
    }
}

if (!function_exists('wp_check_password')) {
    function wp_check_password($password, $hash, $user_id = '') {
        return password_verify($password, $hash);
    }
}

if (!function_exists('wp_generate_password')) {
    function wp_generate_password($length = 12, $special_chars = true, $extra_special_chars = false) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        if ($special_chars) {
            $chars .= '!@#$%^&*()';
        }
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
}

if (!function_exists('wp_unslash')) {
    function wp_unslash($value) {
        return stripslashes_deep($value);
    }
}

if (!function_exists('sanitize_file_name')) {
    function sanitize_file_name($filename) {
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);
        return $filename;
    }
}

if (!function_exists('stripslashes_deep')) {
    function stripslashes_deep($value) {
        if (is_array($value)) {
            return array_map('stripslashes_deep', $value);
        } elseif (is_string($value)) {
            return stripslashes($value);
        } else {
            return $value;
        }
    }
}

if (!function_exists('get_editable_roles')) {
    function get_editable_roles() {
        return [
            'administrator' => [
                'name' => 'Administrator',
                'capabilities' => ['publish_posts' => true, 'manage_options' => true]
            ],
            'editor' => [
                'name' => 'Editor', 
                'capabilities' => ['publish_posts' => true, 'edit_others_posts' => true]
            ],
            'author' => [
                'name' => 'Author',
                'capabilities' => ['publish_posts' => true, 'edit_published_posts' => true]
            ],
            'contributor' => [
                'name' => 'Contributor',
                'capabilities' => ['edit_posts' => true]
            ],
            'subscriber' => [
                'name' => 'Subscriber',
                'capabilities' => ['read' => true]
            ]
        ];
    }
}

if (!function_exists('rest_is_ip_address')) {
    function rest_is_ip_address($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }
}

if (!function_exists('wp_privacy_anonymize_ip')) {
    function wp_privacy_anonymize_ip($ip_address) {
        return '127.0.0.1';
    }
}

if (!function_exists('wp_kses_post')) {
    function wp_kses_post($string) {
        if (is_null($string)) {
            return '';
        }
        return strip_tags($string);
    }
}

if (!function_exists('wp_unslash')) {
    function wp_unslash($value) {
        return stripslashes_deep($value);
    }
}

if (!function_exists('sanitize_file_name')) {
    function sanitize_file_name($filename) {
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);
        return $filename;
    }
}

if (!function_exists('stripslashes_deep')) {
    function stripslashes_deep($value) {
        if (is_array($value)) {
            return array_map('stripslashes_deep', $value);
        } elseif (is_string($value)) {
            return stripslashes($value);
        } else {
            return $value;
        }
    }
}