<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager\AbstractFactory;

use BlackBonjour\ServiceManager\Exception\ContainerException;
use BlackBonjour\ServiceManager\Exception\InvalidFactoryException;
use Psr\Container\ContainerInterface;

final class DynamicFactory implements AbstractFactoryInterface
{
    /**
     * @throws ContainerException
     */
    public function __invoke(ContainerInterface $container, string $service, ?array $options = null): mixed
    {
        if ($this->canCreate($container, $service) === false) {
            throw new ContainerException(sprintf('Cannot create service "%s".', $service));
        }

        $factoryClass = $service . 'Factory';
        $factory = new $factoryClass();

        if (is_callable($factory) === false) {
            throw new InvalidFactoryException(
                sprintf('Dynamic factory "%s" for service "%s" is invalid.', $factoryClass, $service),
            );
        }

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
