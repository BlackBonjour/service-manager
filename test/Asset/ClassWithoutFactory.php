<?php
declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

/**
 * @author    Erick Dyck <info@erickdyck.de>
 * @since     30.09.2019
 * @copyright Copyright (c) 2019 Erick Dyck
 */
class ClassWithoutFactory
{
    private $foo;
    private $bar;

    public function __construct(FooBar $foo, array $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
