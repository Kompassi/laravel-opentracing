<?php
/**
 * Copyright 2020 Tais P. Hansen, Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Clients;

use OpenTracing\Tracer;

interface ClientInterface
{
    /**
     * @return Tracer
     */
    public function getTracer(): Tracer;
}
