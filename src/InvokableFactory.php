<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager;

use Psr\Container\ContainerInterface;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  23.02.2023
 */
class InvokableFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, string $service, ?array $options = null)
    {
        return $options === null
            ? new $service()
            : new $service($options);
    }
}
