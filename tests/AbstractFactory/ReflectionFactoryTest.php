<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\AbstractFactory;

use BlackBonjour\ServiceManager\AbstractFactory\ReflectionFactory;
use BlackBonjour\ServiceManager\Exception\ContainerException;
use BlackBonjour\ServiceManager\Exception\NotFoundException;
use BlackBonjourTest\ServiceManager\Asset\ClassWithArrayParameter;
use BlackBonjourTest\ServiceManager\Asset\ClassWithOptionalParameters;
use BlackBonjourTest\ServiceManager\Asset\ClassWithoutConstructor;
use BlackBonjourTest\ServiceManager\Asset\ClassWithoutFactory;
use BlackBonjourTest\ServiceManager\Asset\ClassWithoutFactoryAndScalarTypeHint;
use BlackBonjourTest\ServiceManager\Asset\FooBar;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * Verifies the `ReflectionFactory` class.
 *
 * This test suite verifies that the `ReflectionFactory` can properly create services using reflection and handle various edge cases correctly.
 */
final class ReflectionFactoryTest extends TestCase
{
    /**
     * Verifies that the `ReflectionFactory` can correctly identify which services it can create.
     *
     * The factory should be able to create services that exist as classes but should return false for non-existent classes.
     *
     * @throws Throwable
     */
    public function testCanCreate(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory = new ReflectionFactory();

        self::assertTrue($factory->canCreate($container, ClassWithoutFactory::class));
        self::assertFalse($factory->canCreate($container, 'ClassDoesNotExist'));
    }

    /**
     * Verifies that the `ReflectionFactory` can successfully create a service when all dependencies are available.
     *
     * The factory should be able to create a service by resolving its dependencies through the container.
     *
     * @throws Throwable
     */
    public function testInvoke(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('get')->with(FooBar::class)->willReturn(new FooBar('', ''));
        $container->expects($this->once())->method('has')->with(FooBar::class)->willReturn(true);

        self::assertInstanceOf(
            ClassWithoutFactory::class,
            (new ReflectionFactory())($container, ClassWithoutFactory::class),
        );
    }

    /**
     * Verifies that the `ReflectionFactory` throws an appropriate exception when a dependency cannot be resolved.
     *
     * When a dependency is not available in the container, the factory should throw a `NotFoundException` with a clear message about which parameter could not be resolved.
     *
     * @throws Throwable
     */
    public function testInvokeUnknownService(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Unable to create service "%s": Cannot resolve parameter "foo" using type hint "%s"!',
                ClassWithoutFactory::class,
                FooBar::class,
            ),
        );

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('has')
            ->with(FooBar::class)
            ->willReturn(false);

        (new ReflectionFactory())($container, ClassWithoutFactory::class);
    }

    /**
     * Verifies that the `ReflectionFactory` can create a service without a constructor.
     *
     * The factory should be able to instantiate classes that don't have a constructor.
     *
     * @throws Throwable
     */
    public function testInvokeWithoutConstructor(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $instance = (new ReflectionFactory())($container, ClassWithoutConstructor::class);

        self::assertInstanceOf(ClassWithoutConstructor::class, $instance);
        self::assertEquals('ClassWithoutConstructor', $instance->getIdentifier());
    }

    /**
     * Verifies that the `ReflectionFactory` can create a service with array type hint parameters.
     *
     * The factory should automatically provide an empty array for parameters with array type hints.
     *
     * @throws Throwable
     */
    public function testInvokeWithArrayParameter(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $instance = (new ReflectionFactory())($container, ClassWithArrayParameter::class);

        self::assertEquals(new ClassWithArrayParameter([]), $instance);
    }

    /**
     * Verifies that the `ReflectionFactory` handles scalar type hints appropriately.
     *
     * The factory should throw a `ContainerException` when it encounters a non-optional parameter with a scalar type hint, as these cannot be automatically resolved.
     *
     * @throws Throwable
     */
    public function testInvokeWithNonOptionalScalarParams(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Unable to create service "%s": Cannot resolve parameter "id" to a class or interface!',
                ClassWithoutFactoryAndScalarTypeHint::class,
            ),
        );

        $container = $this->createMock(ContainerInterface::class);

        (new ReflectionFactory())($container, ClassWithoutFactoryAndScalarTypeHint::class);
    }

    /**
     * Verifies that the `ReflectionFactory` can create a service with optional parameters.
     *
     * The factory should use default values for optional parameters when they are not provided.
     *
     * @throws Throwable
     */
    public function testInvokeWithOptionalParameters(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('has')
            ->with(FooBar::class)
            ->willReturn(false);

        self::assertEquals(
            new ClassWithOptionalParameters(),
            (new ReflectionFactory())($container, ClassWithOptionalParameters::class),
        );
    }
}
