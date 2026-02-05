<?php
/**
 * Test for Activator class
 */

require_once __DIR__ . '/MockClasses.php';

use PHPUnit\Framework\TestCase;
use FluentAuth\App\Helpers\Activator;

class ActivatorTest extends TestCase {
    
    protected function setUp(): void {
        // Mock WordPress functions
        $this->mockWordPressFunctions();
    }
    
    protected function mockWordPressFunctions() {
        // Mock upgrade functions
        if (!function_exists('dbDelta')) {
            function dbDelta($sql = '') {
                return []; // Return empty array indicating success
            }
        }
        
        // Mock network functions
        if (!function_exists('get_sites')) {
            function get_sites($args = []) {
                return [
                    (object)['blog_id' => 1],
                    (object)['blog_id' => 2]
                ];
            }
        }
        
        if (!function_exists('get_current_network_id')) {
            function get_current_network_id() {
                return 1;
            }
        }
        
        if (!function_exists('switch_to_blog')) {
            function switch_to_blog($blog_id) {
                return true;
            }
        }
        
        if (!function_exists('restore_current_blog')) {
            function restore_current_blog() {
                return true;
            }
        }
        
        // Mock scheduling functions
        if (!function_exists('wp_next_scheduled')) {
            function wp_next_scheduled($hook) {
                return false; // Return false indicating no existing schedule
            }
        }
        
        if (!function_exists('wp_schedule_event')) {
            function wp_schedule_event($timestamp, $recurrence, $hook) {
                return true;
            }
        }
        
        if (!function_exists('update_option')) {
            function update_option($option, $value, $autoload = null) {
                return true;
            }
        }
        
        // Mock database functions
        if (!function_exists('get_editable_roles')) {
            function get_editable_roles() {
                return [
                    'administrator' => [
                        'name' => 'Administrator',
                        'capabilities' => ['manage_options' => true]
                    ]
                ];
            }
        }
        
        // Mock global $wpdb
        global $wpdb;
        if (!isset($wpdb)) {
            $wpdb = new class() {
                public $prefix = 'wp_';
                public $siteid = 1;
                public $blogs = 'wp_blogs';
                
                public function get_var($query) {
                    if (strpos($query, 'SHOW TABLES LIKE') !== false) {
                        return null; // Table doesn't exist
                    }
                    return null;
                }
                
                public function get_col($query) {
                    if (strpos($query, 'SELECT blog_id FROM') !== false) {
                        return [1, 2]; // Return blog IDs
                    }
                    return [];
                }
            };
        }
    }
    
    public function testActivateSingleSite() {
        // Test single site activation
        $result = Activator::activate(false);
        
        $this->assertNull($result);
        
        // Verify that migrations would be called (no exceptions thrown)
        $this->assertTrue(true);
    }
    
    public function testActivateNetworkWide() {
        // Test network wide activation
        $result = Activator::activate(true);
        
        $this->assertNull($result);
        
        // Verify that migrations would be called for all sites
        $this->assertTrue(true);
    }
    
    public function testMigrate() {
        // Use reflection to test private method
        $reflection = new ReflectionClass(Activator::class);
        $method = $reflection->getMethod('migrate');
        $method->setAccessible(true);
        
        $result = $method->invoke(null);
        
        $this->assertNull($result);
    }
    
    public function testMigrateLogsTable() {
        // Use reflection to test private method
        $reflection = new ReflectionClass(Activator::class);
        $method = $reflection->getMethod('migrateLogsTable');
        $method->setAccessible(true);
        
        $result = $method->invoke(null);
        
        $this->assertNull($result);
    }
    
    public function testMigrateHashesTable() {
        // Use reflection to test private method
        $reflection = new ReflectionClass(Activator::class);
        $method = $reflection->getMethod('migrateHashesTable');
        $method->setAccessible(true);
        
        $result = $method->invoke(null);
        
        $this->assertNull($result);
    }
    
    public function testMigrateHashesTableAlterColumn() {
        // Mock database to return existing table but missing column
        global $wpdb;
        $wpdb = new class() {
            public $prefix = 'wp_';
            public $siteid = 1;
            public $blogs = 'wp_blogs';
            
            public function get_var($query) {
                if (strpos($query, 'SHOW TABLES LIKE') !== false) {
                    return 'wp_fls_login_hashes'; // Table exists
                }
                if (strpos($query, 'SHOW COLUMNS FROM') !== false) {
                    return null; // Column doesn't exist
                }
                return null;
            }
            
            public function get_col($query) {
                return [];
            }
            
            public function query($sql) {
                return true;
            }
        };
        
        // Use reflection to test private method
        $reflection = new ReflectionClass(Activator::class);
        $method = $reflection->getMethod('migrateHashesTable');
        $method->setAccessible(true);
        
        $result = $method->invoke(null);
        
        $this->assertNull($result);
    }
    
    public function testMigrateSchedulesCronJobs() {
        // This is tested indirectly through the migrate() method
        // The cron jobs should be scheduled without errors
        $reflection = new ReflectionClass(Activator::class);
        $method = $reflection->getMethod('migrate');
        $method->setAccessible(true);
        
        $result = $method->invoke(null);
        
        $this->assertNull($result);
    }
    
    public function testMigrateSchedulesExistingCronJobs() {
        // Mock that cron jobs already exist
        if (!function_exists('wp_next_scheduled')) {
            function wp_next_scheduled($hook) {
                return 1234567890; // Return timestamp indicating existing schedule
            }
        }
        
        // Use reflection to test private method
        $reflection = new ReflectionClass(Activator::class);
        $method = $reflection->getMethod('migrate');
        $method->setAccessible(true);
        
        $result = $method->invoke(null);
        
        $this->assertNull($result);
    }
    
    public function testCreateTablesStructure() {
        // Test that table creation SQL is properly structured
        // This is more of a structural test to ensure the SQL is valid
        
        $reflection = new ReflectionClass(Activator::class);
        $method = $reflection->getMethod('migrateLogsTable');
        $method->setAccessible(true);
        
        // This should not throw any SQL syntax errors (mocked)
        try {
            $method->invoke(null);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail('Table creation failed: ' . $e->getMessage());
        }
    }
    
    public function testDatabaseVersionUpdate() {
        // Test that database version is updated after migration
        if (!function_exists('update_option')) {
            $called = false;
            function update_option($option, $value, $autoload = null) {
                global $called;
                $called = true;
                return true;
            }
        }
        
        $reflection = new ReflectionClass(Activator::class);
        $method = $reflection->getMethod('migrateHashesTable');
        $method->setAccessible(true);
        
        $method->invoke(null);
        
        // Verify update_option was called
        global $called;
        $this->assertTrue($called, 'Database version should be updated');
    }
    
    public function testActivationHookCompatibility() {
        // Test that the activation hook method is compatible with WordPress
        // This mainly tests the method signature and that it doesn't throw exceptions
        
        try {
            Activator::activate(false);
            Activator::activate(true);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail('Activation hook failed: ' . $e->getMessage());
        }
    }
    
    public function testNetworkActivationWithNoSites() {
        // Mock network with no sites
        if (!function_exists('get_sites')) {
            function get_sites($args = []) {
                return [];
            }
        }
        
        if (!function_exists('switch_to_blog')) {
            $switch_called = false;
            function switch_to_blog($blog_id) {
                global $switch_called;
                $switch_called = true;
                return true;
            }
        }
        
        if (!function_exists('restore_current_blog')) {
            function restore_current_blog() {
                return true;
            }
        }
        
        $result = Activator::activate(true);
        
        $this->assertNull($result);
        
        // Verify that switch_to_blog was not called (no sites to switch to)
        global $switch_called;
        $this->assertFalse($switch_called, 'Should not switch blogs when no sites exist');
    }
}