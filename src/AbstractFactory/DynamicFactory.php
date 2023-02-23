<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager\AbstractFactory;

use Psr\Container\ContainerInterface;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  18.09.2019
 */
class DynamicFactory implements AbstractFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, string $service, array $options = [])
    {
        $factoryClass = $service . 'Factory';
        $factory      = new $factoryClass();

        return $factory($container, $service, $options);
    }

    /**
     * @inheritDoc
     */
    public function canCreate(ContainerInterface $container, string $service): bool
    {
        return class_exists($service . 'Factory');
    }
}
