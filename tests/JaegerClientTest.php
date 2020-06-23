<?php
/**
 * Copyright 2020 Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Tests;

use LaravelOpenTracing\Clients\JaegerClient;
use OpenTracing\Tracer;

class JaegerClientTest extends TestCase
{
    public function testGetTracer()
    {
        $client = new JaegerClient([
            'service_name' => 'test.service',
            'agent' => [
                'host' => 'localhost',
                'port' => 5775,
            ],
            'sampler' => [
                'type' => null, // default should be const
                'param' => null,
            ],
        ]);

        $tracer = $client->getTracer();

        $this->assertInstanceOf(Tracer::class, $tracer);
    }
}
