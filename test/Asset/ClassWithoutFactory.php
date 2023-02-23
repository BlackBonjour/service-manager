<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  30.09.2019
 */
class ClassWithoutFactory
{
    private $foo;
    private $bar;
    private $baz;

    public function __construct(FooBar $foo, array $bar, int $baz = 123)
    {
        $this->foo = $foo;
        $this->bar = $bar;
        $this->baz = $baz;
    }
}
