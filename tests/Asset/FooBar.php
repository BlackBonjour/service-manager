<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

final readonly class FooBar
{
    public function __construct(
        public string $foo,
        public string $bar,
    ) {}
}
