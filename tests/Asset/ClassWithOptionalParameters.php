<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

final readonly class ClassWithOptionalParameters
{
    public function __construct(
        public string $name = 'default',
        public int $value = 42,
        public ?FooBar $fooBar = null,
    ) {}
}
