<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager;

use BlackBonjour\ServiceManager\AbstractFactory\DynamicFactory;
use BlackBonjour\ServiceManager\Exception\ContainerException;
use BlackBonjour\ServiceManager\Exception\InvalidFactoryException;
use BlackBonjour\ServiceManager\ServiceManager;
use BlackBonjourTest\ServiceManager\Asset\ClassWithoutDependencies;
use BlackBonjourTest\ServiceManager\Asset\FooBar;
use BlackBonjourTest\ServiceManager\Asset\FooBarFactory;
use BlackBonjourTest\ServiceManager\Asset\FooBarFactoryWithOptions;
use BlackBonjourTest\ServiceManager\Asset\FooBarFactoryWithoutInterface;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

/**
 * Verifies the `ServiceManager` class.
 *
 * This test suite verifies that the `ServiceManager` can properly manage services, factories, and abstract factories and handle various edge cases correctly.
 */
final class ServiceManagerTest extends TestCase
{
    /**
     * Verifies that the `ServiceManager` can add and use an abstract factory class string.
     *
     * The service manager should be able to instantiate and use an abstract factory when given its class name as a string.
     *
     * @throws Throwable
     */
    public function testAddAbstractFactoryWithClassString(): void
    {
        $manager = new ServiceManager();
        $manager->addAbstractFactory(DynamicFactory::class);

        self::assertInstanceOf(FooBar::class, $manager[FooBar::class]);
    }

    /**
     * Verifies that the `ServiceManager` can add and use an abstract factory instance.
     *
     * The service manager should be able to use an abstract factory to create services that weren't explicitly registered.
     *
     * @throws Throwable
     */
    public function testAddAbstractFactoryWithInstance(): void
    {
        $manager = new ServiceManager();
        $manager->addAbstractFactory(new DynamicFactory());

        self::assertInstanceOf(FooBar::class, $manager[FooBar::class]);
    }

    /**
     * Verifies that the `ServiceManager` can add and use a factory without implementing `FactoryInterface`.
     *
     * The service manager should be able to use a factory that doesn't implement the interface `FactoryInterface` but is still invokable.
     *
     * @throws Throwable
     */
    public function testAddFactoryWithoutInterface(): void
    {
        $manager = new ServiceManager();
        $manager->addFactory(FooBar::class, FooBarFactoryWithoutInterface::class);

        self::assertInstanceOf(FooBar::class, $manager[FooBar::class]);
    }

    /**
     * Verifies that the `ServiceManager` can add and use a factory implementing `FactoryInterface`.
     *
     * The service manager should be able to use a factory that implements the interface `FactoryInterface` to create services.
     *
     * @throws Throwable
     */
    public function testAddFactoryWithInterface(): void
    {
        $manager = new ServiceManager();
        $manager->addFactory(FooBar::class, FooBarFactory::class);

        self::assertInstanceOf(FooBar::class, $manager[FooBar::class]);
    }

    /**
     * Verifies that the `ServiceManager` can add and use invokable services.
     *
     * The service manager should be able to register classes as invokable services and instantiate them when requested.
     *
     * @throws Throwable
     */
    public function testAddInvokable(): void
    {
        $manager = new ServiceManager(invokables: [ClassWithoutDependencies::class]);
        $manager->addInvokable(stdClass::class);

        self::assertInstanceOf(ClassWithoutDependencies::class, $manager->get(ClassWithoutDependencies::class));
        self::assertInstanceOf(stdClass::class, $manager->get(stdClass::class));
    }

    /**
     * Verifies that the `ServiceManager` can add and retrieve services.
     *
     * The service manager should be able to store and retrieve services using both method calls and array access syntax.
     *
     * @throws Throwable
     */
    public function testAddService(): void
    {
        $manager = new ServiceManager();
        $manager['foo'] = 'bar';
        $manager->addService('config', ['foo' => 'bar']);

        self::assertEquals(['foo' => 'bar'], $manager['config']);
        self::assertEquals('bar', $manager['foo']);
    }

    /**
     * Verifies that the `ServiceManager` can create services with options.
     *
     * The service manager should be able to create services using factories and pass options to those factories.
     *
     * @throws Throwable
     */
    public function testCreateService(): void
    {
        $manager = new ServiceManager();
        $manager->addFactory(FooBar::class, FooBarFactoryWithOptions::class);

        self::assertInstanceOf(FooBar::class, $manager->createService(FooBar::class, ['foo' => 'foo', 'bar' => 'bar']));
    }

    /**
     * Verifies that the `ServiceManager` throws an appropriate exception when a service cannot be created.
     *
     * When a service cannot be created, the service manager should throw a `ContainerException` with a clear message about which service could not be created.
     *
     * @throws Throwable
     */
    public function testCreateServiceWithException(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(sprintf('Service "%s" could not be created!', FooBar::class));

        $manager = new ServiceManager();
        $manager->createService(FooBar::class);
    }

    /**
     * Verifies that the `ServiceManager` can retrieve services using different methods.
     *
     * The service manager should be able to retrieve services using the `get` method, the `offsetGet` method, and array access syntax.
     *
     * @throws Throwable
     */
    public function testGet(): void
    {
        $manager = new ServiceManager();
        $manager->addService('config', 123);
        $manager->addFactory(FooBar::class, FooBarFactory::class);

        self::assertEquals(123, $manager->get('config'));
        self::assertEquals(123, $manager->offsetGet('config'));
        self::assertEquals(123, $manager['config']);

        self::assertInstanceOf(FooBar::class, $manager->get(FooBar::class));
        self::assertInstanceOf(FooBar::class, $manager->offsetGet(FooBar::class));
        self::assertInstanceOf(FooBar::class, $manager[FooBar::class]);
    }

    /**
     * Verifies that the `ServiceManager` can check if services exist using different methods.
     *
     * The service manager should be able to check if services exist using the `has` method, the `offsetExists` method, and `isset` with array access syntax.
     *
     * @throws Throwable
     */
    public function testHas(): void
    {
        $manager = new ServiceManager();
        $manager->addService(FooBar::class, 123);

        self::assertTrue($manager->has(FooBar::class));
        self::assertTrue($manager->offsetExists(FooBar::class));
        self::assertTrue(isset($manager[FooBar::class]));
        self::assertFalse($manager->has('config'));
        self::assertFalse($manager->offsetExists('config'));
        self::assertFalse(isset($manager['config']));
    }

    /**
     * Verifies that the `ServiceManager` can check if services can be created by abstract factories.
     *
     * The service manager should be able to check if a service can be created by an abstract factory using `isset` with array access syntax.
     *
     * @throws Throwable
     */
    public function testHasAbstractFactory(): void
    {
        $manager = new ServiceManager();
        $manager->addAbstractFactory(new DynamicFactory());

        self::assertTrue(isset($manager[FooBar::class]));
        self::assertFalse(isset($manager['config']));
    }

    /**
     * Verifies that the `ServiceManager` can remove services using different methods.
     *
     * The service manager should be able to remove services using the `removeService` method and `unset` with array access syntax.
     *
     * @throws Throwable
     */
    public function testRemoveService(): void
    {
        $manager = new ServiceManager();
        $manager->addService('config', []);
        $manager->addService('foo', 'bar');
        $manager->addService('bar', 'baz');

        unset($manager['config']);
        self::assertFalse($manager->has('config'));

        $manager->removeService('foo');
        self::assertFalse(isset($manager['foo']));

        self::assertTrue(isset($manager['bar']));
    }

    /**
     * Verifies that the `ServiceManager` throws an appropriate exception when a factory is invalid.
     *
     * When a factory is not a callable or a string representing a class name, the service manager should throw a `ContainerException` with a nested
     * `InvalidFactoryException` that has a clear message about which factory is invalid.
     *
     * @throws Throwable
     */
    public function testRequestingServiceWithInvalidFactory(): void
    {
        $manager = new ServiceManager(
            services: [],
            factories: [FooBar::class => 123],
            abstractFactories: [],
            invokables: [],
        );

        $this->expectException(ContainerException::class);

        try {
            $manager[FooBar::class];
        } catch (Throwable $t) {
            // Verify the previous exception before re-throwing
            self::assertInstanceOf(InvalidFactoryException::class, $t->getPrevious());
            self::assertEquals(
                sprintf('Factory for service "%s" is invalid!', FooBar::class),
                $t->getPrevious()->getMessage(),
            );

            throw $t; // Re-throw for expectException to catch
        }
    }
}
