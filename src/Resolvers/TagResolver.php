<?php

namespace LaravelOpenTracing\Resolvers;

interface TagResolver
{
    public function resolve($carrier, array $options = []): array;
}
