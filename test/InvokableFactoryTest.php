<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager;

use BlackBonjour\ServiceManager\Exception\ContainerException;
use BlackBonjour\ServiceManager\InvokableFactory;
use BlackBonjourTest\ServiceManager\Asset\FooBar;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  23.02.2023
 */
class InvokableFactoryTest extends TestCase
{
    public function testInvokeWithInvalidOptions(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(sprintf('Cannot create service "%s": Invalid options given!', FooBar::class));

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())->method('get');
        $container->expects(self::never())->method('has');

        $factory = new InvokableFactory();

        ($factory)($container, FooBar::class, ['foo' => 'test-string-1', 'bar' => 'test-string-2']);
    }

    public function testInvokeWithOptions(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())->method('get');
        $container->expects(self::never())->method('has');

        $factory = new InvokableFactory();
        $service = ($factory)($container, FooBar::class, ['test-string-1', 'test-string-2']);

        self::assertInstanceOf(FooBar::class, $service);
        self::assertEquals('test-string-1', $service->foo);
        self::assertEquals('test-string-2', $service->bar);
    }

    public function testInvokeWithoutOptions(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())->method('get');
        $container->expects(self::never())->method('has');

        $factory = new InvokableFactory();

        self::assertInstanceOf(stdClass::class, ($factory)($container, stdClass::class));
    }
}
