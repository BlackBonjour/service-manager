<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager;

use ArrayAccess;
use BlackBonjour\ServiceManager\AbstractFactory\AbstractFactoryInterface;
use BlackBonjour\ServiceManager\Exception\ContainerException;
use Psr\Container\ContainerInterface;
use Throwable;

use function array_key_exists;
use function is_callable;
use function is_string;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  13.05.2019
 */
class ServiceManager implements ArrayAccess, ContainerInterface
{
    /** @var FactoryInterface[]|callable[] */
    private array $resolvedFactories = [];
    private array $resolvedServices = [];

    /**
     * @param FactoryInterface[]|callable[]|string[] $factories
     * @param AbstractFactoryInterface[]             $abstractFactories
     */
    public function __construct(
        private array $services = [],
        private array $factories = [],
        private array $abstractFactories = []
    ) {
    }

    public function addAbstractFactory(AbstractFactoryInterface $abstractFactory): void
    {
        $this->abstractFactories[] = $abstractFactory;
    }

    public function addFactory(string $name, string|callable $factory): void
    {
        $this->factories[$name] = $factory;
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
            throw new ContainerException(sprintf('Service "%s" could not be created!', $name), 0, $t);
        }
    }

    public function get(string $id)
    {
        return $this->offsetGet($id);
    }

    public function has(string $id): bool
    {
        return $this->offsetExists($id);
    }

    public function offsetExists(mixed $offset): bool
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
     * @throws ContainerException
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (array_key_exists($offset, $this->services)) {
            return $this->services[$offset];
        }

        if (array_key_exists($offset, $this->resolvedServices)) {
            return $this->resolvedServices[$offset];
        }

        $service                         = $this->createService($offset);
        $this->resolvedServices[$offset] = $service;

        return $service;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->services[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
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

    private function getAbstractFactory(string $name): ?AbstractFactoryInterface
    {
        foreach ($this->abstractFactories as $abstractFactory) {
            if ($abstractFactory->canCreate($this, $name)) {
                return $abstractFactory;
            }
        }

        return null;
    }

    /**
     * @throws ContainerException
     */
    private function getFactory(string $name): FactoryInterface|callable
    {
        if (isset($this->resolvedFactories[$name])) {
            return $this->resolvedFactories[$name];
        }

        $resolvableFactory = $this->factories[$name] ?? $this->getAbstractFactory($name);

        if (empty($resolvableFactory)) {
            throw new ContainerException(sprintf('Factory for service "%s" not found!', $name));
        }

        if (is_callable($resolvableFactory)) {
            $this->resolvedFactories[$name] = $resolvableFactory;

            return $resolvableFactory;
        }

        if (is_string($resolvableFactory) && class_exists($resolvableFactory)) {
            $factory = new $resolvableFactory();

            if ($factory instanceof FactoryInterface) {
                $this->resolvedFactories[$name] = $factory;

                return $factory;
            }
        }

        throw new ContainerException(sprintf('Factory for service "%s" is invalid!', $name));
    }
}
