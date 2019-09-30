<?php
declare(strict_types=1);

namespace BlackBonjour\ServiceManager\Exception;

use Error;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @author    Erick Dyck <info@erickdyck.de>
 * @since     13.05.2019
 * @copyright Copyright (c) 2019 Erick Dyck
 */
class NotFoundException extends Error implements NotFoundExceptionInterface
{
}
