<?php
/**
 * Copyright 2020 Tais P. Hansen, Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing;

use Illuminate\Bus\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use LaravelOpenTracing\Clients\ClientInterface;
use LaravelOpenTracing\Clients\JaegerClient;
use LaravelOpenTracing\Clients\LocalClient;
use LaravelOpenTracing\Jobs\Middleware\Tracing;
use OpenTracing\GlobalTracer;
use OpenTracing\Span;
use OpenTracing\StartSpanOptions;
use OpenTracing\Tracer;

class TracingServiceProvider extends ServiceProvider
{
    private const CLIENT_MAP = [
        'local' => LocalClient::class,
        'jaeger' => JaegerClient::class,
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $cfgFile = dirname(__DIR__) . '/config/tracing.php';

        $this->mergeConfigFrom($cfgFile, 'tracing');

        $this->registerTracer();

        $this->registerService();

        $this->registerTagResolvers();

        $this->registerTerminatingCallback();

        if (config('tracing.enable_query_tracing')) {
            $this->registerQueryListener();
        }
    }

    public function registerTracer()
    {
        $this->app->singleton(
            Tracer::class,
            function (Application $app) {
                $clientType = $app['config']['tracing.type'] ?: 'local';
                $clientClass = Arr::get(self::CLIENT_MAP, $clientType, self::CLIENT_MAP['local']);

                /** @var ClientInterface $client */
                $client = new $clientClass($app['config']['tracing.clients.' . $clientType]);
                $tracer = $client->getTracer();

                GlobalTracer::set($tracer);

                if ($app['config']['tracing.autostart'] === true) {
                    $span = $tracer->startSpan('root');
                    $this->app->instance(Span::class, $span);
                }

                return $tracer;
            }
        );

        $this->app->alias(Tracer::class, 'opentracing.tracer');
    }

    public function registerService()
    {
        $this->app->singleton(
            TracingService::class,
            static function (Application $app) {
                return new TracingService($app->make(Tracer::class));
            }
        );

        $this->app->alias(TracingService::class, 'opentracing');
    }

    public function registerTagResolvers()
    {
        if (config('tracing.tags.query')) {
            $this->app->bind('opentracing.tags.query', config('tracing.tags.query'));
        }

        if (config('tracing.tags.middleware.request')) {
            $this->app->bind('opentracing.tags.request', config('tracing.tags.middleware.request'));
        }

        if (config('tracing.tags.middleware.response')) {
            $this->app->bind('opentracing.tags.response', config('tracing.tags.middleware.response'));
        }
    }

    public function registerTerminatingCallback()
    {
        $this->app->terminating(
            function () {
                try {
                    app('opentracing')->endAllTraces();
                } catch (\Exception $e) {
                    // Passthrough.
                }

                $this->app->make(Tracer::class)->flush();
            }
        );
    }

    public function registerQueryListener() {
        DB::listen(function(QueryExecuted $query) {
            $endTime = (int) round(microtime(true) * 1000000);
            $duration = (int) round($query->time*1000);

            $scope = \LaravelOpenTracing\Facades\Tracing::beginTrace(
                'db::query',
                [
                    'start_time' => (int) ($endTime - $duration),
                    'finish_span_on_close' => false,
                    'tags' => [
                        'connection' => $query->connectionName,
                        'query' => $query->sql
                    ],
                ]
            );

            $span = $scope->getSpan();
            if (config('app.debug') === true) {
                $bindings = implode(',', $query->bindings);
                $span->setTag('bindings', $bindings);
            }

            \LaravelOpenTracing\Facades\Tracing::endTrace($scope);
            $span->finish($endTime);
        });
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([dirname(__DIR__) . '/config/tracing.php' => config_path('tracing.php')]);

        if (config('tracing.enable_jobs')) {
            app(Dispatcher::class)->pipeThrough([
                Tracing::class,
            ]);
        }
    }

    public function provides()
    {
        return [
            'opentracing', 'opentracing.tracer', 'opentracing.tags.query',
            'opentracing.tags.request', 'opentracing.tags.response'
        ];
    }
}
