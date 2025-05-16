<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager;

use BlackBonjour\ServiceManager\Exception\ContainerException;
use BlackBonjour\ServiceManager\InvokableFactory;
use BlackBonjourTest\ServiceManager\Asset\FooBar;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Throwable;

/**
 * Verifies the `InvokableFactory` class.
 *
 * This test suite verifies that the `InvokableFactory` can properly create services with and without constructor parameters.
 */
final class InvokableFactoryTest extends TestCase
{
    /**
     * Verifies that the factory can create a service without constructor parameters.
     *
     * The factory should be able to create a service without any parameters when none are provided.
     *
     * @throws Throwable
     */
    public function testInvokeWithoutOptions(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())->method('get');
        $container->expects(self::never())->method('has');

        /** @phpstan-ignore-next-line */
        self::assertInstanceOf(stdClass::class, (new InvokableFactory())($container, stdClass::class));
    }

    /**
     * Verifies that the `InvokableFactory` throws an appropriate exception when given invalid options.
     *
     * When options are provided as an associative array instead of a sequential array, the factory should throw a `ContainerException` with a clear error message.
     *
     * @throws Throwable
     */
    public function testInvokeWithInvalidOptions(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(sprintf('Cannot create service "%s": Invalid options given.', FooBar::class));

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())->method('get');
        $container->expects(self::never())->method('has');

        /** @phpstan-ignore-next-line */
        (new InvokableFactory())($container, FooBar::class, ['foo' => 'test-string-1', 'bar' => 'test-string-2']);
    }

    /**
     * Verifies that the `InvokableFactory` can create a service with constructor parameters.
     *
     * The factory should be able to create a service and pass constructor parameters when they are provided as a sequential array.
     *
     * @throws Throwable
     */
    public function testInvokeWithOptions(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())->method('get');
        $container->expects(self::never())->method('has');

        /** @phpstan-ignore-next-line */
        $service = (new InvokableFactory())($container, FooBar::class, ['test-string-1', 'test-string-2']);

        self::assertInstanceOf(FooBar::class, $service);
        self::assertEquals('test-string-1', $service->foo);
        self::assertEquals('test-string-2', $service->bar);
    }
}
