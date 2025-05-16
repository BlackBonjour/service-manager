<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

final readonly class ClassWithoutFactoryAndScalarTypeHint
{
    public function __construct(
        public int $id,
        public string $foo,
    ) {}
}
