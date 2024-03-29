<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  18.09.2019
 */
class FooBar
{
    public function __construct(
        public readonly string $foo,
        public readonly string $bar,
    ) {
    }
}
