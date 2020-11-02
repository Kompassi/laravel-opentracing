<?php
/**
 * Copyright 2020 Tais P. Hansen, Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LaravelOpenTracing\Resolvers\TagResolver;
use LaravelOpenTracing\TracingService;
use OpenTracing\StartSpanOptions;

class Tracing
{
    /**
     * Handle incoming request and start a trace if the request contains a trace context.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var TracingService $service */
        $service = app(TracingService::class);

        $context = null;

        try {
            $context = $service->extractFromHttpRequest($request);
        } catch (Exception $e) {
            Log::warning(
                'Failed getting trace context from request',
                ['exception' => $e, 'header' => $request->header()]
            );
        }

        /** @var TagResolver $requestResolver */
        $requestResolver = app('opentracing.tags.request');
        /** @var TagResolver $responseResolver */
        $responseResolver = app('opentracing.tags.response');

        if ($context !== null) {
            $options = StartSpanOptions::create([
                'child_of' => $context,
                'tags' => $responseResolver ? $requestResolver->resolve($request) : [],
            ]);
        } else {
            $options = StartSpanOptions::create([
                'tags' => $responseResolver ? $requestResolver->resolve($request) : [],
            ]);
        }

        $operationName = 'http.' . (
            $request->route()->getName() ?? strtolower($request->getMethod()) . '.' . $request->decodedPath()
        );

        $scope = $service->beginTrace($operationName, $options);

        try {
            $response = $next($request);

            $responseTags = $responseResolver ? $responseResolver->resolve($response) : [];

            foreach ($responseTags as $key => $value) {
                $scope->getSpan()->setTag($key, $value);
            }

            return $response;
        } finally {
            $service->endTrace($scope);
        }
    }
}
