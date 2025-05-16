<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager;

use ArrayAccess;
use BlackBonjour\ServiceManager\AbstractFactory\AbstractFactoryInterface;
use BlackBonjour\ServiceManager\Exception\ClassNotFoundException;
use BlackBonjour\ServiceManager\Exception\InvalidAbstractFactoryException;
use BlackBonjour\ServiceManager\Exception\InvalidFactoryException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Service Manager Interface
 *
 * This interface defines a service container that manages the creation and retrieval of services.
 * It extends PSR-11 ContainerInterface and ArrayAccess to provide both standard container methods and array-like access to services.
 *
 * The Service Manager supports multiple ways to define and create services:
 * - Direct service instances.
 * - Factories (classes or callables that create services).
 * - Invokables (classes that can be instantiated directly).
 * - Abstract factories (for dynamically determining if a service can be created).
 *
 * @extends ArrayAccess<string, mixed>
 */
interface ServiceManagerInterface extends ArrayAccess, PsrContainerInterface
{
    /**
     * Adds an abstract factory to the service manager.
     *
     * Abstract factories are used to dynamically create services that are not explicitly defined.
     * When a service is requested but not found, the service manager will query each abstract factory to see if it can create the requested service.
     *
     * @param AbstractFactoryInterface|class-string $abstractFactory The abstract factory to add.
     *
     * @throws InvalidAbstractFactoryException When the provided class name does not exist or does not implement the AbstractFactoryInterface.
     */
    public function addAbstractFactory(AbstractFactoryInterface|string $abstractFactory): void;

    /**
     * Associates a factory with a specific service.
     *
     * Factories are responsible for creating service instances when requested.
     *
     * They can be provided as:
     * - An instance of FactoryInterface
     * - A callable that follows the factory signature
     * - A class name that implements FactoryInterface
     *
     * @param string                                 $id      The service identifier.
     * @param FactoryInterface|callable|class-string $factory The factory that will create the service.
     *
     * @throws InvalidFactoryException When the provided factory class name does not exist.
     */
    public function addFactory(string $id, FactoryInterface|callable|string $factory): void;

    /**
     * Registers a class as an invokable service.
     *
     * An invokable is a class that can be instantiated directly without a factory.
     * The service identifier will be the same as the class name.
     *
     * @param class-string $id The class name to register as an invokable service.
     *
     * @throws ClassNotFoundException When the provided class does not exist.
     */
    public function addInvokable(string $id): void;

    /**
     * Adds a pre-created service instance to the service manager.
     *
     * This method allows you to directly add service instances that are already created, rather than having the service manager create them on demand.
     *
     * @param string $id      The service identifier.
     * @param mixed  $service The service instance to store.
     */
    public function addService(string $id, mixed $service): void;

    /**
     * Creates a new instance of a service using its factory.
     *
     * This method will locate the appropriate factory for the service and use it
     * to create a new instance, passing any provided options to the factory.
     * Unlike get(), this method always creates a new instance and does not cache it.
     *
     * @param string                        $id      The service identifier.
     * @param array<string|int, mixed>|null $options Optional parameters to pass to the factory.
     *
     * @return mixed The created service instance.
     * @throws ContainerExceptionInterface If there was an error creating the service.
     * @throws NotFoundExceptionInterface If no factory was found for the service.
     */
    public function createService(string $id, ?array $options = null): mixed;

    /**
     * Removes a service and its related configurations from the service manager.
     *
     * This will remove the service instance, factory, and any other configurations associated with the specified service identifier.
     *
     * @param string $id The service identifier to remove.
     */
    public function removeService(string $id): void;
}
