<?php
declare(strict_types=1);

namespace BlackBonjour\ServiceManager;

use ArrayAccess;
use BlackBonjour\ServiceManager\AbstractFactory\AbstractFactoryInterface;
use BlackBonjour\ServiceManager\Exception\ContainerException;
use Psr\Container\ContainerInterface;
use Throwable;
use TypeError;
use function array_key_exists;
use function is_callable;
use function is_string;

/**
 * @author    Erick Dyck <info@erickdyck.de>
 * @since     13.05.2019
 * @copyright Copyright (c) 2019 Erick Dyck
 */
class ServiceManager implements ArrayAccess, ContainerInterface
{
    /** @var AbstractFactoryInterface[] */
    private $abstractFactories;

    /** @var FactoryInterface[]|callable[]|string[] */
    private $factories;

    /** @var FactoryInterface[]|callable[] */
    private $resolvedFactories = [];

    /** @var mixed[] */
    private $resolvedServices = [];

    /** @var mixed[] */
    private $services;

    /**
     * @param mixed[]                                $services
     * @param FactoryInterface[]|callable[]|string[] $factories
     * @param AbstractFactoryInterface[]             $abstractFactories
     */
    public function __construct(array $services = [], array $factories = [], array $abstractFactories = [])
    {
        $this->abstractFactories = $abstractFactories;
        $this->factories         = $factories;
        $this->services          = $services;
    }

    public function addAbstractFactory(AbstractFactoryInterface $abstractFactory): void
    {
        $this->abstractFactories[] = $abstractFactory;
    }

    public function addFactory(string $name, $factory): void
    {
        if (is_string($factory) || is_callable($factory)) {
            $this->factories[$name] = $factory;
        } else {
            throw new TypeError(sprintf('Invalid factory for service %s given!', $name));
        }
    }

    public function addService(string $name, $service): void
    {
        $this->offsetSet($name, $service);
    }

    /**
     * @throws ContainerException
     */
    public function createService(string $name, array $options = [])
    {
        try {
            return $this->getFactory($name)($this, $name, $options);
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

    private function getAbstractFactory(string $name): ?callable
    {
        foreach ($this->abstractFactories as $abstractFactory) {
            if ($abstractFactory->canCreate($this, $name)) {
                return $abstractFactory;
            }
        }

        return null;
    }

    /**
     * @return FactoryInterface|callable
     * @throws ContainerException
     */
    private function getFactory(string $name): callable
    {
        if (isset($this->resolvedFactories[$name])) {
            return $this->resolvedFactories[$name];
        }

        $resolvableFactory = $this->factories[$name] ?? $this->getAbstractFactory($name);

        if (empty($resolvableFactory)) {
            throw new ContainerException(sprintf('Factory for service %s not found!', $name));
        }

        if (is_callable($resolvableFactory)) {
            $this->resolvedFactories[$name] = $resolvableFactory;

            return $resolvableFactory;
        }

        if (is_string($resolvableFactory) && class_exists($resolvableFactory)) {
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
        if (array_key_exists($offset, $this->services) || isset($this->factories[$offset])) {
            return true;
        }

        foreach ($this->abstractFactories as $abstractFactory) {
            if ($abstractFactory->canCreate($this, $offset)) {
                return true;
            }
        }

        return false;
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

    public function removeService(string $name): void
    {
        $this->offsetUnset($name);
    }
}
