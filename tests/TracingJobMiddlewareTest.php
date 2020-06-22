<?php
/**
 * Copyright 2020 Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Tests;

use LaravelOpenTracing\Jobs\Middleware\Tracing;
use OpenTracing\Tracer;

class TracingJobMiddlewareTest extends TestCase
{
    public function testHandle()
    {
        $job = new \stdClass();

        (new Tracing)->handle($job, function () {
            /** @var Tracer $tracer */
            $tracer = $this->app->make(Tracer::class);
            $this->assertNotNull($tracer->getActiveSpan()->getContext());
        });
    }
}
