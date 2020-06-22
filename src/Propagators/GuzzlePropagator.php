<?php
/**
 * Copyright 2020 Tais P. Hansen, Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Propagators;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use LaravelOpenTracing\TracingService;
use Psr\Http\Message\RequestInterface;
use function GuzzleHttp\choose_handler;

class GuzzlePropagator extends HandlerStack
{
    public function __construct(callable $handler = null)
    {
        parent::__construct($handler ?: choose_handler());

        $this->push(
            Middleware::mapRequest(
                function (RequestInterface $request) {
                    foreach (app(TracingService::class)->getInjectHeaders() as $header => $value) {
                        $request = $request->withHeader($header, $value);
                    }

                    return $request;
                }
            )
        );
    }
}
