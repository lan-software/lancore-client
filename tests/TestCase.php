<?php

namespace LanSoftware\LanCoreClient\Tests;

use LanSoftware\LanCoreClient\LanCoreServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [LanCoreServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('lancore.enabled', true);
        $app['config']->set('lancore.base_url', 'http://lancore.test');
        $app['config']->set('lancore.internal_url', 'http://lancore.test');
        $app['config']->set('lancore.token', 'test-token');
        $app['config']->set('lancore.app_slug', 'test-app');
        $app['config']->set('lancore.callback_url', 'http://localhost/auth/callback');
        $app['config']->set('lancore.http.timeout', 5);
        $app['config']->set('lancore.http.retries', 2);
        $app['config']->set('lancore.http.retry_delay', 0);
        $app['config']->set('lancore.webhooks.secret', 'test-secret');
    }
}
