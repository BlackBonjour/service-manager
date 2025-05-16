<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

final class FooBarFactoryWithoutInterface
{
    public function __invoke(): FooBar
    {
        return new FooBar('foo', 'bar');
    }
}
