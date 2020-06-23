<?php
/**
 * Copyright 2020 Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Tests;

use Illuminate\Support\Facades\DB;
use LaravelOpenTracing\Resolvers\QueryTagResolver;
use const OpenTracing\Tags\DATABASE_INSTANCE;
use const OpenTracing\Tags\DATABASE_STATEMENT;
use const OpenTracing\Tags\DATABASE_TYPE;
use const OpenTracing\Tags\DATABASE_USER;
use const OpenTracing\Tags\PEER_ADDRESS;
use const OpenTracing\Tags\PEER_PORT;
use const OpenTracing\Tags\PEER_SERVICE;
use const OpenTracing\Tags\SPAN_KIND;
use const OpenTracing\Tags\SPAN_KIND_RPC_CLIENT;

class QueryTagResolverTest extends TestCase
{
    protected const BASE_CONFIG = [
        'database.default' => 'mysql',
        'database.connections.mysql' => [
            'driver' => 'mysql',
            'database' => 'test',
            'host' => 'database.host',
            'port' => 9000,
            'username' => 'test_user'
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();

        config(self::BASE_CONFIG);
    }

    public function testResolve()
    {
        $resolver = new QueryTagResolver;

        $tags = $resolver->resolve(DB::table('test'));

        $this->assertEquals(SPAN_KIND_RPC_CLIENT, $tags[SPAN_KIND]);
        $this->assertEquals('mysql', $tags[DATABASE_INSTANCE]);
        $this->assertEquals(QueryTagResolver::DATABASE_TYPE, $tags[DATABASE_TYPE]);
        $this->assertEquals('test_user', $tags[DATABASE_USER]);
        $this->assertEquals('select * from `test`', $tags[DATABASE_STATEMENT]);
        $this->assertEquals('database.host:9000', $tags[PEER_ADDRESS]);
        $this->assertEquals(9000, $tags[PEER_PORT]);
        $this->assertEquals(QueryTagResolver::PEER_SERVICE, $tags[PEER_SERVICE]);
    }

    public function testResolveRead()
    {
        config([
            'database.connections.mysql' => array_merge(self::BASE_CONFIG['database.connections.mysql'], [
                'read' => [
                    'host' => 'database_read.host',
                    'port' => 9001,
                    'username' => 'read_user'
                ],
                'write' => [
                    'host' => 'database_write.host',
                    'port' => 9002,
                    'username' => 'write_user'
                ],
            ]),
        ]);

        $resolver = new QueryTagResolver;

        $query = DB::connection()->table('test');

        $tags = $resolver->resolve($query);

        $this->assertEquals(SPAN_KIND_RPC_CLIENT, $tags[SPAN_KIND]);
        $this->assertEquals('mysql', $tags[DATABASE_INSTANCE]);
        $this->assertEquals(QueryTagResolver::DATABASE_TYPE, $tags[DATABASE_TYPE]);
        $this->assertEquals('read_user', $tags[DATABASE_USER]);
        $this->assertEquals('select * from `test`', $tags[DATABASE_STATEMENT]);
        $this->assertEquals('database_read.host:9001', $tags[PEER_ADDRESS]);
        $this->assertEquals(9001, $tags[PEER_PORT]);
        $this->assertEquals(QueryTagResolver::PEER_SERVICE, $tags[PEER_SERVICE]);
    }

    public function testResolveWrite()
    {
        config([
            'database.connections.mysql' => array_merge(self::BASE_CONFIG['database.connections.mysql'], [
                'read' => [
                    'host' => 'database_read.host',
                    'port' => 9001,
                    'username' => 'read_user'
                ],
                'write' => [
                    'host' => 'database_write.host',
                    'port' => 9002,
                    'username' => 'write_user'
                ],
            ]),
        ]);

        $resolver = new QueryTagResolver;

        $tags = $resolver->resolve(DB::connection()->table('test')->useWritePdo());

        $this->assertEquals(SPAN_KIND_RPC_CLIENT, $tags[SPAN_KIND]);
        $this->assertEquals('mysql', $tags[DATABASE_INSTANCE]);
        $this->assertEquals(QueryTagResolver::DATABASE_TYPE, $tags[DATABASE_TYPE]);
        $this->assertEquals('write_user', $tags[DATABASE_USER]);
        $this->assertEquals('select * from `test`', $tags[DATABASE_STATEMENT]);
        $this->assertEquals('database_write.host:9002', $tags[PEER_ADDRESS]);
        $this->assertEquals(9002, $tags[PEER_PORT]);
        $this->assertEquals(QueryTagResolver::PEER_SERVICE, $tags[PEER_SERVICE]);
    }
}
