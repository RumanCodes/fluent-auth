<?php
/**
 * Test for Helper class
 */

require_once __DIR__ . '/MockClasses.php';

use PHPUnit\Framework\TestCase;
use FluentAuth\App\Helpers\Helper;

class HelperTest extends TestCase {
    
    protected function setUp(): void {
        // Mock WordPress functions
        $this->mockWordPressFunctions();
    }
    
    protected function mockWordPressFunctions() {
        // Mock option functions
        if (!function_exists('get_option')) {
            function get_option($option, $default = false) {
                $options = [
                    '__fls_auth_settings' => [
                        'disable_xmlrpc' => 'no',
                        'enable_auth_logs' => 'yes',
                        'login_try_limit' => 5,
                        'login_try_timing' => 30,
                        'auto_delete_logs_day' => 30
                    ],
                    'users_can_register' => 1,
                    'default_role' => 'subscriber'
                ];
                return $options[$option] ?? $default;
            }
        }
        
        if (!function_exists('update_option')) {
            function update_option($option, $value, $autoload = null) {
                return true;
            }
        }
        
        if (!function_exists('get_editable_roles')) {
            function get_editable_roles() {
                return [
                    'administrator' => [
                        'name' => 'Administrator',
                        'capabilities' => ['manage_options' => true]
                    ],
                    'editor' => [
                        'name' => 'Editor',
                        'capabilities' => ['publish_posts' => true]
                    ],
                    'subscriber' => [
                        'name' => 'Subscriber',
                        'capabilities' => ['read' => true]
                    ]
                ];
            }
        }
        
        if (!function_exists('wp_parse_args')) {
            function wp_parse_args($args, $defaults = '') {
                if (is_object($args)) {
                    $r = get_object_vars($args);
                } elseif (is_array($args)) {
                    $r = $args;
                } else {
                    parse_str($args, $r);
                }
                
                if (is_array($defaults)) {
                    return array_merge($defaults, $r);
                }
                
                return $r;
            }
        }
        
        if (!function_exists('apply_filters')) {
            function apply_filters($tag, $value, ...$args) {
                return $value;
            }
        }
        
        if (!function_exists('sanitize_text_field')) {
            function sanitize_text_field($string) {
                return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
            }
        }
        
        if (!function_exists('sanitize_url')) {
            function sanitize_url($url) {
                return filter_var($url, FILTER_SANITIZE_URL);
            }
        }
        
        if (!function_exists('current_time')) {
            function current_time($type, $gmt = 0) {
                if ($type === 'timestamp') {
                    return time();
                }
                if ($type === 'mysql') {
                    return date('Y-m-d H:i:s');
                }
                return date($type);
            }
        }
        
        if (!function_exists('wp_kses_post')) {
            function wp_kses_post($string) {
                return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
            }
        }
        
        if (!function_exists('wp_validate_redirect')) {
            function wp_validate_redirect($location, $fallback = '') {
                if (filter_var($location, FILTER_VALIDATE_URL)) {
                    return $location;
                }
                return $fallback;
            }
        }
        
        if (!function_exists('get_bloginfo')) {
            function get_bloginfo($show = '') {
                $info = [
                    'name' => 'Test Blog',
                    'description' => 'Test Description'
                ];
                return $info[$show] ?? '';
            }
        }
        
        // Mock database functions
        if (!function_exists('flsDb')) {
            function flsDb() {
                $mock = new class() {
                    public function table($table) {
                        return new class() {
                            public function where($column, $operator, $value = null) {
                                return $this;
                            }
                            
                            public function delete() {
                                return true;
                            }
                            
                            public function update($data) {
                                return true;
                            }
                            
                            public function first() {
                                return (object)['valid_till' => date('Y-m-d H:i:s', strtotime('+1 hour'))];
                            }
                        };
                    }
                };
                return $mock;
            }
        }
        
        // Mock user functions
        if (!function_exists('get_user_by')) {
            function get_user_by($field, $value) {
                return false;
            }
        }
    }
    
    public function testGetAuthSettings() {
        $settings = Helper::getAuthSettings();
        
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('disable_xmlrpc', $settings);
        $this->assertArrayHasKey('enable_auth_logs', $settings);
        $this->assertArrayHasKey('login_try_limit', $settings);
        $this->assertEquals('no', $settings['disable_xmlrpc']);
        $this->assertEquals('yes', $settings['enable_auth_logs']);
        $this->assertEquals(5, $settings['login_try_limit']);
    }
    
    public function testGetAppPermission() {
        $permission = Helper::getAppPermission();
        $this->assertEquals('manage_options', $permission);
    }
    
    public function testGetUserRoles() {
        // Test keyed array
        $roles = Helper::getUserRoles(true);
        $this->assertIsArray($roles);
        $this->assertArrayHasKey('administrator', $roles);
        $this->assertArrayHasKey('editor', $roles);
        $this->assertArrayHasKey('subscriber', $roles);
        
        // Test unkeyed array
        $roles = Helper::getUserRoles(false);
        $this->assertIsArray($roles);
        $this->assertIsArray($roles[0]);
        $this->assertArrayHasKey('id', $roles[0]);
        $this->assertArrayHasKey('title', $roles[0]);
    }
    
    public function testGetLowLevelRoles() {
        $roles = Helper::getLowLevelRoles();
        $this->assertIsArray($roles);
        $this->assertArrayHasKey('subscriber', $roles);
        $this->assertArrayNotHasKey('administrator', $roles);
        $this->assertArrayNotHasKey('editor', $roles);
    }
    
    public function testGetWpPermissions() {
        // Test keyed array
        $permissions = Helper::getWpPermissions(true);
        $this->assertIsArray($permissions);
        $this->assertArrayHasKey('manage_options', $permissions);
        $this->assertArrayHasKey('publish_posts', $permissions);
        $this->assertArrayHasKey('read', $permissions);
        
        // Test unkeyed array
        $permissions = Helper::getWpPermissions(false);
        $this->assertIsArray($permissions);
        $this->assertIsArray($permissions[0]);
        $this->assertArrayHasKey('id', $permissions[0]);
        $this->assertArrayHasKey('title', $permissions[0]);
    }
    
    public function testGetSetting() {
        // Test existing setting
        $setting = Helper::getSetting('disable_xmlrpc');
        $this->assertEquals('no', $setting);
        
        // Test non-existing setting with default
        $setting = Helper::getSetting('non_existing', 'default_value');
        $this->assertEquals('default_value', $setting);
    }
    
    public function testGetIp() {
        // Mock server variables
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        
        $ip = Helper::getIp();
        $this->assertEquals('192.168.1.1', $ip);
        
        // Test with anonymization
        $anonymized = Helper::getIp(true);
        $this->assertStringContainsString('192', $anonymized);
    }
    
    public function testLoadView() {
        $data = ['title' => 'Test Title'];
        
        // This should not throw an exception even if the view file doesn't exist
        $result = Helper::loadView('nonexistent_template', $data);
        $this->assertIsString($result);
    }
    
    public function testCleanUpLogs() {
        // This should not throw an exception
        $result = Helper::cleanUpLogs();
        $this->assertNull($result);
    }
    
    public function testGetSocialAuthSettings() {
        $settings = Helper::getSocialAuthSettings();
        
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('enabled', $settings);
        $this->assertArrayHasKey('enable_google', $settings);
        $this->assertArrayHasKey('google_client_id', $settings);
        $this->assertEquals('no', $settings['enabled']);
        $this->assertEquals('no', $settings['enable_google']);
    }
    
    public function testGetAuthFormsSettings() {
        $settings = Helper::getAuthFormsSettings();
        
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('enabled', $settings);
        $this->assertArrayHasKey('login_redirects', $settings);
        $this->assertEquals('no', $settings['enabled']);
        $this->assertEquals('no', $settings['login_redirects']);
    }
    
    public function testSetAndGetLoginMedia() {
        Helper::setLoginMedia('api');
        $media = Helper::getLoginMedia();
        $this->assertEquals('api', $media);
    }
    
    public function testGetAuthCustomizerSettings() {
        $settings = Helper::getAuthCustomizerSettings();
        
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('login', $settings);
        $this->assertArrayHasKey('signup', $settings);
        $this->assertArrayHasKey('status', $settings);
        
        // Test login section
        $this->assertArrayHasKey('banner', $settings['login']);
        $this->assertArrayHasKey('form', $settings['login']);
        
        // Test banner section
        $this->assertArrayHasKey('title', $settings['login']['banner']);
        $this->assertArrayHasKey('description', $settings['login']['banner']);
    }
    
    public function testFormatAuthCustomizerSettings() {
        $settings = [
            'login' => [
                'banner' => [
                    'title' => 'Test Title <script>alert(1)</script>',
                    'description' => 'Test Description',
                    'hidden' => true
                ],
                'form' => [
                    'title' => 'Form Title',
                    'button_label' => 'Login',
                    'button_color' => '#ff0000',
                    'background_image' => 'http://example.com/image.jpg',
                    'background_color' => '#ffffff'
                ]
            ]
        ];
        
        $formatted = Helper::formatAuthCustomizerSettings($settings);
        
        $this->assertIsArray($formatted);
        $this->assertStringNotContainsString('<script>', $formatted['login']['banner']['title']);
        $this->assertEquals('Test Title', $formatted['login']['banner']['title']);
        $this->assertTrue($formatted['login']['banner']['hidden']);
        $this->assertEquals('http://example.com/image.jpg', $formatted['login']['form']['background_image']);
    }
    
    public function testGetValidatedRedirectUrl() {
        // Test valid URL
        $result = Helper::getValidatedRedirectUrl('http://example.com', '/fallback');
        $this->assertEquals('http://example.com', $result);
        
        // Test invalid URL
        $result = Helper::getValidatedRedirectUrl('invalid-url', '/fallback');
        $this->assertEquals('/fallback', $result);
    }
}