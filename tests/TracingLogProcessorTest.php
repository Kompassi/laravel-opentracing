<?php
/**
 * Copyright 2020 Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Tests;

use LaravelOpenTracing\Log\Processors\TracingProcessor;
use OpenTracing\Span;
use OpenTracing\SpanContext;
use OpenTracing\Tracer;

class TracingLogProcessorTest extends TestCase
{
    public function testInvoke()
    {
        $this->mock(Tracer::class, function ($mock) {
            $mock->shouldReceive('getActiveSpan')->once()->andReturn(null);
        });
        $this->mock(Span::class, function ($mock) {
            $mockContext = \Mockery::mock(SpanContext::class)
                ->shouldReceive([
                    'getTraceId' => 'deadbeefdeadbeef',
                    'getSpanId' => 'beefdeadbeefdead',
                ])->once()->getMock();

            $mock->shouldReceive([
                'getContext' => $mockContext,
                'getOperationName' => 'test.span',
            ])->once();
        });

        $processor = new TracingProcessor;

        $record = $processor([]);

        $this->assertEquals([
            'extra' => [
                'trace_id' => 'deadbeefdeadbeef',
                'span_id' => 'beefdeadbeefdead',
                'span_name' => 'test.span',
            ],
        ], $record);
    }
}
