<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Tests;

use MasyukAI\Jnt\JntServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            JntServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('jnt.environment', 'testing');
        config()->set('jnt.api_account', '640826271705595946');
        config()->set('jnt.private_key', '8e88c8477d4e4939859c560192fcafbc');
        config()->set('jnt.customer_code', 'ITTEST0001');
        config()->set('jnt.password', '9C75439FB1FD01EB01861670DD1B949C');
    }
}
