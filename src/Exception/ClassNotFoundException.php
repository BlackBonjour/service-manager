<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class ClassNotFoundException extends Exception implements NotFoundExceptionInterface {}
