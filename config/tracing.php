<?php

return [
    /*
     * Whether to automatically start a root span when running the application.
     */
    'autostart' => false,

    /*
     * Whether the job middleware should be automatically applied to all jobs.
     */
    'enable_jobs' => false,

    /*
     * Type of client to use for tracing.
     *
     * Currently either 'local' or 'jaeger'.
     */
    'type' => 'local',

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

    /*
     * Client specific settings.
     */
    'clients' => [
        'local' => [],

        /*
         * Jaeger client settings.
         *
         * Generally follows the environment variable conventions of other Jaeger client implementations.
         */
        'jaeger' => [
            /*
             * The name of the current service.
             */
            'service_name' => env('JAEGER_SERVICE_NAME', 'laravel-opentracing'),

            'agent' => [
                /*
                 * The hostname of the agent to send traces to using UDP.
                 */
                'host' => env('JAEGER_AGENT_HOST', 'localhost'),

                /*
                 * The port of the agent to send traces to using UDP.
                 */
                'port' => env('JAEGER_AGENT_PORT', 5775),
            ],

            'sampler' => [
                /*
                 * Type of sampler to use.
                 *
                 * Currently either 'const', 'probabilistic' or 'ratelimiting'.
                 */
                'type' => env('JAEGER_SAMPLER_TYPE', 'const'),

                /*
                 * The sampler parameter.
                 *
                 * Const:
                 *  0   Never sample.
                 *  1   Always sample.
                 *
                 * Probabilistic:
                 *  [0.0, 1.0]   Probability of the trace to be sampled.
                 *
                 * Rate limiting:
                 *  (0.0, ∞)   Maximum number of traces per second.
                 */
                'param' => env('JAEGER_SAMPLER_PARAM', 0),
            ],
        ],
    ],
];
