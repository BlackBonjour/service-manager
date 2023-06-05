<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  30.09.2019
 */
class ClassWithoutFactory
{
    public function __construct(
        public readonly FooBar $foo,
        public readonly array $bar,
        public readonly int $baz = 123,
    ) {
    }
}
