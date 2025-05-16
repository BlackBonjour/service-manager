<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

final readonly class ClassWithArrayParameter
{
    /**
     * @param list<mixed> $config
     */
    public function __construct(
        public array $config,
    ) {}
}
