<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager;

use BlackBonjour\ServiceManager\AbstractFactory\AbstractFactoryInterface;
use BlackBonjour\ServiceManager\Exception\ClassNotFoundException;
use BlackBonjour\ServiceManager\Exception\ContainerException;
use BlackBonjour\ServiceManager\Exception\InvalidAbstractFactoryException;
use BlackBonjour\ServiceManager\Exception\InvalidArgumentException;
use BlackBonjour\ServiceManager\Exception\InvalidFactoryException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class ServiceManager implements ServiceManagerInterface
{
    /** @var array<AbstractFactoryInterface|class-string> */
    private array $abstractFactories = [];

    /** @var array<string, FactoryInterface|callable|class-string> */
    private array $factories = [];

    /** @var array<class-string, class-string> */
    private array $invokables = [];

    /** @var array<string, AbstractFactoryInterface> */
    private array $resolvedAbstractFactories = [];

    /** @var array<string, FactoryInterface|callable> */
    private array $resolvedFactories = [];

    /** @var array<string, mixed> */
    private array $resolvedServices = [];

    /** @var array<string, mixed> */
    private array $services = [];

    /**
     * @param array<string, mixed>                                  $services
     * @param array<string, FactoryInterface|callable|class-string> $factories
     * @param array<AbstractFactoryInterface|class-string>          $abstractFactories
     * @param array<string|int, class-string>                       $invokables
     *
     * @throws ClassNotFoundException
     * @throws InvalidAbstractFactoryException
     * @throws InvalidFactoryException
     */
    public function __construct(
        array $services = [],
        array $factories = [],
        array $abstractFactories = [],
        array $invokables = [],
    ) {
        foreach ($services as $id => $service) {
            $this->addService($id, $service);
        }

        foreach ($factories as $id => $factory) {
            $this->addFactory($id, $factory);
        }

        foreach ($abstractFactories as $abstractFactory) {
            $this->addAbstractFactory($abstractFactory);
        }

        foreach ($invokables as $invokable) {
            $this->addInvokable($invokable);
        }
    }

    public function addAbstractFactory(AbstractFactoryInterface|string $abstractFactory): void
    {
        if (is_string($abstractFactory) && class_exists($abstractFactory) === false) {
            throw new InvalidAbstractFactoryException(
                sprintf('The abstract factory "%s" does not exist.', $abstractFactory),
            );
        }

        $this->abstractFactories[] = $abstractFactory;
    }

    public function addFactory(string $id, FactoryInterface|callable|string $factory): void
    {
        if (is_string($factory) && class_exists($factory) === false) {
            throw new InvalidFactoryException(sprintf('The factory "%s" does not exist.', $factory));
        }

        $this->factories[$id] = $factory;
    }

    public function addInvokable(string $id): void
    {
        if (class_exists($id) === false) {
            throw new ClassNotFoundException(sprintf('The class "%s" does not exist.', $id));
        }

        $this->invokables[$id] = $id;
    }

    public function addService(string $id, mixed $service): void
    {
        $this->services[$id] = $service;
    }

    public function createService(string $id, ?array $options = null): mixed
    {
        try {
            return $this->getFactory($id)($this, $id, $options);
        } catch (Throwable $t) {
            throw new ContainerException(sprintf('The service "%s" could not be created.', $id), previous: $t);
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

        $service = $this->createService($id);
        $this->resolvedServices[$id] = $service;

        return $service;
    }

    /**
     * @throws InvalidAbstractFactoryException
     */
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

    /**
     * @throws InvalidAbstractFactoryException
     * @throws InvalidArgumentException
     */
    public function offsetExists(mixed $offset): bool
    {
        /** @phpstan-ignore-next-line */
        if (is_string($offset) === false) {
            throw new InvalidArgumentException('The service ID must be of type string.');
        }

        return $this->has($offset);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    public function offsetGet(mixed $offset): mixed
    {
        /** @phpstan-ignore-next-line */
        if (is_string($offset) === false) {
            throw new InvalidArgumentException('The service ID must be of type string.');
        }

        return $this->get($offset);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_string($offset) === false) {
            throw new InvalidArgumentException('The service ID must be of type string.');
        }

        $this->addService($offset, $value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function offsetUnset(mixed $offset): void
    {
        /** @phpstan-ignore-next-line */
        if (is_string($offset) === false) {
            throw new InvalidArgumentException('The service ID must be of type string.');
        }

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

    /**
     * @throws InvalidAbstractFactoryException
     */
    private function getAbstractFactory(string $id): ?AbstractFactoryInterface
    {
        foreach ($this->abstractFactories as $abstractFactory) {
            if ($abstractFactory instanceof AbstractFactoryInterface) {
                $factory = $abstractFactory;
            } elseif (isset($this->resolvedAbstractFactories[$abstractFactory])) {
                $factory = $this->resolvedAbstractFactories[$abstractFactory];
            } else {
                $factory = new $abstractFactory();

                if ($factory instanceof AbstractFactoryInterface) {
                    $this->resolvedAbstractFactories[$abstractFactory] = $factory;
                } else {
                    throw new InvalidAbstractFactoryException(
                        sprintf('The abstract factory "%s" is invalid.', $abstractFactory),
                    );
                }
            }

            if ($factory->canCreate($this, $id)) {
                return $factory;
            }
        }

        return null;
    }

    /**
     * @throws ContainerException
     * @throws InvalidAbstractFactoryException
     * @throws InvalidFactoryException
     */
    private function getFactory(string $id): FactoryInterface|callable
    {
        if (isset($this->resolvedFactories[$id])) {
            return $this->resolvedFactories[$id];
        }

        $resolvableFactory = $this->factories[$id]
            ?? $this->getInvokableFactory($id)
            ?? $this->getAbstractFactory($id);

        if ($resolvableFactory === null) {
            throw new ContainerException(sprintf('The factory for service "%s" was not found.', $id));
        }

        if (is_callable($resolvableFactory)) {
            $this->resolvedFactories[$id] = $resolvableFactory;

            return $resolvableFactory;
        }

        $factory = new $resolvableFactory();

        if ($factory instanceof FactoryInterface || is_callable($factory)) {
            $this->resolvedFactories[$id] = $factory;

            return $factory;
        }

        throw new InvalidFactoryException(sprintf('The factory for service "%s" is invalid.', $id));
    }

    private function getInvokableFactory(string $id): ?InvokableFactory
    {
        return isset($this->invokables[$id])
            ? new InvokableFactory()
            : null;
    }
}
