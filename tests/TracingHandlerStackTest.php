<?php
/**
 * Copyright 2020 Tais P. Hansen, Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use LaravelOpenTracing\TracingHandlerStack;
use LaravelOpenTracing\TracingService;
use Mockery;

class TracingHandlerStackTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function testTraceContextHeadersAreSentInRequests()
    {
        $this->partialMock(TracingService::class, function ($mock) {
            $mock->shouldReceive('getInjectHeaders')->once()
                ->andReturn(['test-trace-id' => 'deadbeefdeadbeef:beefdeadbeefdead:0:1']);
        });

        $container = [];
        $history = Middleware::history($container);

        $handler = new TracingHandlerStack(new MockHandler([
            new Response(200),
        ]));
        $handler->push($history);

        $client = new Client(['handler' => $handler]);

        $client->request('GET', 'http://localhost');

        $this->assertEquals(
            ['deadbeefdeadbeef:beefdeadbeefdead:0:1'],
            $container[0]['request']->getHeader('test-trace-id')
        );
    }
}
