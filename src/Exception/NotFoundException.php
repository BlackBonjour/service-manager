<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends ContainerException implements NotFoundExceptionInterface {}
