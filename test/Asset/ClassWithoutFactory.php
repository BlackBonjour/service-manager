<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

final readonly class ClassWithoutFactory
{
    public function __construct(
        public FooBar $foo,
        public array $bar,
        public int $baz = 123,
    ) {}
}
