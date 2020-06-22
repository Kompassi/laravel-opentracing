<?php
/**
 * Copyright 2020 Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Resolvers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;

class QueryTagResolver implements TagResolver
{
    public const DATABASE_TYPE = 'sql';
    public const PEER_SERVICE = 'database';

    /**
     * Resolve appropriate tags from a Query Builder instance.
     *
     * @param Builder $carrier
     * @param array $options
     * @return array
     */
    public function resolve($carrier, array $options = []): array
    {
        $connectionConfig = config('database.connections')[$carrier->getConnection()->getName()];

        $tags = [
            \OpenTracing\Tags\SPAN_KIND => \OpenTracing\Tags\SPAN_KIND_RPC_CLIENT,
            \OpenTracing\Tags\DATABASE_INSTANCE => $carrier->getConnection()->getName(),
            \OpenTracing\Tags\DATABASE_TYPE => self::DATABASE_TYPE,
            \OpenTracing\Tags\DATABASE_USER => $connectionConfig['username'],
            \OpenTracing\Tags\DATABASE_STATEMENT => $carrier->toSql(),
            \OpenTracing\Tags\PEER_ADDRESS => $connectionConfig['host'] . ':' . $connectionConfig['port'],
            \OpenTracing\Tags\PEER_PORT => $connectionConfig['port'],
            \OpenTracing\Tags\PEER_SERVICE => self::PEER_SERVICE,
        ];

        $usedConnection = $carrier->useWritePdo ? 'write' : 'read';

        if (Arr::get($connectionConfig, "{$usedConnection}.username")) {
            $tags[\OpenTracing\Tags\DATABASE_USER] = Arr::get($connectionConfig, "{$usedConnection}.username");
        }

        if (Arr::get($connectionConfig, "{$usedConnection}.port")) {
            $tags[\OpenTracing\Tags\PEER_PORT] = Arr::get($connectionConfig, "{$usedConnection}.port");
        }

        if (Arr::get($connectionConfig, "{$usedConnection}.host") ||
            Arr::get($connectionConfig, "{$usedConnection}.port")) {
            $host = Arr::get($connectionConfig, "{$usedConnection}.host", $connectionConfig['host']);
            $tags[\OpenTracing\Tags\PEER_ADDRESS] = "{$host}:{$tags[\OpenTracing\Tags\PEER_PORT]}";
        }

        return $tags;
    }
}