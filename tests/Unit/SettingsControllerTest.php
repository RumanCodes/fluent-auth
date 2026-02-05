<?php
/**
 * Test for SettingsController class
 */

require_once __DIR__ . '/MockClasses.php';

use PHPUnit\Framework\TestCase;
use FluentAuth\App\Http\Controllers\SettingsController;

class SettingsControllerTest extends TestCase {
    
    protected function setUp(): void {
        // Mock WordPress functions
        $this->mockWordPressFunctions();
        
        // Mock Helper class
        if (!class_exists('FluentAuth\App\Helpers\Helper')) {
            class MockHelper {
                public static function getAuthSettings() {
                    return [
                        'disable_xmlrpc' => 'no',
                        'enable_auth_logs' => 'yes',
                        'login_try_limit' => 5,
                        'login_try_timing' => 30,
                        'auto_delete_logs_day' => 30,
                        'email2fa' => 'no',
                        'email2fa_roles' => ['administrator', 'editor']
                    ];
                }
                
                public static function getUserRoles($keyed = false) {
                    if ($keyed) {
                        return [
                            'administrator' => 'Administrator',
                            'editor' => 'Editor',
                            'subscriber' => 'Subscriber'
                        ];
                    }
                    return [
                        ['id' => 'administrator', 'title' => 'Administrator'],
                        ['id' => 'editor', 'title' => 'Editor'],
                        ['id' => 'subscriber', 'title' => 'Subscriber']
                    ];
                }
                
                public static function getLowLevelRoles() {
                    return [
                        'subscriber' => 'Subscriber'
                    ];
                }
                
                public static function getWpPermissions($keyed = false) {
                    if ($keyed) {
                        return [
                            'manage_options' => 'manage_options',
                            'publish_posts' => 'publish_posts',
                            'read' => 'read'
                        ];
                    }
                    return [
                        ['id' => 'manage_options', 'title' => 'manage_options'],
                        ['id' => 'publish_posts', 'title' => 'publish_posts'],
                        ['id' => 'read', 'title' => 'read']
                    ];
                }
                
                public static function getAuthFormsSettings() {
                    return [
                        'enabled' => 'no',
                        'login_redirects' => 'no',
                        'default_login_redirect' => '',
                        'default_logout_redirect' => '',
                        'redirect_rules' => []
                    ];
                }
                
                public static function getAuthCustomizerSettings() {
                    return [
                        'status' => 'no',
                        'login' => [
                            'banner' => [
                                'title' => 'Welcome',
                                'description' => 'Test Description'
                            ],
                            'form' => [
                                'title' => 'Login',
                                'button_label' => 'Login Button'
                            ]
                        ]
                    ];
                }
                
                public static function formatAuthCustomizerSettings($settings) {
                    return $settings;
                }
                
                public static function getValidatedRedirectUrl($location, $fallback = '') {
                    if (filter_var($location, FILTER_VALIDATE_URL)) {
                        return $location;
                    }
                    return $fallback;
                }
            }
        }
        
        // Mock Arr class
        if (!class_exists('FluentAuth\App\Helpers\Arr')) {
            class MockArr {
                public static function only($array, $keys) {
                    $keys = (array)$keys;
                    $result = [];
                    foreach ($keys as $key) {
                        if (isset($array[$key])) {
                            $result[$key] = $array[$key];
                        }
                    }
                    return $result;
                }
                
                public static function get($array, $key, $default = null) {
                    $keys = explode('.', $key);
                    $value = $array;
                    foreach ($keys as $k) {
                        if (is_array($value) && isset($value[$k])) {
                            $value = $value[$k];
                        } else {
                            return $default;
                        }
                    }
                    return $value;
                }
            }
        }
        
        // Mock ServerModeHandler
        if (!class_exists('FluentAuth\App\Hooks\Handlers\ServerModeHandler')) {
            class MockServerModeHandler {
                public function isEnabled() {
                    return false;
                }
            }
        }
    }
    
    protected function mockWordPressFunctions() {
        // Mock option functions
        if (!function_exists('update_option')) {
            function update_option($option, $value, $autoload = null) {
                return true;
            }
        }
        
        if (!function_exists('get_option')) {
            function get_option($option, $default = false) {
                return $default;
            }
        }
        
        if (!function_exists('is_wp_error')) {
            function is_wp_error($thing) {
                return ($thing instanceof \WP_Error);
            }
        }
        
        if (!function_exists('__')) {
            function __($text, $domain = 'default') {
                return $text;
            }
        }
        
        if (!function_exists('sanitize_text_field')) {
            function sanitize_text_field($string) {
                return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
            }
        }
        
        if (!function_exists('map_deep')) {
            function map_deep($value, $callback) {
                if (is_array($value)) {
                    return array_map('map_deep', $value, $callback);
                }
                return call_user_func($callback, $value);
            }
        }
        
        if (!function_exists('sanitize_url')) {
            function sanitize_url($url) {
                return filter_var($url, FILTER_SANITIZE_URL);
            }
        }
        
        if (!function_exists('wp_check_filetype_and_ext')) {
            function wp_check_filetype_and_ext($filename, $mimes = null, $test_image = false) {
                return [
                    'ext' => 'jpg',
                    'type' => 'image/jpeg'
                ];
            }
        }
        
        if (!function_exists('wp_handle_upload')) {
            function wp_handle_upload($file, $overrides = null) {
                return [
                    'url' => 'http://example.com/uploaded.jpg',
                    'file' => '/tmp/uploaded.jpg'
                ];
            }
        }
        
        if (!function_exists('current_user_can')) {
            function current_user_can($capability) {
                return true;
            }
        }
        
        if (!function_exists('admin_url')) {
            function admin_url($path = '') {
                return 'http://example.org/wp-admin/' . ltrim($path, '/');
            }
        }
        
        if (!function_exists('WP_Filesystem')) {
            function WP_Filesystem() {
                return true;
            }
        }
        
        if (!function_exists('get_plugins')) {
            function get_plugins() {
                return [];
            }
        }
        
        if (!function_exists('is_plugin_active')) {
            function is_plugin_active($plugin) {
                return false;
            }
        }
        
        if (!function_exists('plugins_api')) {
            function plugins_api($action, $args = []) {
                return (object)[
                    'download_link' => 'http://example.com/plugin.zip'
                ];
            }
        }
        
        if (!function_exists('wp_clean_plugins_cache')) {
            function wp_clean_plugins_cache() {
                return true;
            }
        }
        
        if (!function_exists('wp_generate_password')) {
            function wp_generate_password($length = 12, $special_chars = true, $extra_special_chars = false) {
                return 'generated_password';
            }
        }
        
        if (!function_exists('rest_url')) {
            function rest_url($path = '') {
                return 'http://example.org/wp-json/' . ltrim($path, '/');
            }
        }
        
        if (!function_exists('site_url')) {
            function site_url($path = '') {
                return 'http://example.org/' . ltrim($path, '/');
            }
        }
        
        if (!function_exists('get_user_by')) {
            function get_user_by($field, $value) {
                $user = new WP_User();
                $user->ID = 1;
                $user->user_login = 'testuser';
                $user->user_email = 'test@example.com';
                return $user;
            }
        }
        
        if (!function_exists('get_user_meta')) {
            function get_user_meta($user_id, $key = '', $single = false) {
                if ($key === '__flsc_temp_token') {
                    return 'valid_token';
                }
                return '';
            }
        }
        
        if (!function_exists('activate_plugin')) {
            function activate_plugin($plugin, $redirect = '', $network_wide = false, $silent = false) {
                return null;
            }
        }
        
        // Mock global variables
        if (!isset($_FILES)) {
            $_FILES = [];
        }
    }
    
    public function testGetSettings() {
        $request = new WP_REST_Request();
        $result = SettingsController::getSettings($request);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('settings', $result);
        $this->assertArrayHasKey('user_roles', $result);
        $this->assertArrayHasKey('low_level_roles', $result);
        
        // Check settings structure
        $this->assertIsArray($result['settings']);
        $this->assertArrayHasKey('disable_xmlrpc', $result['settings']);
        $this->assertArrayHasKey('enable_auth_logs', $result['settings']);
        
        // Check user roles structure
        $this->assertIsArray($result['user_roles']);
        $this->assertArrayHasKey('administrator', $result['user_roles']);
        
        // Check low level roles structure
        $this->assertIsArray($result['low_level_roles']);
        $this->assertArrayHasKey('subscriber', $result['low_level_roles']);
    }
    
    public function testUpdateSettings() {
        $request = new WP_REST_Request();
        $request->set_param('settings', [
            'disable_xmlrpc' => 'yes',
            'enable_auth_logs' => 'yes',
            'login_try_limit' => 10,
            'login_try_timing' => 60,
            'auto_delete_logs_day' => 15
        ]);
        
        $result = SettingsController::updateSettings($request);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('settings', $result);
        $this->assertArrayHasKey('message', $result);
        
        // Check that numeric values are properly converted
        $this->assertEquals(10, $result['settings']['login_try_limit']);
        $this->assertEquals(60, $result['settings']['login_try_timing']);
        $this->assertEquals(15, $result['settings']['auto_delete_logs_day']);
    }
    
    public function testUpdateSettingsValidationErrors() {
        // Test with missing required fields
        $request = new WP_REST_Request();
        $request->set_param('settings', [
            'enable_auth_logs' => 'yes',
            'login_try_limit' => 0, // Missing required value
            'login_try_timing' => 0, // Missing required value
            'email2fa' => 'yes',
            'email2fa_roles' => [] // Missing required roles
        ]);
        
        $result = SettingsController::updateSettings($request);
        
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('validation_error', $result->get_error_code());
    }
    
    public function testGetAuthFormSettings() {
        $request = new WP_REST_Request();
        $result = SettingsController::getAuthFormSettings($request);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('settings', $result);
        $this->assertArrayHasKey('roles', $result);
        $this->assertArrayHasKey('user_capabilities', $result);
        
        // Check settings structure
        $this->assertIsArray($result['settings']);
        $this->assertArrayHasKey('enabled', $result['settings']);
        $this->assertArrayHasKey('login_redirects', $result['settings']);
        
        // Check roles structure
        $this->assertIsArray($result['roles']);
        $this->assertArrayHasKey('administrator', $result['roles']);
        
        // Check capabilities structure
        $this->assertIsArray($result['user_capabilities']);
        $this->assertArrayHasKey('manage_options', $result['user_capabilities']);
    }
    
    public function testSaveAuthFormSettings() {
        $request = new WP_REST_Request();
        $request->set_param('settings', [
            'enabled' => 'yes'
        ]);
        
        $result = SettingsController::saveAuthFormSettings($request);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('settings', $result);
    }
    
    public function testSaveAuthFormSettingsWithRedirects() {
        $request = new WP_REST_Request();
        $request->set_param('redirect_settings', [
            'login_redirects' => 'yes',
            'default_login_redirect' => 'http://example.com/login',
            'default_logout_redirect' => 'http://example.com/logout',
            'redirect_rules' => [
                [
                    'login' => 'http://example.com/admin-login',
                    'logout' => 'http://example.com/admin-logout',
                    'conditions' => [
                        [
                            'type' => 'role',
                            'value' => 'administrator'
                        ]
                    ]
                ]
            ]
        ]);
        
        $result = SettingsController::saveAuthFormSettings($request);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('yes', $result['settings']['login_redirects']);
    }
    
    public function testGetAuthCustomizerSetting() {
        $request = new WP_REST_Request();
        $result = SettingsController::getAuthCustomizerSetting($request);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('settings', $result);
        $this->assertArrayHasKey('login_form_html', $result);
        
        // Check settings structure
        $this->assertIsArray($result['settings']);
        $this->assertArrayHasKey('login', $result['settings']);
        $this->assertArrayHasKey('banner', $result['settings']['login']);
        $this->assertArrayHasKey('form', $result['settings']['login']);
    }
    
    public function testSaveAuthCustomizerSetting() {
        $request = new WP_REST_Request();
        $request->set_param('settings', [
            'status' => 'yes',
            'login' => [
                'banner' => [
                    'title' => 'Custom Title',
                    'description' => 'Custom Description'
                ],
                'form' => [
                    'title' => 'Custom Form Title',
                    'button_label' => 'Custom Button'
                ]
            ]
        ]);
        
        $result = SettingsController::saveAuthCustomizerSetting($request);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('settings', $result);
        $this->assertEquals('yes', $result['settings']['status']);
    }
    
    public function testUploadImage() {
        // Mock file upload
        $_FILES['file'] = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024
        ];
        
        $request = new WP_REST_Request();
        $result = SettingsController::uploadImage($request);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('media', $result);
        $this->assertIsArray($result['media']);
    }
    
    public function testUploadImageInvalidFile() {
        // Mock invalid file upload
        $_FILES['file'] = [
            'name' => 'test.exe',
            'type' => 'application/x-executable',
            'tmp_name' => '/tmp/test.exe',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024
        ];
        
        $request = new WP_REST_Request();
        $result = SettingsController::uploadImage($request);
        
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('invalid_file_type', $result->get_error_code());
    }
    
    public function testUploadImageNoFile() {
        $_FILES['file'] = [];
        
        $request = new WP_REST_Request();
        $result = SettingsController::uploadImage($request);
        
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('invalid_file', $result->get_error_code());
    }
    
    public function testSaveChildSiteNotServerMode() {
        $request = new WP_REST_Request();
        $request->set_param('site_config', '{"site_url":"http://example.com","callback_url":"http://example.com/callback","site_title":"Test Site"}');
        
        $result = SettingsController::saveChildSite($request);
        
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('invalid_request', $result->get_error_code());
        $this->assertStringContainsString('server mode', $result->get_error_message());
    }
    
    public function testGetChildSitesNotServerMode() {
        $request = new WP_REST_Request();
        $result = SettingsController::getChildSites($request);
        
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('invalid_request', $result->get_error_code());
    }
    
    public function testValidateChildSiteToken() {
        $request = new WP_REST_Request();
        $request->set_param('user_token', 'test_token_1');
        $request->set_param('server_token', 'test_server_token');
        $request->set_param('site_id', 'test_site');
        
        $result = SettingsController::validateChildSiteToken($request);
        
        // This should fail because no sites are configured
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('invalid_request', $result->get_error_code());
    }
    
    public function testInstallPlugin() {
        $request = new WP_REST_Request();
        $request->set_param('plugin', 'fluent-smtp');
        
        $result = SettingsController::installPlugin($request);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('settings_url', $result);
    }
    
    public function testInstallPluginInvalidRequest() {
        $request = new WP_REST_Request();
        $request->set_param('plugin', '');
        
        $result = SettingsController::installPlugin($request);
        
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('invalid_request', $result->get_error_code());
    }
}