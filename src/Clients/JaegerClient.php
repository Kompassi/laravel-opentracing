<?php
/**
 * Copyright 2020 Tais P. Hansen, Jordan Gosney
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaravelOpenTracing\Clients;

use Illuminate\Support\Arr;
use Jaeger\Reporter\RemoteReporter;
use Jaeger\Sampler\ConstSampler;
use Jaeger\Sampler\ProbabilisticSampler;
use Jaeger\Sampler\RateLimitingSampler;
use Jaeger\Sampler\SamplerInterface;
use Jaeger\Sender\UdpSender;
use Jaeger\Thrift\Agent\AgentClient;
use Jaeger\ThriftUdpTransport;
use Jaeger\Tracer;
use Jaeger\Util\RateLimiter;
use Thrift\Exception\TTransportException;
use Thrift\Protocol\TCompactProtocol;
use Thrift\Transport\TBufferedTransport;
use const Jaeger\SAMPLER_TYPE_CONST;
use const Jaeger\SAMPLER_TYPE_PROBABILISTIC;
use const Jaeger\SAMPLER_TYPE_RATE_LIMITING;

class JaegerClient extends Client
{
    /**
     * @return \OpenTracing\Tracer
     * @throws \Exception
     */
    public function getTracer(): \OpenTracing\Tracer
    {
        $transport = $this->getSender(Arr::get($this->config, 'agent.host'), Arr::get($this->config, 'agent.port'));

        return new Tracer(
            Arr::get($this->config, 'service_name'),
            new RemoteReporter($transport),
            $this->getSampler(),
            true,
            app('log'),
            null,
            'uber-trace-id',
            'uberctx-',
            'jaeger-debug-id'
        );
    }

    /**
     * @return SamplerInterface
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getSampler(): SamplerInterface
    {
        $type = Arr::get($this->config, 'sampler.type');
        $param = Arr::get($this->config, 'sampler.param');

        if ($type === null) {
            $type = SAMPLER_TYPE_CONST;
            $param = true;
        }

        if ($type === SAMPLER_TYPE_CONST) {
            return new ConstSampler((int)$param === 1);
        }

        if ($type === SAMPLER_TYPE_PROBABILISTIC) {
            return new ProbabilisticSampler((float)$param);
        }

        if ($type === SAMPLER_TYPE_RATE_LIMITING) {
            return new RateLimitingSampler(
                $param ?: 0,
                new RateLimiter(
                    app('cache.psr6'),
                    'opentracing.rate.current_balance',
                    'opentracing.rate.last_tick'
                )
            );
        }

        throw new \RuntimeException('Unknown sampler type: ' . $type);
    }

    /**
     * @param string|null $hostname
     * @param int|null $port
     * @return UdpSender
     */
    private function getSender(?string $hostname, ?int $port): UdpSender
    {
        $udp = new ThriftUdpTransport(
            $hostname ?: 'localhost',
            $port ?: 5775
        );

        $transport = new TBufferedTransport($udp, 8192, 8192);
        try {
            $transport->open();
        } catch (TTransportException $e) {
            // Ignored.
        }

        $protocol = new TCompactProtocol($transport);
        $client = new AgentClient($protocol);

        return new UdpSender($client, 8192);
    }
}
