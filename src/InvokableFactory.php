<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager;

use BlackBonjour\ServiceManager\Exception\ContainerException;
use Psr\Container\ContainerInterface;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  23.02.2023
 */
class InvokableFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     * @throws ContainerException
     */
    public function __invoke(ContainerInterface $container, string $service, ?array $options = null)
    {
        if ($options === null) {
            return new $service();
        }

        if (array_is_list($options) === false) {
            throw new ContainerException(sprintf('Cannot create service "%s": Invalid options given!', $service));
        }

        return new $service(...$options);
    }
}
