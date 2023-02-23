<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

class ClassWithoutFactoryAndScalarTypeHint
{
    public function __construct(
        public readonly int $id,
        public readonly string $foo,
    ) {
    }
}
