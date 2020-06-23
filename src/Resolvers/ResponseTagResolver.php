<?php
/**
 * Copyright 2020 Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Resolvers;

use Illuminate\Http\Response;
use const OpenTracing\Tags\ERROR;
use const OpenTracing\Tags\HTTP_STATUS_CODE;

class ResponseTagResolver implements TagResolver
{
    /**
     * @param Response $carrier
     * @param array $options
     * @return array
     */
    public function resolve($carrier, array $options = []): array
    {
        $tags = [
            HTTP_STATUS_CODE => $carrier->getStatusCode(),
        ];

        if ($carrier->getStatusCode() >= 500) {
            $tags[ERROR] = true;
        }

        return $tags;
    }
}
