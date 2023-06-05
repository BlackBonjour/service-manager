<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager;

use BlackBonjour\ServiceManager\AbstractFactory\DynamicFactory;
use BlackBonjour\ServiceManager\Exception\ContainerException;
use BlackBonjour\ServiceManager\ServiceManager;
use BlackBonjourTest\ServiceManager\Asset\ClassWithoutDependencies;
use BlackBonjourTest\ServiceManager\Asset\FooBar;
use BlackBonjourTest\ServiceManager\Asset\FooBarFactory;
use BlackBonjourTest\ServiceManager\Asset\FooBarFactoryWithOptions;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;
use TypeError;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  18.09.2019
 */
class ServiceManagerTest extends TestCase
{
    public function testAddAbstractFactory(): void
    {
        $manager = new ServiceManager();
        $manager->addAbstractFactory(new DynamicFactory());

        self::assertInstanceOf(FooBar::class, $manager[FooBar::class]);
    }

    public function testAddFactory(): void
    {
        $manager = new ServiceManager();
        $manager->addFactory(FooBar::class, FooBarFactory::class);

        self::assertInstanceOf(FooBar::class, $manager[FooBar::class]);
    }

    public function testAddFactoryWithException(): void
    {
        $this->expectException(TypeError::class);

        (new ServiceManager())->addFactory(FooBar::class, 123);
    }

    public function testAddInvokable(): void
    {
        $manager = new ServiceManager(invokables: [ClassWithoutDependencies::class]);
        $manager->addInvokable(stdClass::class);

        self::assertInstanceOf(ClassWithoutDependencies::class, $manager->get(ClassWithoutDependencies::class));
        self::assertInstanceOf(stdClass::class, $manager->get(stdClass::class));
    }

    public function testAddService(): void
    {
        $config         = ['foo' => 'bar'];
        $manager        = new ServiceManager();
        $manager['foo'] = 'bar';
        $manager->addService('config', $config);

        self::assertEquals($config, $manager['config']);
        self::assertEquals('bar', $manager['foo']);
    }

    public function testCreateService(): void
    {
        $manager = new ServiceManager();
        $manager->addFactory(FooBar::class, FooBarFactoryWithOptions::class);

        self::assertInstanceOf(FooBar::class, $manager->createService(FooBar::class, ['foo' => 'foo', 'bar' => 'bar']));
    }

    public function testCreateServiceWithException(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(sprintf('Service "%s" could not be created!', FooBar::class));

        (new ServiceManager())->createService(FooBar::class);
    }

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

    public function testHasAbstractFactory(): void
    {
        $manager = new ServiceManager();
        $manager->addAbstractFactory(new DynamicFactory());

        self::assertTrue(isset($manager[FooBar::class]));
        self::assertFalse(isset($manager['config']));
    }

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

    public function testRequestingServiceWithInvalidFactory(): void
    {
        $manager = new ServiceManager([], [FooBar::class => 123], []);

        try {
            $manager[FooBar::class];
        } catch (Throwable $t) {
            self::assertInstanceOf(ContainerException::class, $t);
            self::assertInstanceOf(ContainerException::class, $t->getPrevious());
            self::assertEquals(
                sprintf('Factory for service "%s" is invalid!', FooBar::class),
                $t->getPrevious()->getMessage()
            );
        }
    }
}
