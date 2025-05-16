<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\AbstractFactory;

use BlackBonjour\ServiceManager\AbstractFactory\DynamicFactory;
use BlackBonjour\ServiceManager\Exception\ContainerException;
use BlackBonjourTest\ServiceManager\Asset\FooBar;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * Verifies the `DynamicFactory` class.
 *
 * This test suite verifies that the `DynamicFactory` can properly create services dynamically and handle various edge cases correctly.
 */
final class DynamicFactoryTest extends TestCase
{
    /**
     * Verifies that the `DynamicFactory` can correctly identify which services it can create.
     *
     * The factory should be able to create services that exist as instantiable classes but should return false for non-instantiable classes.
     *
     * @throws Throwable
     */
    public function testCanCreate(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory = new DynamicFactory();

        self::assertTrue($factory->canCreate($container, FooBar::class));
        self::assertFalse($factory->canCreate($container, self::class));
    }

    /**
     * Verifies that the `DynamicFactory` can successfully create a service.
     *
     * The factory should be able to dynamically instantiate a class without requiring any dependencies.
     *
     * @throws Throwable
     */
    public function testInvoke(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory = new DynamicFactory();

        self::assertInstanceOf(FooBar::class, ($factory)($container, FooBar::class));
    }

    /**
     * Verifies that the `DynamicFactory` throws an appropriate exception when it cannot create a service.
     *
     * When a class cannot be instantiated, the factory should throw a `ContainerException` with a clear message about which service could not be created.
     *
     * @throws Throwable
     */
    public function testInvokeFails(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(sprintf('Cannot create service "%s"!', self::class));

        $container = $this->createMock(ContainerInterface::class);
        $factory = new DynamicFactory();

        ($factory)($container, self::class);
    }
}
