# Service Manager

Small, lightweight and factory-biased service manager based on the PSR-11 standard.

[![Latest Stable Version](https://poser.pugx.org/blackbonjour/service-manager/v/stable)](https://packagist.org/packages/blackbonjour/service-manager)
[![License](https://poser.pugx.org/blackbonjour/service-manager/license)](https://packagist.org/packages/blackbonjour/service-manager)

## Table of Contents

- [Installation](#installation)
- [Basic Usage](#basic-usage)
- [Configuration](#configuration)
  - [Services](#services)
  - [Factories](#factories)
  - [Abstract Factories](#abstract-factories)
  - [Invokables](#invokables)
  - [Aliases](#aliases)
- [Advanced Usage](#advanced-usage)
  - [Creating Services with Options](#creating-services-with-options)
  - [Using Array Access](#using-array-access)
  - [Removing Services](#removing-services)
- [Abstract Factories](#abstract-factories-1)
  - [DynamicFactory](#dynamicfactory)
  - [ReflectionFactory](#reflectionfactory)
- [API Reference](#api-reference)
  - [ServiceManager](#servicemanager)
  - [FactoryInterface](#factoryinterface)
  - [AbstractFactoryInterface](#abstractfactoryinterface)
  - [InvokableFactory](#invokablefactory)

## Installation

You can install the package via composer:

```bash
composer require blackbonjour/service-manager
```

## Basic Usage

```php
use BlackBonjour\ServiceManager\ServiceManager;

// Create a new service manager
$serviceManager = new ServiceManager();

// Add a service directly
$serviceManager->addService('config', [
    'db' => [
        'host' => 'localhost',
        'user' => 'root',
        'password' => 'secret',
    ],
]);

// Retrieve a service
$config = $serviceManager->get('config');
```

## Configuration

The ServiceManager constructor accepts several configuration arrays:

```php
$serviceManager = new ServiceManager(
    services: [],          // Pre-created service instances
    factories: [],         // Factories for creating services
    abstractFactories: [], // Abstract factories for dynamic service creation
    invokables: [],        // Classes that can be instantiated directly
    aliases: [],           // Alternative names for services
);
```

### Services

Services are pre-created instances that are stored in the container:

```php
// Via constructor
$serviceManager = new ServiceManager([
    'config' => ['debug' => true],
    'logger' => new Logger(),
]);

// Via method
$serviceManager->addService('config', ['debug' => true]);
$serviceManager->addService('logger', new Logger());
```

### Factories

Factories are responsible for creating service instances. They can be:

1. Classes implementing `FactoryInterface`
2. Callable objects
3. Class strings that resolve to one of the above

```php
// Using a factory class
$serviceManager->addFactory(Database::class, DatabaseFactory::class);

// Using a callable
$serviceManager->addFactory('logger', function($container, $requestedName) {
    return new Logger();
});

// Via constructor
$serviceManager = new ServiceManager(
    factories: [
        Database::class => DatabaseFactory::class,
        'logger' => function($container, $requestedName) {
            return new Logger();
        },
    ]
);
```

### Abstract Factories

Abstract factories are used when a service is not explicitly defined. They determine if they can create a requested service:

```php
// Add an abstract factory
$serviceManager->addAbstractFactory(new DynamicFactory());

// Or via constructor
$serviceManager = new ServiceManager(
    abstractFactories: [
        new DynamicFactory(),
        ReflectionFactory::class,
    ]
);
```

### Invokables

Invokables are classes that can be instantiated directly without a factory:

```php
// Add an invokable
$serviceManager->addInvokable(stdClass::class);

// Via constructor
$serviceManager = new ServiceManager(
    invokables: [
        stdClass::class,
        SomeClass::class,
    ]
);
```

### Aliases

Aliases provide alternative names for services:

```php
// Add an alias
$serviceManager->addAlias('configuration', 'config');

// Via constructor
$serviceManager = new ServiceManager(
    aliases: [
        'configuration' => 'config',
        'db' => Database::class,
    ]
);
```

## Advanced Usage

### Creating Services with Options

You can create services with additional options:

```php
// Define a factory that accepts options
$serviceManager->addFactory('database', function($container, $requestedName, $options = null) {
    return new Database(
        $options['host'] ?? 'localhost',
        $options['user'] ?? 'root',
        $options['password'] ?? 'secret'
    );
});

// Create the service with options
$db = $serviceManager->createService('database', [
    'host' => 'db.example.com',
    'user' => 'admin',
    'password' => 'password123'
]);
```

### Using Array Access

The ServiceManager implements `ArrayAccess`, allowing you to use array syntax:

```php
// Add a service
$serviceManager['config'] = ['debug' => true];

// Check if a service exists
if (isset($serviceManager['config'])) {
    // Service exists
}

// Get a service
$config = $serviceManager['config'];

// Remove a service
unset($serviceManager['config']);
```

### Removing Services

You can remove services from the container:

```php
$serviceManager->removeService('config');
// or
unset($serviceManager['config']);
```

## Abstract Factories

### DynamicFactory

The `DynamicFactory` looks for a factory class with the same name as the requested service plus "Factory":

```php
// Add the DynamicFactory
$serviceManager->addAbstractFactory(new DynamicFactory());

// Now if you request MyService, it will look for MyServiceFactory
$service = $serviceManager->get(MyService::class);
```

### ReflectionFactory

The `ReflectionFactory` uses PHP's Reflection API to automatically instantiate classes and resolve their dependencies:

```php
// Add the ReflectionFactory
$serviceManager->addAbstractFactory(new ReflectionFactory());

// Now you can get any class that has constructor dependencies registered in the container
$service = $serviceManager->get(MyService::class);
```

## API Reference

### ServiceManager

The main container class implementing PSR-11's `ContainerInterface`.

**Methods:**

- `__construct(array $services = [], array $factories = [], array $abstractFactories = [], array $invokables = [], array $aliases = [])`
- `addAbstractFactory(AbstractFactoryInterface|string $abstractFactory): void`
- `addAlias(string $alias, string $id): void`
- `addFactory(string $id, FactoryInterface|callable|string $factory): void`
- `addInvokable(string $id): void`
- `addService(string $id, mixed $service): void`
- `createService(string $id, ?array $options = null): mixed`
- `get(string $id): mixed`
- `has(string $id): bool`
- `removeService(string $id): void`

### FactoryInterface

Interface for factories that create services.

**Methods:**

- `__invoke(ContainerInterface $container, string $service, ?array $options = null)`

### AbstractFactoryInterface

Interface for abstract factories that can dynamically determine if they can create a service.

**Methods:**

- `canCreate(ContainerInterface $container, string $service): bool`
- `__invoke(ContainerInterface $container, string $service, ?array $options = null)` (inherited from FactoryInterface)

### InvokableFactory

A factory for creating instances of classes without dependencies.

**Methods:**

- `__invoke(ContainerInterface $container, string $service, ?array $options = null)`
