<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\AbstractFactory;

use BlackBonjour\ServiceManager\AbstractFactory\ReflectionFactory;
use BlackBonjour\ServiceManager\Exception\ContainerException;
use BlackBonjour\ServiceManager\Exception\NotFoundException;
use BlackBonjourTest\ServiceManager\Asset\ClassWithoutFactory;
use BlackBonjourTest\ServiceManager\Asset\ClassWithoutFactoryAndScalarTypeHint;
use BlackBonjourTest\ServiceManager\Asset\FooBar;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  30.09.2019
 */
class ReflectionFactoryTest extends TestCase
{
    public function testCanCreate(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new ReflectionFactory();

        self::assertTrue($factory->canCreate($container, ClassWithoutFactory::class));
        self::assertFalse($factory->canCreate($container, 'ClassDoesNotExist'));
    }

    public function testInvoke(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())->method('has')->with(FooBar::class)->willReturn(true);
        $container
            ->expects(self::once())
            ->method('get')
            ->with(FooBar::class)
            ->willReturn($this->createMock(FooBar::class));

        $factory = new ReflectionFactory();

        self::assertInstanceOf(ClassWithoutFactory::class, ($factory)($container, ClassWithoutFactory::class));
    }

    public function testInvokeUnknownService(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Unable to create service "%s": Cannot resolve parameter "foo" using type hint "%s"!',
                ClassWithoutFactory::class,
                FooBar::class
            )
        );

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())->method('has')->with(FooBar::class)->willReturn(false);

        $factory = new ReflectionFactory();

        ($factory)($container, ClassWithoutFactory::class);
    }

    public function testInvokeWithNonOptionalScalarParams(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Unable to create service "%s": Cannot resolve parameter "id" to a class or interface!',
                ClassWithoutFactoryAndScalarTypeHint::class
            )
        );

        $container = $this->createMock(ContainerInterface::class);
        $factory   = new ReflectionFactory();

        ($factory)($container, ClassWithoutFactoryAndScalarTypeHint::class);
    }
}
