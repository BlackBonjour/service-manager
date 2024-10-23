<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  30.09.2019
 */
final readonly class ClassWithoutFactory
{
    public function __construct(
        public FooBar $foo,
        public array $bar,
        public int $baz = 123,
    ) {}
}
