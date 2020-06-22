<?php
/**
 * Copyright 2020 Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Tests;

use Illuminate\Http\Request;
use LaravelOpenTracing\Resolvers\RequestTagResolver;
use const OpenTracing\Tags\HTTP_METHOD;
use const OpenTracing\Tags\HTTP_URL;
use const OpenTracing\Tags\SPAN_KIND;
use const OpenTracing\Tags\SPAN_KIND_RPC_SERVER;

class RequestTagResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'localhost']);
    }

    public function testResolve()
    {
        $resolver = new RequestTagResolver;

        $tags = $resolver->resolve(Request::create('/_test/resolver', Request::METHOD_GET));

        $this->assertEquals([
            SPAN_KIND => SPAN_KIND_RPC_SERVER,
            HTTP_URL => 'http://localhost/_test/resolver',
            HTTP_METHOD => Request::METHOD_GET,
        ], $tags);
    }

    public function testResolveAltMethod()
    {
        $resolver = new RequestTagResolver;

        $tags = $resolver->resolve(Request::create('/_test/resolver', Request::METHOD_POST));

        $this->assertEquals([
            SPAN_KIND => SPAN_KIND_RPC_SERVER,
            HTTP_URL => 'http://localhost/_test/resolver',
            HTTP_METHOD => Request::METHOD_POST,
        ], $tags);
    }
}