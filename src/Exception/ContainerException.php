<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager\Exception;

use Error;
use Psr\Container\ContainerExceptionInterface;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  13.05.2019
 */
class ContainerException extends Error implements ContainerExceptionInterface {}
