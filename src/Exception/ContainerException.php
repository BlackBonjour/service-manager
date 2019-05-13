<?php
declare(strict_types=1);

namespace BlackBonjour\ServiceManager\Exception;

use Error;
use Psr\Container\ContainerExceptionInterface;

/**
 * @author    Erick Dyck <info@erickdyck.de>
 * @since     13.05.2019
 * @package   BlackBonjour\ServiceManager\Exception
 * @copyright Copyright (c) 2019 Erick Dyck
 */
class ContainerException extends Error implements ContainerExceptionInterface
{
}
