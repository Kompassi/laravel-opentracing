<?php
/**
 * Copyright 2019 Tais P. Hansen
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Tests;

use LaravelOpenTracing\TracingService;
use LaravelOpenTracing\TracingServiceProvider;
use Mockery;

class TracingServiceProviderTest extends TestCase
{

    public function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testBoot()
    {
        $provider = new TracingServiceProvider($this->app);
        $provider->boot();
    }

    public function testRegister()
    {
        $provider = new TracingServiceProvider($this->app);
        $provider->register();

        $this->assertInstanceOf(TracingService::class, $this->app->make(TracingService::class));
    }
}
