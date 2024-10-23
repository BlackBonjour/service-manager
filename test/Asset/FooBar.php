<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  18.09.2019
 */
final readonly class FooBar
{
    public function __construct(
        public string $foo,
        public string $bar,
    ) {}
}
