<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager\Exception;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;

class InvalidAbstractFactoryException extends InvalidArgumentException implements ContainerExceptionInterface {}
