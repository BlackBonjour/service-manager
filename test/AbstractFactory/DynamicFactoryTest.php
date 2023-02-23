<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\AbstractFactory;

use BlackBonjour\ServiceManager\AbstractFactory\DynamicFactory;
use BlackBonjour\ServiceManager\Exception\ContainerException;
use BlackBonjourTest\ServiceManager\Asset\FooBar;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  18.09.2019
 */
class DynamicFactoryTest extends TestCase
{
    public function testCanCreate(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new DynamicFactory();

        self::assertTrue($factory->canCreate($container, FooBar::class));
        self::assertFalse($factory->canCreate($container, self::class));
    }

    public function testInvoke(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new DynamicFactory();

        self::assertInstanceOf(FooBar::class, ($factory)($container, FooBar::class));
    }

    public function testInvokeFails(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(sprintf('Cannot create service "%s"!', self::class));

        $container = $this->createMock(ContainerInterface::class);
        $factory   = new DynamicFactory();

        ($factory)($container, self::class);
    }
}
