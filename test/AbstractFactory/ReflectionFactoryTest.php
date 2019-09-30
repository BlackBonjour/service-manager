<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\AbstractFactory;

use BlackBonjour\ServiceManager\AbstractFactory\ReflectionFactory;
use BlackBonjourTest\ServiceManager\Asset\ClassWithoutFactory;
use BlackBonjourTest\ServiceManager\Asset\FooBar;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @author    Erick Dyck <info@erickdyck.de>
 * @since     30.09.2019
 * @copyright Copyright (c) 2019 Erick Dyck
 */
class ReflectionFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('has')
            ->willReturnMap([
                [FooBar::class, true],
            ]);

        $container
            ->method('get')
            ->willReturnMap([
                [FooBar::class, $this->createMock(FooBar::class)],
            ]);

        self::assertInstanceOf(
            ClassWithoutFactory::class,
            (new ReflectionFactory)($container, ClassWithoutFactory::class)
        );
    }

    public function testCanCreate(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new ReflectionFactory;

        self::assertTrue($factory->canCreate($container, ClassWithoutFactory::class));
    }
}
