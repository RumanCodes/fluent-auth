<?php

namespace FluentAuth\Tests\Integration;

use FluentAuth\Tests\Unit\BaseTestCase;
use FluentAuth\App\Helpers\Helper;
use FluentAuth\App\Helpers\Arr;

class PluginIntegrationTest extends BaseTestCase
{
    public function testHelperClassFunctionality()
    {
        $settings = Helper::getAuthSettings();
        $this->assertIsArray($settings, 'Helper::getAuthSettings should return an array');
    }

    public function testArrHelperFunctionality()
    {
        $array = ['name' => 'John', 'profile' => ['age' => 30]];

        $this->assertTrue(Arr::has($array, 'name'));
        $this->assertFalse(Arr::has($array, 'missing'));
        $this->assertEquals('John', Arr::get($array, 'name'));
    }

    public function testSecurityFunctions()
    {
        $input = '<script>alert("xss");</script>';
        $sanitized = sanitize_text_field($input);

        $this->assertStringNotContainsString('<script>', $sanitized);
    }

    public function testSettingsRoundTrip()
    {
        update_option('__fls_auth_settings', [
            'disable_xmlrpc' => 'yes',
            'enable_auth_logs' => 'yes',
            'login_try_limit' => 10,
            'login_try_timing' => 60,
        ]);

        $settings = Helper::getAuthSettings();
        $this->assertIsArray($settings);
        $this->assertEquals('yes', $settings['disable_xmlrpc']);
        $this->assertEquals(10, $settings['login_try_limit']);
    }

    public function testHelperAndArrInteraction()
    {
        $settings = Helper::getAuthSettings();
        $xmlrpc = Arr::get($settings, 'disable_xmlrpc', 'no');
        $this->assertEquals('no', $xmlrpc);
    }
}
