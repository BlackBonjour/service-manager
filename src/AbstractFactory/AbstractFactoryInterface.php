<?php
declare(strict_types=1);

namespace BlackBonjour\ServiceManager;

use Psr\Container\ContainerInterface;

/**
 * @author    Erick Dyck <info@erickdyck.de>
 * @since     18.09.2019
 * @copyright Copyright (c) 2019 Erick Dyck
 */
interface AbstractFactoryInterface extends FactoryInterface
{
    public function canCreate(ContainerInterface $container, string $service): bool;
}
