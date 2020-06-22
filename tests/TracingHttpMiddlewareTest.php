<?php
/**
 * Copyright 2020 Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Jaeger\SpanContext;
use LaravelOpenTracing\Http\Middleware\Tracing;
use LaravelOpenTracing\Resolvers\TagResolver;
use Mockery\Mock;
use OpenTracing\Tracer;
use const Jaeger\TRACE_ID_HEADER;

class TracingHttpMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/_test/middleware')->name('test.middleware');

        $this->mock(TagResolver::class, function ($mock) {
            $mock->shouldReceive('resolve')->andReturn([]);
        });
        $this->app->bind('opentracing.tags.request', TagResolver::class);
        $this->app->bind('opentracing.tags.response', TagResolver::class);
    }

    public function testHandle()
    {
        $request = Request::create('/_test/middleware', Request::METHOD_GET);
        $request->headers->add([TRACE_ID_HEADER => '1234567890:9876543210:0:1']);
        $request->setRouteResolver(function () {
            return (new Mock())->shouldReceive('name')->once()->andReturn('test.middleware');
        });

        (new Tracing)->handle($request, function () {
            /** @var Tracer $tracer */
            $tracer = $this->app->make(Tracer::class);
            /** @var SpanContext $context */
            $context = $tracer->getActiveSpan()->getContext();
            $this->assertEquals('1234567890', dechex($context->getTraceId()));
            $this->assertEquals('9876543210', dechex($context->getParentId()));
        });
    }
}
