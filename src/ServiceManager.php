<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager;

use ArrayAccess;
use BlackBonjour\ServiceManager\AbstractFactory\AbstractFactoryInterface;
use BlackBonjour\ServiceManager\Exception\ContainerException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
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
    /** @var array<string, string> */
    private array $invokables;

    /** @var FactoryInterface[]|callable[] */
    private array $resolvedFactories = [];
    private array $resolvedServices = [];

    /**
     * @param FactoryInterface[]|callable[]|string[] $factories
     * @param AbstractFactoryInterface[]             $abstractFactories
     * @param string[]                               $invokables
     */
    public function __construct(
        private array $services = [],
        private array $factories = [],
        private array $abstractFactories = [],
        array $invokables = [],
    ) {
        $this->invokables = array_combine($invokables, $invokables);
    }

    public function addAbstractFactory(AbstractFactoryInterface $abstractFactory): void
    {
        $this->abstractFactories[] = $abstractFactory;
    }

    public function addFactory(string $id, string|callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    public function addInvokable(string $id): void
    {
        $this->invokables[$id] ??= $id;
    }

    public function addService(string $id, $service): void
    {
        $this->services[$id] = $service;
    }

    /**
     * @throws ContainerException
     */
    public function createService(string $id, ?array $options = null)
    {
        try {
            return $this->getFactory($id)($this, $id, $options);
        } catch (Throwable $t) {
            throw new ContainerException(sprintf('Service "%s" could not be created!', $id), 0, $t);
        }
    }

    public function get(string $id)
    {
        if (array_key_exists($id, $this->services)) {
            return $this->services[$id];
        }

        if (array_key_exists($id, $this->resolvedServices)) {
            return $this->resolvedServices[$id];
        }

        $service                     = $this->createService($id);
        $this->resolvedServices[$id] = $service;

        return $service;
    }

    public function has(string $id): bool
    {
        if (array_key_exists($id, $this->services) || isset($this->factories[$id])) {
            return true;
        }

        foreach ($this->abstractFactories as $abstractFactory) {
            if ($abstractFactory->canCreate($this, $id)) {
                return true;
            }
        }

        return false;
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->addService($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->removeService($offset);
    }

    public function removeService(string $id): void
    {
        unset(
            $this->factories[$id],
            $this->invokables[$id],
            $this->resolvedFactories[$id],
            $this->resolvedServices[$id],
            $this->services[$id]
        );
    }

    private function getAbstractFactory(string $id): ?AbstractFactoryInterface
    {
        foreach ($this->abstractFactories as $abstractFactory) {
            if ($abstractFactory->canCreate($this, $id)) {
                return $abstractFactory;
            }
        }

        return null;
    }

    /**
     * @throws ContainerException
     */
    private function getFactory(string $id): FactoryInterface|callable
    {
        if (isset($this->resolvedFactories[$id])) {
            return $this->resolvedFactories[$id];
        }

        $resolvableFactory = $this->factories[$id]
            ?? $this->getInvokableFactory($id)
            ?? $this->getAbstractFactory($id);

        if (empty($resolvableFactory)) {
            throw new ContainerException(sprintf('Factory for service "%s" not found!', $id));
        }

        if (is_callable($resolvableFactory)) {
            $this->resolvedFactories[$id] = $resolvableFactory;

            return $resolvableFactory;
        }

        if (is_string($resolvableFactory) && class_exists($resolvableFactory)) {
            $factory = new $resolvableFactory();

            if ($factory instanceof FactoryInterface) {
                $this->resolvedFactories[$id] = $factory;

                return $factory;
            }
        }

        throw new ContainerException(sprintf('Factory for service "%s" is invalid!', $id));
    }

    private function getInvokableFactory(string $id): ?InvokableFactory
    {
        return isset($this->invokables[$id])
            ? new InvokableFactory()
            : null;
    }
}
