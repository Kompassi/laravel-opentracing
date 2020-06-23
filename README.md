# OpenTracing for Laravel

[![Total Downloads](https://img.shields.io/packagist/dt/wizofgoz/laravel-opentracing.svg?style=flat-square)](https://packagist.org/packages/wizofgoz/laravel-opentracing)
[![Latest Stable Version](https://img.shields.io/packagist/v/wizofgoz/laravel-opentracing.svg?style=flat-square)](https://packagist.org/packages/wizofgoz/laravel-opentracing)
[![StyleCI](https://github.styleci.io/repos/271911972/shield?style=flat-square&branch=develop)](https://github.styleci.io/repos/271911972)
[![Build Status](https://img.shields.io/travis/wizofgoz/laravel-opentracing/master.svg?style=flat-square)](https://travis-ci.org/wizofgoz/laravel-opentracing)
[![Coverage Status](https://coveralls.io/repos/github/Wizofgoz/laravel-opentracing/badge.svg?branch=master)](https://coveralls.io/github/Wizofgoz/laravel-opentracing?branch=master)

Reference implementation of the OpenTracing API for Laravel including a server-less local tracer for application
logging purposes.

See [OpenTracing](http://opentracing.io/) for more information.

## Supported Clients

Currently supported clients:

- Local: No-op tracer used mainly for adding trace ids to logs.
- Jaeger: open source, end-to-end distributed tracing. See [Jaeger](https://www.jaegertracing.io/) and
    Jonah George's [Jaeger Client PHP](https://github.com/jonahgeorge/jaeger-client-php).

## Installation

Install the latest version using:

```bash
composer require wizofgoz/laravel-opentracing
```

Copy the default configuration file to your application if you want to change it by running the command below. Note
that you have to use `--force` if the file already exists.

```bash
php artisan vendor:publish --provider="LaravelOpenTracing\TracingServiceProvider"
```

## Basic Usage

### Example setup

Example `bootstrap/app.php` file:

```php
<?php

// Create the application.
$app = new \Illuminate\Foundation\Application(realpath(__DIR__ . '/../'));

// Bind important interfaces.
// ...

// Register important providers.
$app->register(\LaravelOpenTracing\TracingServiceProvider::class);

// Enable tracing span context in log messages.
$app->configureMonologUsing(function (\Monolog\Logger $logger) {
    $logger->pushProcessor(new \LaravelOpenTracing\Log\Processors\TracingProcessor());
});

// Return the application.
return $app;
```

### Tracing

To trace a specific process in your application you can wrap the process in a trace closure using the provided facade as shown below. This will take
care of starting a new trace span and closing it again when the closure either returns or throws.

```php
<?php

$items = \LaravelOpenTracing\Facades\Tracing::trace(
    'todo.get_list_items',
    function () {
        return \App\Models\TodoListItem::get();
    }
);
```

Nested traces are also possible like below. It will automatically take care of the span child/parent relations.

```php
<?php

function a() {
    // We don't care about tracing this specifically.
    doSomething();

    \LaravelOpenTracing\Facades\Tracing::trace(
        'app.do_something_else',
        function () {
            doSomethingElse();
        }
    );
}

\LaravelOpenTracing\Facades\Tracing::trace(
    'app.do_stuff',
    function () {
        a();
    }
);
```

If you want to add context information or tags to your spans, it is possible like below.

```php
<?php

$title = 'Make more coffee';

$item = \LaravelOpenTracing\Facades\Tracing::trace(
    'todo.store_item',
    function () use ($title) {
        return \App\Models\TodoListItem::create(['title' => $title]);
    },
    ['tags' => ['title' => $title]]
);
```

Helper functions are provided for all functions available on the facade.

```php
<?php

trace(
    'todo.store_item',
    function () use ($title) {
        return \App\Models\TodoListItem::create(['title' => $title]);
    },
    ['tags' => ['title' => $title]]
);
```

### Common Tags

This package provides resolvers for common tags from a given context such as an Eloquent Query Builder, Request object, or Response object. These can be overridden in configuration like below.
```php
/*
 * Resolvers used for generating tags for spans.
 */
'tags' => [
    'middleware' => [
        'request' => \LaravelOpenTracing\Resolvers\RequestTagResolver::class,
        'response' => \LaravelOpenTracing\Resolvers\ResponseTagResolver::class,
    ],
    'query' => \LaravelOpenTracing\Resolvers\QueryTagResolver::class,
],
```

There are helper functions available for deriving tags from contexts based on the current configuration.

```php
<?php
/** @var \Illuminate\Database\Query\Builder $builder */
$tags = query_tags($builder);

/** @var \Illuminate\Http\Request $request */
$tags = request_tags($request);

/** @var \Illuminate\Http\Response $response */
$tags = response_tags($response);
```

### Tracing Jobs

All jobs can be automatically traced by using the `enable_jobs` boolean in the configuration file or for tracing on certain jobs, attach the provided middleware to the ones you wish to trace.

```php
<?php

// Add to job classes

/**
 * Get the middleware the job should pass through.
 *
 * @return array
 */
public function middleware()
{
    return [new \LaravelOpenTracing\Jobs\Middleware\Tracing];
}
```

### Tracing Across Service Boundaries

Trace contexts can easily be used across services. If your application starts a tracing context, that context can be
carried over HTTP to another service with an OpenTracing compatible implementation.

To automatically accept trace contexts from other services, add the tracing middleware to your application by adding it
to your `app/Http/Kernel.php` file like below:

```php
<?php

class Kernel extends HttpKernel
{
    protected $middleware = [
        \LaravelOpenTracing\Http\Middleware\Tracing::class,
    ];
}
```

Assuming your application uses GuzzleHttp for sending requests to external services, you can use the provided tracing
handler when creating the client like below. This will automatically send the necessary trace context headers with the
HTTP request to the external service.

```php
<?php

new \GuzzleHttp\Client(
    [
        'handler' => new \LaravelOpenTracing\Propagators\GuzzlePropagator(),
        'headers' => [
            'cache-control' => 'no-cache',
            'content-type' => 'application/json',
        ],
        'base_uri' => 'http://localhost/api/myservice',
        'http_errors' => false
    ]
);
```

## Testing

docker run --rm -it -v $(pwd):/app php:7.3-cli-alpine /bin/sh -c 'apk add --no-cache $PHPIZE_DEPS && pecl install xdebug-2.7.2 && cd app && php -dzend_extension=xdebug.so vendor/bin/phpunit'

## About

### Requirements

- PHP 7.3 or newer.

### Contributions

Bugs and feature requests are tracked on [GitHub](https://github.com/Wizofgoz/laravel-opentracing/issues).

### License

OpenTracing for Laravel is licensed under the Apache-2.0 license. See the [LICENSE](LICENSE) file for details.
