<?php
namespace FluentAuth\Tests\Unit;

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase {
    
    protected function setUp(): void {
        parent::setUp();
        
        // Mock basic WordPress functions
        $this->mockWordPressFunctions();
    }
    
    protected function tearDown(): void {
        parent::tearDown();
    }
    
    /**
     * Mock essential WordPress functions for testing
     */
    protected function mockWordPressFunctions() {
        // Mock option functions
        if (!function_exists('get_option')) {
            function get_option($option, $default = false) {
                return $default;
            }
        }
        
        if (!function_exists('update_option')) {
            function update_option($option, $value, $autoload = null) {
                return true;
            }
        }
        
        // Mock sanitization functions
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
        
        // Mock user functions
        if (!function_exists('get_user_by')) {
            function get_user_by($field, $value) {
                return false;
            }
        }
        
        if (!function_exists('wp_get_current_user')) {
            function wp_get_current_user() {
                return new \WP_User();
            }
        }
        
        if (!function_exists('get_current_user_id')) {
            function get_current_user_id() {
                return 0;
            }
        }
        
        // Mock error functions
        if (!function_exists('is_wp_error')) {
            function is_wp_error($thing) {
                return ($thing instanceof \WP_Error);
            }
        }
        
        // Mock i18n functions
        if (!function_exists('__')) {
            function __($text, $domain = 'default') {
                return $text;
            }
        }
        
        if (!function_exists('esc_html__')) {
            function esc_html__($text, $domain = 'default') {
                return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
            }
        }
        
        // Mock utility functions
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
        
        if (!function_exists('add_filter')) {
            function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
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
    }
    
    /**
     * Create a mock WP_REST_Request
     */
    protected function createMockRequest($params = []) {
        $request = $this->getMockBuilder('WP_REST_Request')
            ->onlyMethods(['get_param', 'set_param'])
            ->getMock();
            
        foreach ($params as $key => $value) {
            $request->expects($this->any())
                ->method('get_param')
                ->with($key)
                ->willReturn($value);
        }
        
        return $request;
    }
    
    /**
     * Assert that a WP_Error has specific error code
     */
    protected function assertWpError($error, $code) {
        $this->assertInstanceOf(\WP_Error::class, $error);
        $this->assertEquals($code, $error->get_error_code());
    }
    
    /**
     * Assert that a WP_Error has specific error message
     */
    protected function assertWpErrorMessage($error, $message) {
        $this->assertInstanceOf(\WP_Error::class, $error);
        $this->assertStringContainsString($message, $error->get_error_message());
    }
}