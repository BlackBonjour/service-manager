<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager;

use BlackBonjour\ServiceManager\AbstractFactory\DynamicFactory;
use BlackBonjourTest\ServiceManager\Asset\FooBar;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @author    Erick Dyck <info@erickdyck.de>
 * @since     18.09.2019
 * @copyright Copyright (c) 2019 Erick Dyck
 */
class AbstractFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        self::assertInstanceOf(
            FooBar::class,
            (new DynamicFactory)($this->createMock(ContainerInterface::class), FooBar::class)
        );
    }

    public function testCanCreate(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new DynamicFactory;

        self::assertTrue($factory->canCreate($container, FooBar::class));
        self::assertFalse($factory->canCreate($container, self::class));
    }
}
