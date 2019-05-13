<?php
declare(strict_types=1);

namespace BlackBonjour\ServiceManager;

use ArrayAccess;
use BlackBonjour\ServiceManager\Exception\ContainerException;
use Psr\Container\ContainerInterface;
use Throwable;
use function array_key_exists;
use function is_callable;

/**
 * @author    Erick Dyck <info@erickdyck.de>
 * @since     13.05.2019
 * @package   BlackBonjour\ServiceManager
 * @copyright Copyright (c) 2019 Erick Dyck
 */
class ServiceManager implements ArrayAccess, ContainerInterface
{
    /** @var callable[]|string[] */
    private $factories;

    /** @var callable[] */
    private $resolvedFactories = [];

    /** @var mixed[] */
    private $resolvedServices = [];

    /** @var mixed[] */
    private $services;

    /**
     * @param mixed[]             $services
     * @param callable[]|string[] $factories
     */
    public function __construct(array $services = [], array $factories = [])
    {
        $this->factories = $factories;
        $this->services  = $services;
    }

    /**
     * @throws ContainerException
     */
    public function createService(string $name, array $options = [])
    {
        try {
            return $this->getFactory($name)($this, $options);
        } catch (Throwable $t) {
            throw new ContainerException(sprintf('Service %s could not be created!', $name), 0, $t);
        }
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * @throws ContainerException
     */
    private function getFactory(string $name): callable
    {
        if (isset($this->resolvedFactories[$name])) {
            return $this->resolvedFactories[$name];
        }

        $resolvableFactory = $this->factories[$name] ?? null;

        if (empty($resolvableFactory)) {
            throw new ContainerException(sprintf('Factory for service %s not found!', $name));
        }

        if (is_callable($resolvableFactory)) {
            $this->resolvedFactories[$name] = $resolvableFactory;

            return $resolvableFactory;
        }

        if (class_exists($resolvableFactory)) {
            $factory = new $resolvableFactory;

            if ($factory instanceof FactoryInterface) {
                $this->resolvedFactories[$name] = $factory;

                return $factory;
            }
        }

        throw new ContainerException(sprintf('Factory for service %s is invalid!', $name));
    }

    /**
     * @inheritDoc
     */
    public function has($id): bool
    {
        return $this->offsetExists($id);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->services) || isset($this->factories[$offset]);
    }

    /**
     * @inheritDoc
     * @throws ContainerException
     */
    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->services)) {
            return $this->services[$offset];
        }

        if (array_key_exists($offset, $this->resolvedServices)) {
            return $this->resolvedServices[$offset];
        }

        $this->resolvedServices[$offset] = $service = $this->createService($offset);

        return $service;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->services[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        unset(
            $this->factories[$offset],
            $this->resolvedFactories[$offset],
            $this->resolvedServices[$offset],
            $this->services[$offset]
        );
    }
}
