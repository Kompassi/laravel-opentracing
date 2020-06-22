<?php
/**
 * Copyright 2020 Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelOpenTracing\Resolvers\TagResolver;
use LaravelOpenTracing\TracingService;
use OpenTracing\Scope;
use OpenTracing\StartSpanOptions;

/**
 * Wraps a call in a trace span.
 *
 * @param Closure $callable
 * @param string $operationName
 * @param array|StartSpanOptions $options
 * @return mixed
 * @throws Exception
 */
function trace(string $operationName, Closure $callable, $options = null)
{
    return app(TracingService::class)->trace($operationName, $callable, $options);
}

/**
 * Extracts applicable span tags from a query builder.
 *
 * @param Builder $builder
 * @return array
 */
function query_tags(Builder $builder): array
{
    /** @var TagResolver $resolver */
    $resolver = app('opentracing.tags.query');

    return $resolver ? $resolver->resolve($builder) : [];
}

/**
 * Extracts applicable span tags from a request.
 *
 * @param Request $request
 * @return array
 */
function request_tags(Request $request): array
{
    /** @var TagResolver $resolver */
    $resolver = app('opentracing.tags.request');

    return $resolver ? $resolver->resolve($request) : [];
}

/**
 * Extracts applicable span tags from a response.
 *
 * @param Response $request
 * @return array
 */
function response_tags(Response $request): array
{
    /** @var TagResolver $resolver */
    $resolver = app('opentracing.tags.response');

    return $resolver ? $resolver->resolve($request) : [];
}

/**
 * Starts a new trace span.
 *
 * @param $operationName
 * @param array|StartSpanOptions $options
 * @return Scope
 */
function begin_trace($operationName, $options = null): Scope
{
    return app(TracingService::class)->beginTrace($operationName, $options);
}

/**
 * Ends the specified or last started trace span.
 *
 * @param Scope|null $scope
 */
function end_trace(?Scope $scope = null): void
{
    app(TracingService::class)->endTrace($scope);
}