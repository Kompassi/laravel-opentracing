<?php
/**
 * Copyright 2020 Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Resolvers;

use Illuminate\Http\Request;
use const OpenTracing\Tags\HTTP_METHOD;
use const OpenTracing\Tags\HTTP_URL;
use const OpenTracing\Tags\SPAN_KIND;
use const OpenTracing\Tags\SPAN_KIND_RPC_SERVER;

class RequestTagResolver implements TagResolver
{
    /**
     * @param Request $carrier
     * @param array $options
     * @return array
     */
    public function resolve($carrier, array $options = []): array
    {
        return [
            SPAN_KIND => SPAN_KIND_RPC_SERVER,
            HTTP_URL => $carrier->fullUrl(),
            HTTP_METHOD => $carrier->getMethod(),
        ];
    }
}