<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

final class ClassWithoutConstructor
{
    public function getIdentifier(): string
    {
        return 'ClassWithoutConstructor';
    }
}
