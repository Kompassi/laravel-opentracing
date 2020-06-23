<?php
/**
 * Copyright 2020 Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Clients;

use Illuminate\Support\Arr;
use Jaeger\Reporter\NullReporter;
use Jaeger\Sampler\ConstSampler;
use OpenTracing\Tracer;

class LocalClient extends Client
{
    public function getTracer(): Tracer
    {
        return new \Jaeger\Tracer(
            Arr::get($this->config, 'service_name'),
            new NullReporter(),
            new ConstSampler(1),
            true,
            app('log'),
            null,
            'uber-trace-id',
            'uberctx-',
            'jaeger-debug-id'
        );
    }
}
