<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

class ClassWithoutDependencies
{
    public function __construct(
        public readonly int $id = 1,
    ) {
    }
}
