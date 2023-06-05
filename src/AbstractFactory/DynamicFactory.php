<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager\AbstractFactory;

use BlackBonjour\ServiceManager\Exception\ContainerException;
use Psr\Container\ContainerInterface;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  18.09.2019
 */
class DynamicFactory implements AbstractFactoryInterface
{
    /**
     * @throws ContainerException
     */
    public function __invoke(ContainerInterface $container, string $service, ?array $options = null)
    {
        if ($this->canCreate($container, $service) === false) {
            throw new ContainerException(sprintf('Cannot create service "%s"!', $service));
        }

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
