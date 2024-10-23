<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager\AbstractFactory;

use BlackBonjour\ServiceManager\Exception\ContainerException;
use BlackBonjour\ServiceManager\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  18.09.2019
 */
final class DynamicFactory implements AbstractFactoryInterface
{
    /**
     * @inheritDoc
     * @throws ContainerException
     */
    public function __invoke(ContainerInterface $container, string $service, ?array $options = null): mixed
    {
        if ($this->canCreate($container, $service) === false) {
            throw new ContainerException(sprintf('Cannot create service "%s"!', $service));
        }

        $factoryClass = $service . 'Factory';
        $factory      = new $factoryClass();

        assert(
            $factory instanceof FactoryInterface || is_callable($factory),
            sprintf('Dynamic factory "%s" for service "%s" is not callable!', $factoryClass, $service),
        );

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
