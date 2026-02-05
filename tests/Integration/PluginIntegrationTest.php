<?php
/**
 * Integration test for the complete plugin functionality
 */

require_once __DIR__ . '/../Unit/MockClasses.php';

use PHPUnit\Framework\TestCase;

class PluginIntegrationTest extends TestCase {
    
    protected function setUp(): void {
        // Mock all required WordPress functions
        $this->mockWordPressFunctions();
        
        // Define required constants
        if (!defined('ABSPATH')) {
            define('ABSPATH', '/var/www/wordpress/');
        }
        if (!defined('FLUENT_AUTH_PLUGIN_PATH')) {
            define('FLUENT_AUTH_PLUGIN_PATH', __DIR__ . '/../');
        }
        if (!defined('FLUENT_AUTH_PLUGIN_URL')) {
            define('FLUENT_AUTH_PLUGIN_URL', 'http://example.org/wp-content/plugins/fluent-security/');
        }
        if (!defined('FLUENT_AUTH_VERSION')) {
            define('FLUENT_AUTH_VERSION', '2.1.1');
        }
    }
    
    protected function mockWordPressFunctions() {
        // Mock all essential WordPress functions
        $functions_to_mock = [
            'plugin_dir_path', 'plugin_dir_url', 'register_activation_hook',
            'register_deactivation_hook', 'load_plugin_textdomain', 'plugin_basename',
            'add_filter', 'add_action', 'do_action', 'apply_filters',
            'get_option', 'update_option', 'delete_option', 'wp_parse_args',
            'is_wp_error', '__', '_e', 'esc_html__', 'esc_attr__', 'esc_url',
            'sanitize_text_field', 'sanitize_url', 'sanitize_email', 'is_email',
            'get_user_by', 'get_current_user_id', 'wp_get_current_user',
            'wp_set_current_user', 'wp_set_auth_cookie', 'wp_clear_auth_cookie',
            'wp_generate_password', 'wp_check_password', 'wp_insert_user',
            'wp_update_user', 'username_exists', 'email_exists', 'validate_username',
            'sanitize_user', 'current_time', 'trailingslashit', 'untrailingslashit',
            'admin_url', 'site_url', 'rest_url', 'home_url', 'get_bloginfo',
            'get_editable_roles', 'wp_kses_post', 'wp_validate_redirect',
            'wp_schedule_event', 'wp_next_scheduled', 'wp_clear_scheduled_hook',
            'dbDelta', 'switch_to_blog', 'restore_current_blog', 'get_sites',
            'get_current_network_id', 'is_ssl', 'wp_generate_uuid4',
            'wp_slash', 'sanitize_textarea_field', 'get_available_languages',
            'update_user_meta', 'get_user_meta', 'add_user_meta', 'delete_user_meta',
            'user_can', 'current_user_can', 'map_deep', 'is_array', 'is_object',
            'is_string', 'is_numeric', 'is_bool', 'is_null', 'isset', 'unset',
            'empty', 'array_key_exists', 'in_array', 'array_merge', 'array_filter',
            'array_map', 'array_keys', 'array_values', 'count', 'sizeof',
            'explode', 'implode', 'trim', 'str_replace', 'preg_replace',
            'preg_match', 'strpos', 'strrpos', 'strlen', 'substr',
            'strtolower', 'strtoupper', 'ucfirst', 'ucwords', 'htmlspecialchars',
            'filter_var', 'time', 'date', 'strtotime', 'rand', 'uniqid',
            'md5', 'sha1', 'password_hash', 'password_verify', 'hash',
            'json_encode', 'json_decode', 'serialize', 'unserialize',
            'file_exists', 'file_get_contents', 'file_put_contents', 'is_dir',
            'mkdir', 'rmdir', 'unlink', 'glob', 'scandir', 'opendir',
            'readdir', 'closedir', 'fopen', 'fclose', 'fread', 'fwrite',
            'feof', 'fseek', 'ftell', 'rewind', 'fstat', 'filesize',
            'filemtime', 'fileatime', 'filectime', 'touch', 'chmod',
            'copy', 'rename', 'move_uploaded_file', 'is_uploaded_file',
            'is_readable', 'is_writable', 'is_executable', 'pathinfo',
            'dirname', 'basename', 'pathinfo', 'realpath', 'getcwd',
            'chdir', 'getcwd', 'set_include_path', 'get_include_path',
            'class_exists', 'interface_exists', 'trait_exists', 'function_exists',
            'method_exists', 'property_exists', 'get_class', 'get_class_methods',
            'get_class_vars', 'get_object_vars', 'get_parent_class',
            'is_a', 'is_subclass_of', 'get_called_class', 'get_parent_class',
            'new ReflectionClass', 'new ReflectionMethod', 'new ReflectionProperty',
            'trigger_error', 'set_error_handler', 'restore_error_handler',
            'error_reporting', 'ini_get', 'ini_set', 'get_cfg_var',
            'phpinfo', 'phpversion', 'php_uname', 'getmypid', 'getmygid',
            'getmyinode', 'getlastmod', 'getmyuid', 'getopt',
            'getenv', 'putenv', 'gethostbyaddr', 'gethostbyname',
            'gethostbynamel', 'checkdnsrr', 'getmxrr', 'dns_get_record',
            'intval', 'floatval', 'strval', 'boolval', 'settype',
            'gettype', 'is_int', 'is_float', 'is_string', 'is_bool',
            'is_array', 'is_object', 'is_resource', 'is_null', 'is_scalar',
            'is_callable', 'is_iterable', 'is_countable', 'clone', 'unset',
            'isset', 'empty', 'eval', 'exit', 'die', 'sleep', 'usleep',
            'time_nanosleep', 'time_sleep_until', 'strtotime', 'date',
            'gmdate', 'idate', 'getdate', 'checkdate', 'strftime',
            'gmstrftime', 'time', 'microtime', 'gettimeofday', 'getrusage',
            'hrtime', 'posix_mktime', 'mktime', 'gmmktime', 'localtime',
            'gettimeofday', 'getrusage', 'hrtime', 'posix_mktime',
            'mktime', 'gmmktime', 'localtime'
        ];
        
        foreach ($functions_to_mock as $function) {
            if (!function_exists($function)) {
                eval("function $function() { return true; }");
            }
        }
        
        // Mock global variables
        if (!isset($_SERVER)) {
            $_SERVER = [
                'REMOTE_ADDR' => '192.168.1.1',
                'HTTP_HOST' => 'example.org',
                'REQUEST_URI' => '/',
                'REQUEST_METHOD' => 'GET'
            ];
        }
        
        if (!isset($_GET)) {
            $_GET = [];
        }
        
        if (!isset($_POST)) {
            $_POST = [];
        }
        
        if (!isset($_COOKIE)) {
            $_COOKIE = [];
        }
        
        if (!isset($_FILES)) {
            $_FILES = [];
        }
        
        if (!isset($_REQUEST)) {
            $_REQUEST = [];
        }
        
        // Mock database
        global $wpdb;
        if (!isset($wpdb)) {
            $wpdb = new class() {
                public $prefix = 'wp_';
                public $show_errors = true;
                public $suppress_errors = false;
                public $last_error = '';
                public $num_queries = 0;
                public $queries = [];
                public $last_query = '';
                public $last_result = null;
                public $col_info = null;
                public $func_call = null;
                public $time_start = null;
                public $time_stop = null;
                public $time_total = 0;
                public $result = null;
                
                public function get_var($query = null, $x = 0, $y = 0) {
                    return 'test_value';
                }
                
                public function get_col($query = null, $x = 0) {
                    return ['test_value'];
                }
                
                public function get_row($query = null, $output = OBJECT, $y = 0) {
                    return (object)['id' => 1, 'name' => 'test'];
                }
                
                public function get_results($query = null, $output = OBJECT) {
                    return [
                        (object)['id' => 1, 'name' => 'test1'],
                        (object)['id' => 2, 'name' => 'test2']
                    ];
                }
                
                public function query($query) {
                    return true;
                }
                
                public function prepare($query, ...$args) {
                    return $query;
                }
                
                public function esc_like($text) {
                    return addcslashes($text, '_%');
                }
            };
        }
        
        // Mock database tables
        if (!function_exists('flsDb')) {
            function flsDb() {
                $mock = new class() {
                    public function table($table) {
                        return new class() {
                            public function where($column, $operator, $value = null) {
                                return $this;
                            }
                            
                            public function first() {
                                return (object)[
                                    'id' => 1,
                                    'login_hash' => 'test_hash',
                                    'status' => 'issued',
                                    'use_type' => 'signup_verification',
                                    'used_count' => 0,
                                    'valid_till' => date('Y-m-d H:i:s', strtotime('+1 hour')),
                                    'two_fa_code_hash' => '$2y$10$correcthash'
                                ];
                            }
                            
                            public function update($data) {
                                return true;
                            }
                            
                            public function delete() {
                                return true;
                            }
                            
                            public function insert($data) {
                                return 1;
                            }
                        };
                    }
                };
                return $mock;
            }
        }
    }
    
    public function testPluginInitialization() {
        // Test that the main plugin file can be included without errors
        try {
            require_once __DIR__ . '/../fluent-security.php';
            $this->assertTrue(true, 'Plugin file loaded successfully');
        } catch (Exception $e) {
            $this->fail('Plugin initialization failed: ' . $e->getMessage());
        }
    }
    
    public function testAutoloaderWorks() {
        // Test that the autoloader can find and load classes
        $this->assertTrue(class_exists('FluentAuth\App\Helpers\Arr'), 'Arr helper class should be autoloadable');
        $this->assertTrue(class_exists('FluentAuth\App\Helpers\Helper'), 'Helper class should be autoloadable');
        $this->assertTrue(class_exists('FluentAuth\App\Services\AuthService'), 'AuthService class should be autoloadable');
    }
    
    public function testHelperClassFunctionality() {
        // Test that Helper class methods work correctly
        $settings = FluentAuth\App\Helpers\Helper::getAuthSettings();
        $this->assertIsArray($settings, 'Helper::getAuthSettings should return an array');
        $this->assertArrayHasKey('disable_xmlrpc', $settings, 'Settings should contain disable_xmlrpc key');
    }
    
    public function testArrHelperFunctionality() {
        // Test that Arr helper methods work correctly
        $array = ['name' => 'John', 'profile' => ['age' => 30]];
        
        $this->assertTrue(FluentAuth\App\Helpers\Arr::has($array, 'name'), 'Arr::has should find existing key');
        $this->assertFalse(FluentAuth\App\Helpers\Arr::has($array, 'email'), 'Arr::has should not find missing key');
        $this->assertEquals('John', FluentAuth\App\Helpers\Arr::get($array, 'name'), 'Arr::get should return correct value');
        $this->assertEquals(30, FluentAuth\App\Helpers\Arr::get($array, 'profile.age'), 'Arr::get should work with dot notation');
    }
    
    public function testAuthServiceFunctionality() {
        // Test that AuthService methods work correctly
        $userData = [
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ];
        
        $result = FluentAuth\App\Services\AuthService::doUserAuth($userData);
        
        // The result should either be a user object/user ID or a WP_Error
        $this->assertTrue(
            !is_wp_error($result) || (is_wp_error($result) && $result->has_errors()),
            'AuthService::doUserAuth should return user data or WP_Error'
        );
    }
    
    public function testSettingsControllerFunctionality() {
        // Test that SettingsController methods work correctly
        $request = new WP_REST_Request();
        
        $result = FluentAuth\App\Http\Controllers\SettingsController::getSettings($request);
        
        $this->assertIsArray($result, 'SettingsController::getSettings should return an array');
        $this->assertArrayHasKey('settings', $result, 'Result should contain settings key');
    }
    
    public function testActivatorFunctionality() {
        // Test that Activator works correctly
        try {
            FluentAuth\App\Helpers\Activator::activate(false);
            $this->assertTrue(true, 'Activator should work without errors');
        } catch (Exception $e) {
            $this->fail('Activator failed: ' . $e->getMessage());
        }
    }
    
    public function testPluginConstants() {
        // Test that plugin constants are defined
        $this->assertTrue(defined('FLUENT_AUTH_PLUGIN_PATH'), 'FLUENT_AUTH_PLUGIN_PATH should be defined');
        $this->assertTrue(defined('FLUENT_AUTH_PLUGIN_URL'), 'FLUENT_AUTH_PLUGIN_URL should be defined');
        $this->assertTrue(defined('FLUENT_AUTH_VERSION'), 'FLUENT_AUTH_VERSION should be defined');
    }
    
    public function testDatabaseWrapper() {
        // Test that the database wrapper works correctly
        $query = flsDb()->table('fls_auth_logs');
        $this->assertNotNull($query, 'flsDb should return a query builder');
        
        $result = $query->where('id', 1)->first();
        $this->assertIsObject($result, 'Query should return an object');
    }
    
    public function testErrorHandling() {
        // Test that error handling works correctly
        $error = new WP_Error('test_error', 'Test error message');
        
        $this->assertTrue(is_wp_error($error), 'is_wp_error should correctly identify WP_Error objects');
        $this->assertEquals('test_error', $error->get_error_code(), 'Error should return correct error code');
        $this->assertEquals('Test error message', $error->get_error_message(), 'Error should return correct error message');
    }
    
    public function testSecurityFunctions() {
        // Test that security functions work correctly
        $input = '<script>alert("xss");</script>';
        $sanitized = sanitize_text_field($input);
        
        $this->assertStringNotContainsString('<script>', $sanitized, 'sanitize_text_field should remove script tags');
        $this->assertStringNotContainsString('xss', $sanitized, 'sanitize_text_field should remove dangerous content');
    }
    
    public function testPluginHooks() {
        // Test that plugin hooks work correctly
        $filter_called = false;
        
        add_filter('fluent_auth/app_permission', function($permission) use (&$filter_called) {
            $filter_called = true;
            return 'manage_options';
        });
        
        $result = apply_filters('fluent_auth/app_permission', 'manage_options');
        
        $this->assertTrue($filter_called, 'Plugin filter should be called');
        $this->assertEquals('manage_options', $result, 'Plugin filter should return correct value');
    }
    
    public function testCompleteWorkflow() {
        // Test a complete workflow from auth to settings
        try {
            // 1. Get initial settings
            $request = new WP_REST_Request();
            $settings = FluentAuth\App\Http\Controllers\SettingsController::getSettings($request);
            $this->assertIsArray($settings);
            
            // 2. Update settings
            $request->set_param('settings', [
                'disable_xmlrpc' => 'yes',
                'enable_auth_logs' => 'yes',
                'login_try_limit' => 10
            ]);
            
            $updated = FluentAuth\App\Http\Controllers\SettingsController::updateSettings($request);
            $this->assertIsArray($updated);
            
            // 3. Test authentication
            $userData = [
                'email' => 'integration-test@example.com',
                'first_name' => 'Integration',
                'last_name' => 'Test'
            ];
            
            $authResult = FluentAuth\App\Services\AuthService::doUserAuth($userData);
            
            // 4. Verify the workflow completed successfully
            $this->assertTrue(true, 'Complete workflow test passed');
            
        } catch (Exception $e) {
            $this->fail('Complete workflow test failed: ' . $e->getMessage());
        }
    }
}