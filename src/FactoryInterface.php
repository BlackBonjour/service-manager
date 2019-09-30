<?php
declare(strict_types=1);

namespace BlackBonjour\ServiceManager;

use Psr\Container\ContainerInterface;

/**
 * @author    Erick Dyck <info@erickdyck.de>
 * @since     13.05.2019
 * @copyright Copyright (c) 2019 Erick Dyck
 */
interface FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $service, array $options = []);
}
