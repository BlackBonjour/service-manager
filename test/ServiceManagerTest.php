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
use BlackBonjourTest\ServiceManager\Asset\FooBarFactoryWithoutInterface;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

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

    public function testAddAbstractFactoryWithClassString(): void
    {
        $manager = new ServiceManager();
        $manager->addAbstractFactory(DynamicFactory::class);

        self::assertInstanceOf(FooBar::class, $manager[FooBar::class]);
    }

    public function testAddAlias(): void
    {
        // Via ::add methods
        $manager = new ServiceManager();
        $manager->addAlias('configuration', 'config');
        $manager->addService('config', ['foo' => 'bar']);

        self::assertEquals(['foo' => 'bar'], $manager->get('configuration'));
        self::assertEquals(['foo' => 'bar'], $manager['configuration']);

        // Via constructor
        $manager = new ServiceManager(['config' => ['foo' => 'bar']], aliases: ['configuration' => 'config']);

        self::assertEquals(['foo' => 'bar'], $manager->get('configuration'));
        self::assertEquals(['foo' => 'bar'], $manager['configuration']);
    }

    public function testAddFactory(): void
    {
        $manager = new ServiceManager();
        $manager->addFactory(FooBar::class, FooBarFactory::class);

        self::assertInstanceOf(FooBar::class, $manager[FooBar::class]);
    }

    public function testAddFactoryWithClassStringAndWithoutInterface(): void
    {
        $manager = new ServiceManager();
        $manager->addFactory(FooBar::class, FooBarFactoryWithoutInterface::class);

        self::assertInstanceOf(FooBar::class, $manager[FooBar::class]);
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
        $config = ['foo' => 'bar'];
        $manager = new ServiceManager();
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

    public function testCreateServiceWithAlias(): void
    {
        $manager = new ServiceManager();
        $manager->addAlias('foobar-alias', FooBar::class);
        $manager->addFactory(FooBar::class, FooBarFactoryWithOptions::class);

        self::assertInstanceOf(
            FooBar::class,
            $manager->createService('foobar-alias', ['foo' => 'foo', 'bar' => 'bar']),
        );
    }

    public function testCreateServiceWithAliasAndOptions(): void
    {
        $manager = new ServiceManager();
        $manager->addAlias('alias-service', 'original-service');
        $manager->addFactory(
            'original-service',
            static fn($container, $id, $options) => $options['value'] ?? 'default',
        );

        self::assertEquals('custom', $manager->createService('alias-service', ['value' => 'custom']));
    }

    public function testCreateServiceWithAliasException(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Service "foobar-alias" could not be created!');

        $manager = new ServiceManager();
        $manager->addAlias('foobar-alias', FooBar::class);
        $manager->createService('foobar-alias');
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

    public function testHasWithAlias(): void
    {
        $manager = new ServiceManager();
        $manager->addAlias('alias-service', 'original-service');
        $manager->addService('original-service', 'value');

        self::assertTrue($manager->has('alias-service'));
        self::assertTrue($manager->offsetExists('alias-service'));
        self::assertTrue(isset($manager['alias-service']));
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

    public function testRemoveServiceWithAlias(): void
    {
        $manager = new ServiceManager();
        $manager->addAlias('alias-service', 'original-service');
        $manager->addService('original-service', 'value');

        self::assertTrue($manager->has('alias-service'));

        $manager->removeService('alias-service');

        self::assertFalse($manager->has('alias-service'));
        self::assertTrue($manager->has('original-service'));
    }

    public function testRequestingServiceWithInvalidFactory(): void
    {
        $manager = new ServiceManager(
            services: [],
            factories: [FooBar::class => 123],
            abstractFactories: [],
            invokables: [],
        );

        try {
            $manager[FooBar::class];
        } catch (Throwable $t) {
            self::assertInstanceOf(ContainerException::class, $t);
            self::assertInstanceOf(ContainerException::class, $t->getPrevious());
            self::assertEquals(
                sprintf('Factory for service "%s" is invalid!', FooBar::class),
                $t->getPrevious()->getMessage(),
            );
        }
    }
}
