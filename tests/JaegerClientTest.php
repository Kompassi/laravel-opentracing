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
use const Jaeger\SAMPLER_TYPE_PROBABILISTIC;
use const Jaeger\SAMPLER_TYPE_RATE_LIMITING;

class JaegerClientTest extends TestCase
{
    public function testDefaultSampler()
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

    public function testProbabilisticSampler()
    {
        $client = new JaegerClient([
            'service_name' => 'test.service',
            'agent' => [
                'host' => 'localhost',
                'port' => 5775,
            ],
            'sampler' => [
                'type' => SAMPLER_TYPE_PROBABILISTIC,
                'param' => 0.1,
            ],
        ]);

        $tracer = $client->getTracer();

        $this->assertInstanceOf(Tracer::class, $tracer);
    }

    public function testRateLimitingSampler()
    {
        $client = new JaegerClient([
            'service_name' => 'test.service',
            'agent' => [
                'host' => 'localhost',
                'port' => 5775,
            ],
            'sampler' => [
                'type' => SAMPLER_TYPE_RATE_LIMITING,
                'param' => 10,
            ],
        ]);

        $tracer = $client->getTracer();

        $this->assertInstanceOf(Tracer::class, $tracer);
    }

    public function testInvalidSampler()
    {
        $client = new JaegerClient([
            'service_name' => 'test.service',
            'agent' => [
                'host' => 'localhost',
                'port' => 5775,
            ],
            'sampler' => [
                'type' => 'invalid',
                'param' => null,
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $client->getTracer();
    }
}
