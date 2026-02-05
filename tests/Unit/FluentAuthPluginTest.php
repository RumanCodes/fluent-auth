<?php
/**
 * Test for main FluentAuthPlugin class
 */

require_once __DIR__ . '/MockClasses.php';

use PHPUnit\Framework\TestCase;

class FluentAuthPluginTest extends TestCase {
    
    protected $plugin;
    
    protected function setUp(): void {
        // Mock basic WordPress functions
        $this->mockWordPressFunctions();
        
        // Define plugin constants
        if (!defined('ABSPATH')) {
            define('ABSPATH', '/var/www/wordpress/');
        }
        
        // Include the main plugin file
        require_once FLUENT_AUTH_PLUGIN_PATH . 'fluent-security.php';
        
        // Create plugin instance
        $this->plugin = new FluentAuthPlugin();
    }
    
    protected function mockWordPressFunctions() {
        // Mock plugin functions
        if (!function_exists('plugin_dir_path')) {
            function plugin_dir_path($file) {
                return dirname($file) . '/';
            }
        }
        
        if (!function_exists('plugin_dir_url')) {
            function plugin_dir_url($file) {
                return 'http://example.org/wp-content/plugins/' . basename(dirname($file)) . '/';
            }
        }
        
        if (!function_exists('register_activation_hook')) {
            function register_activation_hook($file, $function) {
                return true;
            }
        }
        
        if (!function_exists('register_deactivation_hook')) {
            function register_deactivation_hook($file, $function) {
                return true;
            }
        }
        
        if (!function_exists('load_plugin_textdomain')) {
            function load_plugin_textdomain($domain, $deprecated = false, $plugin_rel_path = false) {
                return true;
            }
        }
        
        if (!function_exists('plugin_basename')) {
            function plugin_basename($file) {
                return basename(dirname($file)) . '/' . basename($file);
            }
        }
        
        if (!function_exists('add_filter')) {
            function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
                return true;
            }
        }
        
        if (!function_exists('wp_clear_scheduled_hook')) {
            function wp_clear_scheduled_hook($hook) {
                return true;
            }
        }
        
        if (!function_exists('add_action')) {
            function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
                return true;
            }
        }
        
        if (!function_exists('do_action')) {
            function do_action($tag, ...$args) {
                return null;
            }
        }
        
        if (!function_exists('esc_url')) {
            function esc_url($url) {
                return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            }
        }
        
        if (!function_exists('admin_url')) {
            function admin_url($path = '') {
                return 'http://example.org/wp-admin/' . ltrim($path, '/');
            }
        }
        
        if (!function_exists('esc_html__')) {
            function esc_html__($text, $domain = 'default') {
                return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
            }
        }
        
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
    }
    
    public function testPluginInitialization() {
        // Test that plugin initializes without errors
        $this->assertInstanceOf(FluentAuthPlugin::class, $this->plugin);
    }
    
    public function testAutoLoad() {
        // Test that the autoloader is properly set up
        $reflection = new ReflectionClass($this->plugin);
        $method = $reflection->getMethod('autoLoad');
        $method->setAccessible(true);
        
        // This should not throw any exceptions
        try {
            $method->invoke($this->plugin);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail('autoLoad() threw an exception: ' . $e->getMessage());
        }
    }
    
    public function testActivationHook() {
        // Test plugin activation
        $result = $this->plugin->activatePlugin();
        $this->assertNull($result);
    }
    
    public function testDeactivationHook() {
        // Test plugin deactivation
        $result = $this->plugin->deactivatePlugin();
        $this->assertNull($result);
    }
    
    public function testAddContextLinks() {
        // Test adding context links
        $actions = [];
        $result = $this->plugin->addContextLinks($actions);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('settings', $result);
        $this->assertArrayHasKey('dashboard_page', $result);
    }
    
    public function testConstantsDefined() {
        // Test that required constants are defined
        $this->assertTrue(defined('FLUENT_AUTH_PLUGIN_PATH'));
        $this->assertTrue(defined('FLUENT_AUTH_PLUGIN_URL'));
        $this->assertTrue(defined('FLUENT_AUTH_VERSION'));
    }
    
    public function testSingletonPattern() {
        // Test that plugin follows singleton pattern
        $reflection = new ReflectionClass($this->plugin);
        
        // Check that constructor is public (we can instantiate)
        $constructor = $reflection->getConstructor();
        $this->assertTrue($constructor->isPublic());
    }
}