<?php
/**
 * Copyright 2020 Tais P. Hansen, Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Tests;

use LaravelOpenTracing\TracingService;
use LaravelOpenTracing\TracingServiceProvider;

class TracingServiceProviderTest extends TestCase
{
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
        $this->assertInstanceOf(TracingService::class, $this->app->make('opentracing'));
    }
}
