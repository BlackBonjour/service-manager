<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

final readonly class ClassWithoutDependencies
{
    public function __construct(
        public int $id = 1,
    ) {}
}
