<?php
declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

use BlackBonjour\ServiceManager\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * @author    Erick Dyck <info@erickdyck.de>
 * @since     18.09.2019
 * @package   BlackBonjourTest\ServiceManager\Asset
 * @copyright Copyright (c) 2019 Erick Dyck
 */
class FooBarFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, string $service, array $options = [])
    {
        return new FooBar('foo', 'bar');
    }
}
