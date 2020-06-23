<?php
/**
 * Copyright 2020 Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Facades;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use OpenTracing\Scope;
use OpenTracing\SpanContext;
use OpenTracing\StartSpanOptions;

/**
 * @method static mixed trace(string $operationName, \Closure $callable, null|array|StartSpanOptions $options = null)
 * @method static Scope beginTrace(string $operationName, null|array|StartSpanOptions $options = null)
 * @method static void endTrace(?Scope $scope = null)
 * @method static array getInjectHeaders()
 * @method static SpanContext|null extractFromHttpRequest(Request $request)
 *
 * @see \LaravelOpenTracing\TracingService
 */
class Tracing extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'opentracing';
    }
}
