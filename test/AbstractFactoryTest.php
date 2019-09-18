<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager;

use BlackBonjour\ServiceManager\AbstractFactory;
use BlackBonjourTest\ServiceManager\Asset\FooBar;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @author    Erick Dyck <info@erickdyck.de>
 * @since     18.09.2019
 * @package   BlackBonjourTest\ServiceManager
 * @copyright Copyright (c) 2019 Erick Dyck
 * @covers    \BlackBonjour\ServiceManager\AbstractFactory
 */
class AbstractFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        self::assertInstanceOf(
            FooBar::class,
            (new AbstractFactory)($this->createMock(ContainerInterface::class), FooBar::class)
        );
    }

    public function testCanCreate(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new AbstractFactory;

        self::assertTrue($factory->canCreate($container, FooBar::class));
        self::assertFalse($factory->canCreate($container, self::class));
    }
}