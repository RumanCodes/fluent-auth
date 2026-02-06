<?php

namespace FluentAuth\Tests\Unit;

class FluentAuthPluginTest extends BaseTestCase
{
    protected $plugin;

    public function setUp(): void
    {
        parent::setUp();
        $this->plugin = new \FluentAuthPlugin();
    }

    public function testPluginInitialization()
    {
        $this->assertInstanceOf(\FluentAuthPlugin::class, $this->plugin);
    }

    public function testActivationHook()
    {
        $result = $this->plugin->activatePlugin();
        $this->assertNull($result);
    }

    public function testDeactivationHook()
    {
        $result = $this->plugin->deactivatePlugin();
        $this->assertNull($result);
    }

    public function testAddContextLinks()
    {
        $actions = [];
        $result = $this->plugin->addContextLinks($actions);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('settings', $result);
        $this->assertArrayHasKey('dashboard_page', $result);
    }

    public function testConstantsDefined()
    {
        $this->assertTrue(defined('FLUENT_AUTH_PLUGIN_PATH'));
        $this->assertTrue(defined('FLUENT_AUTH_PLUGIN_URL'));
        $this->assertTrue(defined('FLUENT_AUTH_VERSION'));
    }

    public function testCanBeInstantiated()
    {
        $plugin = new \FluentAuthPlugin();
        $this->assertInstanceOf(\FluentAuthPlugin::class, $plugin);
    }
}
