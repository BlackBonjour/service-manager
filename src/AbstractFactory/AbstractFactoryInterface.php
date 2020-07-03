<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager\AbstractFactory;

use BlackBonjour\ServiceManager\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * @author    Erick Dyck <info@erickdyck.de>
 * @since     18.09.2019
 * @copyright Copyright (c) 2019 Erick Dyck
 */
interface AbstractFactoryInterface extends FactoryInterface
{
    /**
     * Checks if given service can be created by this abstract factory.
     */
    public function canCreate(ContainerInterface $container, string $service): bool;
}
