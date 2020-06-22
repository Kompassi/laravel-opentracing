<?php
/**
 * Copyright 2020 Tais P. Hansen, Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Jobs\Middleware;

use LaravelOpenTracing\TracingService;
use OpenTracing\StartSpanOptions;
use OpenTracing\Tracer;

/**
 * Class for wrapping Laravel job processing in a trace span.
 *
 * This enables automatic tracing of all processed jobs when using queue workers.
 *
 * @see https://laravel.com/docs/queues
 */
class Tracing
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param array|StartSpanOptions|null $options
     */
    public function __construct($options = null)
    {
        $this->options = $options;
    }

    /**
     * @param object $job
     * @param \Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle($job, \Closure $next)
    {
        $res = app(TracingService::class)->trace(
            'job.' . strtolower(str_replace('\\', '.', get_class($job))),
            static function () use ($next, $job) {
                return $next($job);
            },
            $this->options
        );
        app(Tracer::class)->flush();
        return $res;
    }
}
