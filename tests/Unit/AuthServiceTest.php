<?php
/**
 * Test for AuthService class
 */

require_once __DIR__ . '/MockClasses.php';

use PHPUnit\Framework\TestCase;
use FluentAuth\App\Services\AuthService;

class AuthServiceTest extends TestCase {
    
    protected function setUp(): void {
        // Mock WordPress functions
        $this->mockWordPressFunctions();
    }
    
    protected function mockWordPressFunctions() {
        // Mock user functions
        if (!function_exists('get_current_user_id')) {
            function get_current_user_id() {
                return 0; // Not logged in
            }
        }
        
        if (!function_exists('is_email')) {
            function is_email($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            }
        }
        
        if (!function_exists('get_user_by')) {
            function get_user_by($field, $value) {
                if ($field === 'email' && $value === 'existing@example.com') {
                    $user = new WP_User();
                    $user->ID = 1;
                    $user->user_login = 'existinguser';
                    $user->user_email = 'existing@example.com';
                    $user->first_name = '';
                    $user->last_name = '';
                    $user->user_url = '';
                    $user->description = '';
                    return $user;
                }
                return false;
            }
        }
        
        if (!function_exists('apply_filters')) {
            function apply_filters($tag, $value, ...$args) {
                if ($tag === 'fluent_auth/signup_enabled') {
                    return true; // Allow signup
                }
                if ($tag === 'fluent_auth/user_role') {
                    return 'subscriber';
                }
                return $value;
            }
        }
        
        if (!function_exists('get_option')) {
            function get_option($option, $default = false) {
                $options = [
                    'users_can_register' => 1,
                    'default_role' => 'administrator'
                ];
                return $options[$option] ?? $default;
            }
        }
        
        if (!function_exists('wp_generate_password')) {
            function wp_generate_password($length = 12, $special_chars = true, $extra_special_chars = false) {
                return 'generated_password_' . $length;
            }
        }
        
        if (!function_exists('sanitize_user')) {
            function sanitize_user($username, $strict = false) {
                return preg_replace('/[^a-z0-9_]/i', '', strtolower($username));
            }
        }
        
        if (!function_exists('username_exists')) {
            function username_exists($username) {
                return $username === 'existinguser' ? 1 : false;
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
        
        if (!function_exists('wp_insert_user')) {
            function wp_insert_user($userdata) {
                if ($userdata['user_login'] === 'error_user') {
                    return new WP_Error('registration_failed', 'Registration failed');
                }
                return 123; // Return user ID
            }
        }
        
        if (!function_exists('wp_update_user')) {
            function wp_update_user($userdata) {
                return true;
            }
        }
        
        if (!function_exists('wp_clear_auth_cookie')) {
            function wp_clear_auth_cookie() {
                return true;
            }
        }
        
        if (!function_exists('wp_set_current_user')) {
            function wp_set_current_user($id, $name = '') {
                return true;
            }
        }
        
        if (!function_exists('wp_set_auth_cookie')) {
            function wp_set_auth_cookie($user_id, $remember = false, $secure = '') {
                return true;
            }
        }
        
        if (!function_exists('is_ssl')) {
            function is_ssl() {
                return false;
            }
        }
        
        if (!function_exists('wp_generate_uuid4')) {
            function wp_generate_uuid4() {
                return 'test-uuid-4-string';
            }
        }
        
        if (!function_exists('wp_slash')) {
            function wp_slash($value) {
                if (is_array($value)) {
                    return array_map('wp_slash', $value);
                }
                return addslashes($value);
            }
        }
        
        if (!function_exists('sanitize_text_field')) {
            function sanitize_text_field($string) {
                return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
            }
        }
        
        if (!function_exists('sanitize_textarea_field')) {
            function sanitize_textarea_field($string) {
                return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
            }
        }
        
        if (!function_exists('sanitize_url')) {
            function sanitize_url($url) {
                return filter_var($url, FILTER_SANITIZE_URL);
            }
        }
        
        if (!function_exists('wp_check_password')) {
            function wp_check_password($password, $hash, $user_id = '') {
                return $password === 'correct_token';
            }
        }
        
        if (!function_exists('current_time')) {
            function current_time($type, $gmt = 0) {
                if ($type === 'timestamp') {
                    return time();
                }
                return date('Y-m-d H:i:s');
            }
        }
        
        if (!function_exists('do_action')) {
            function do_action($tag, ...$args) {
                return null;
            }
        }
        
        if (!function_exists('update_user_meta')) {
            function update_user_meta($user_id, $meta_key, $meta_value, $prev_value = '') {
                return true;
            }
        }
        
        if (!function_exists('get_available_languages')) {
            function get_available_languages() {
                return ['en_US'];
            }
        }
        
        if (!function_exists('validate_username')) {
            function validate_username($username) {
                return preg_match('/^[a-z0-9_]+$/i', $username);
            }
        }
        
        // Mock global variables
        if (!isset($_COOKIE)) {
            $_COOKIE = [];
        }
        
        // Mock database functions
        if (!function_exists('flsDb')) {
            function flsDb() {
                $mock = new class() {
                    public function table($table) {
                        return new class() {
                            private $data;
                            
                            public function __construct() {
                                $this->data = [
                                    'fls_login_hashes' => [
                                        (object)[
                                            'id' => 1, 
                                            'login_hash' => 'valid_hash', 
                                            'status' => 'issued', 
                                            'use_type' => 'signup_verification', 
                                            'used_count' => 0, 
                                            'valid_till' => date('Y-m-d H:i:s', strtotime('+1 hour')), 
                                            'two_fa_code_hash' => '$2y$10$correcthash'
                                        ]
                                    ]
                                ];
                            }
                            
                            public function where($column, $operator, $value = null) {
                                return $this;
                            }
                            
                            public function first() {
                                $table = 'fls_login_hashes';
                                if (isset($this->data[$table]) && !empty($this->data[$table])) {
                                    return $this->data[$table][0];
                                }
                                return null;
                            }
                            
                            public function update($data) {
                                return true;
                            }
                        };
                    }
                };
                return $mock;
            }
        }
        
        // Mock Helper functions
        if (!class_exists('FluentAuth\App\Helpers\Helper')) {
            class MockHelper {
                public static function setLoginMedia($media) {
                    // Do nothing for testing
                }
            }
        }
        
        if (!class_exists('FluentAuth\App\Helpers\Arr')) {
            class MockArr {
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
    }
    
    public function testDoUserAuthSuccess() {
        $userData = [
            'email' => 'newuser@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ];
        
        $result = AuthService::doUserAuth($userData, 'google');
        
        // Should return a user object or user ID on success
        $this->assertNotInstanceOf(WP_Error::class, $result);
    }
    
    public function testDoUserAuthAlreadyLoggedIn() {
        // Mock logged in user
        if (!function_exists('get_current_user_id')) {
            function get_current_user_id() {
                return 1; // Logged in
            }
        }
        
        $userData = ['email' => 'test@example.com'];
        
        $result = AuthService::doUserAuth($userData);
        
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('already_logged_in', $result->get_error_code());
    }
    
    public function testDoUserAuthInvalidEmail() {
        $userData = ['email' => 'invalid-email'];
        
        $result = AuthService::doUserAuth($userData);
        
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('invalid_email', $result->get_error_code());
    }
    
    public function testDoUserAuthExistingUser() {
        $userData = ['email' => 'existing@example.com'];
        
        $result = AuthService::doUserAuth($userData);
        
        $this->assertNotInstanceOf(WP_Error::class, $result);
    }
    
    public function testDoUserAuthSignupDisabled() {
        // Mock signup disabled
        if (!function_exists('apply_filters')) {
            function apply_filters($tag, $value, ...$args) {
                if ($tag === 'fluent_auth/signup_enabled') {
                    return false;
                }
                return $value;
            }
        }
        
        $userData = ['email' => 'newuser@example.com'];
        
        $result = AuthService::doUserAuth($userData);
        
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('signup_disabled', $result->get_error_code());
    }
    
    public function testMakeLoginSuccess() {
        $user = new WP_User();
        $user->ID = 1;
        $user->user_login = 'testuser';
        $user->user_email = 'test@example.com';
        
        $result = AuthService::makeLogin($user);
        
        $this->assertNotInstanceOf(WP_Error::class, $result);
    }
    
    public function testMakeLoginUserNotFound() {
        $result = AuthService::makeLogin(999); // Non-existent user ID
        
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('user_not_found', $result->get_error_code());
    }
    
    public function testSetStateToken() {
        $token = AuthService::setStateToken();
        
        $this->assertIsString($token);
        $this->assertEquals(32, strlen($token)); // MD5 hash length
    }
    
    public function testGetStateToken() {
        // Set a token first
        $_COOKIE['fs_auth_state'] = 'test_token_123';
        
        if (!class_exists('FluentAuth\App\Helpers\Arr')) {
            class MockArr {
                public static function get($array, $key, $default = null) {
                    return $array[$key] ?? $default;
                }
            }
        }
        
        $token = AuthService::getStateToken();
        
        $this->assertEquals('test_token_123', $token);
    }
    
    public function testRegisterNewUserSuccess() {
        $user_id = AuthService::registerNewUser('newuser', 'newuser@example.com', 'password123', [
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
        
        $this->assertIsInt($user_id);
        $this->assertGreaterThan(0, $user_id);
    }
    
    public function testRegisterNewUserValidationError() {
        // This should trigger validation errors
        $user_id = AuthService::registerNewUser('', 'invalid-email', 'password', ['__validated' => true]);
        
        $this->assertInstanceOf(WP_Error::class, $user_id);
    }
    
    public function testCheckUserRegDataErrors() {
        // Test valid data
        $errors = AuthService::checkUserRegDataErrors('validuser', 'valid@example.com');
        
        $this->assertInstanceOf(WP_Error::class, $errors);
        $this->assertFalse($errors->has_errors());
        
        // Test invalid username
        $errors = AuthService::checkUserRegDataErrors('', 'valid@example.com');
        $this->assertTrue($errors->has_errors());
        $this->assertStringContainsString('username', $errors->get_error_message());
        
        // Test invalid email
        $errors = AuthService::checkUserRegDataErrors('validuser', 'invalid-email');
        $this->assertTrue($errors->has_errors());
        $this->assertStringContainsString('email', $errors->get_error_message());
    }
    
    public function testVerifyTokenHashSuccess() {
        $result = AuthService::verifyTokenHash('valid_hash', 'correct_token');
        
        $this->assertTrue($result);
    }
    
    public function testVerifyTokenHashInvalidCode() {
        $result = AuthService::verifyTokenHash('valid_hash', 'wrong_token');
        
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('invalid_verification_code', $result->get_error_code());
    }
    
    public function testVerifyTokenHashInvalidHash() {
        $result = AuthService::verifyTokenHash('invalid_hash', 'correct_token');
        
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('invalid_verification_code', $result->get_error_code());
    }
}