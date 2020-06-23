<?php
/**
 * Copyright 2020 Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Tests;

use Illuminate\Http\Response;
use LaravelOpenTracing\Resolvers\ResponseTagResolver;
use const OpenTracing\Tags\ERROR;
use const OpenTracing\Tags\HTTP_STATUS_CODE;

class ResponseTagResolverTest extends TestCase
{
    public function testResolveOk()
    {
        $resolver = new ResponseTagResolver;

        $tags = $resolver->resolve(new Response('', Response::HTTP_OK));

        $this->assertEquals([
            HTTP_STATUS_CODE => Response::HTTP_OK,
        ], $tags);
    }

    public function testResolveNotFound()
    {
        $resolver = new ResponseTagResolver;

        $tags = $resolver->resolve(new Response('', Response::HTTP_NOT_FOUND));

        $this->assertEquals([
            HTTP_STATUS_CODE => Response::HTTP_NOT_FOUND,
        ], $tags);
    }

    public function testResolveServerError()
    {
        $resolver = new ResponseTagResolver;

        $tags = $resolver->resolve(new Response('', Response::HTTP_INTERNAL_SERVER_ERROR));

        $this->assertEquals([
            HTTP_STATUS_CODE => Response::HTTP_INTERNAL_SERVER_ERROR,
            ERROR => true,
        ], $tags);
    }
}