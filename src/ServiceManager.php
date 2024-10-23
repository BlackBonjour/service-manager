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
 *
 * @implements ArrayAccess<string, mixed>
 */
class ServiceManager implements ArrayAccess, ContainerInterface
{
    /** @var array<AbstractFactoryInterface> */
    private array $abstractFactories;

    /** @var array<string, FactoryInterface|callable|class-string> */
    private array $factories;

    /** @var array<class-string, class-string> */
    private array $invokables;

    /** @var array<string, FactoryInterface|callable> */
    private array $resolvedFactories = [];

    /** @var array<string, mixed> */
    private array $resolvedServices = [];

    /** @var array<string, mixed> */
    private array $services;

    /**
     * @param array<string, mixed>                                  $services
     * @param array<string, FactoryInterface|callable|class-string> $factories
     * @param array<AbstractFactoryInterface>                       $abstractFactories
     * @param array<string|int, class-string>                       $invokables
     */
    public function __construct(
        array $services = [],
        array $factories = [],
        array $abstractFactories = [],
        array $invokables = [],
    ) {
        // Validate services
        foreach (array_keys($services) as $id) {
            assert(is_string($id), sprintf('Service ID must be a string, "%s" given!', $id));
        }

        // Validate factories
        foreach ($factories as $id => $factory) {
            assert(is_string($id), sprintf('Service ID must be a string, "%s" given!', $id));
            assert(
                $factory instanceof FactoryInterface
                || is_callable($factory)
                || (is_string($factory) && class_exists($factory)),
                sprintf('Invalid factory provided for service "%s"!', $id),
            );
        }

        // Validate abstract factories
        foreach ($abstractFactories as $abstractFactory) {
            assert(
                $abstractFactory instanceof AbstractFactoryInterface,
                sprintf('Abstract factories must implement %s!', AbstractFactoryInterface::class),
            );
        }

        // Validate invokable classes
        foreach ($invokables as $invokable) {
            assert(
                is_string($invokable) && class_exists($invokable),
                sprintf('Invokable class "%s" does not exist!', $invokable),
            );
        }

        // Set properties
        $this->abstractFactories = $abstractFactories;
        $this->factories         = $factories;
        $this->invokables        = array_combine($invokables, $invokables);
        $this->services          = $services;
    }

    public function addAbstractFactory(AbstractFactoryInterface $abstractFactory): void
    {
        $this->abstractFactories[] = $abstractFactory;
    }

    public function addFactory(string $id, FactoryInterface|callable|string $factory): void
    {
        if (is_string($factory) && class_exists($factory) === false) {
            throw new ContainerException(sprintf('Factory "%s" does not exist!', $factory));
        }

        $this->factories[$id] = $factory;
    }

    public function addInvokable(string $id): void
    {
        if (class_exists($id) === false) {
            throw new ContainerException(sprintf('Class "%s" does not exist!', $id));
        }

        $this->invokables[$id] = $id;
    }

    public function addService(string $id, mixed $service): void
    {
        $this->services[$id] = $service;
    }

    /**
     * @param array<string|int, mixed>|null $options
     *
     * @throws ContainerException
     */
    public function createService(string $id, ?array $options = null): mixed
    {
        try {
            return $this->getFactory($id)($this, $id, $options);
        } catch (Throwable $t) {
            throw new ContainerException(sprintf('Service "%s" could not be created!', $id), previous: $t);
        }
    }

    public function get(string $id): mixed
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
        if (
            array_key_exists($id, $this->services)
            || array_key_exists($id, $this->resolvedServices)
            || isset($this->factories[$id])
            || isset($this->invokables[$id])
        ) {
            return true;
        }

        return $this->getAbstractFactory($id) !== null;
    }

    public function offsetExists(mixed $offset): bool
    {
        assert(is_string($offset), 'Service ID must be of type string!');

        return $this->has($offset);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function offsetGet(mixed $offset): mixed
    {
        assert(is_string($offset), 'Service ID must be of type string!');

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        assert(is_string($offset), 'Service ID must be of type string!');

        $this->addService($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        assert(is_string($offset), 'Service ID must be of type string!');

        $this->removeService($offset);
    }

    public function removeService(string $id): void
    {
        unset(
            $this->factories[$id],
            $this->invokables[$id],
            $this->resolvedFactories[$id],
            $this->resolvedServices[$id],
            $this->services[$id],
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

            if ($factory instanceof FactoryInterface || is_callable($factory)) {
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
