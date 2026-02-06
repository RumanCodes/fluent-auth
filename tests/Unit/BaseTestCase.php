<?php

namespace FluentAuth\Tests\Unit;

use FluentAuth\App\Helpers\Activator;
use FluentAuth\App\Helpers\Helper;

class BaseTestCase extends \WP_UnitTestCase
{
    public static function wpSetUpBeforeClass($factory)
    {
        Activator::activate(false);
    }

    public static function wpTearDownAfterClass()
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fls_auth_logs");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fls_login_hashes");
    }

    public function setUp(): void
    {
        parent::setUp();
        Helper::resetStatics();
    }

    public function assertWpErrorWithCode($error, $code)
    {
        $this->assertWPError($error);
        $this->assertEquals($code, $error->get_error_code());
    }

    public function assertWpErrorMessage($error, $message)
    {
        $this->assertWPError($error);
        $this->assertStringContainsString($message, $error->get_error_message());
    }
}
