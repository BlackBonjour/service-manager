# Service Manager

Small, lightweight and factory-biased service manager based on the PSR-11 standard.

## Introduction

This library provides a simple yet powerful service container implementation that follows the [PSR-11 Container Interface](https://www.php-fig.org/psr/psr-11/) standard. It allows you to manage your application's services and dependencies with a focus on factory-based instantiation.

Key features:
- PSR-11 compliant container
- Support for multiple service creation strategies:
  - Direct service instances
  - Factories (classes or callables that create services)
  - Invokables (classes that can be instantiated directly)
  - Abstract factories (for dynamically determining if a service can be created)
- Array access syntax support
- Lightweight with minimal dependencies

## Installation

You can install the package via composer:

```bash
composer require blackbonjour/service-manager
```

### Requirements

- PHP 8.3 or higher
- PSR-11 Container Interface

## Basic Usage

### Creating a Service Manager

```php
use BlackBonjour\ServiceManager\ServiceManager;

// Create an empty service manager
$serviceManager = new ServiceManager();

// Or initialize with services, factories, abstract factories, and invokables
$serviceManager = new ServiceManager(
    services: [
        'config' => ['db' => ['host' => 'localhost']],
    ],
    factories: [
        'database' => DatabaseFactory::class,
    ],
    abstractFactories: [
        MyAbstractFactory::class,
    ],
    invokables: [
        SomeClass::class,
    ],
);
```

### Adding Services

```php
// Add a pre-created service instance
$serviceManager->addService('config', ['db' => ['host' => 'localhost']]);

// Using array access syntax
$serviceManager['logger'] = new Logger();
```

### Adding Factories

Factories are responsible for creating service instances when requested. They can be:
- An instance of `FactoryInterface`
- A callable that follows the factory signature
- A class name that implements `FactoryInterface`

```php
use BlackBonjour\ServiceManager\FactoryInterface;
use Psr\Container\ContainerInterface;

// Using a factory class
final class UserServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $service, ?array $options = null): mixed
    {
        $config = $container->get('config');

        return new UserService($config['user_settings']);
    }
}

$serviceManager->addFactory(UserService::class, UserServiceFactory::class);

// Using a callable
$serviceManager->addFactory(
    'database',
    function(ContainerInterface $container, string $service, ?array $options = null): Database {
        return new Database($container->get('config')['db']);
    },
);
```

### Adding Invokables

Invokables are classes that can be instantiated directly without a factory.

```php
// The class name is used as the service identifier
$serviceManager->addInvokable(SomeClass::class);
```

### Adding Abstract Factories

Abstract factories are used to dynamically create services that are not explicitly defined.

```php
use BlackBonjour\ServiceManager\AbstractFactory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

class MyAbstractFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, string $service): bool
    {
        // Determine if this factory can create the requested service
        return str_starts_with($service, 'my.dynamic.');
    }

    public function __invoke(ContainerInterface $container, string $service, ?array $options = null): mixed
    {
        // Create and return the service
        $name = substr($service, 11); // Remove 'my.dynamic.' prefix

        return new DynamicService($name);
    }
}

$serviceManager->addAbstractFactory(new MyAbstractFactory());
// Or using a class name
$serviceManager->addAbstractFactory(MyAbstractFactory::class);
```

### Retrieving Services

```php
// Using the get method
$config = $serviceManager->get('config');

// Using array access syntax
$logger = $serviceManager['logger'];

// Services are created on-demand and cached
$userService = $serviceManager->get(UserService::class);
```

### Creating Services with Options

```php
// Create a service with options (not cached)
$specialUserService = $serviceManager->createService(UserService::class, ['role' => 'admin']);
```

### Checking if Services Exist

```php
// Using the has method
if ($serviceManager->has('config')) {
    // Service exists or can be created
}

// Using array access syntax
if (isset($serviceManager['logger'])) {
    // Service exists or can be created
}
```

### Removing Services

```php
// Using the removeService method
$serviceManager->removeService('config');

// Using array access syntax
unset($serviceManager['logger']);
```

## Advanced Usage

### Service Creation Process

When a service is requested, the Service Manager follows this process:

1. Check if the service already exists in the services array
2. Check if the service has already been resolved and cached
3. Look for a factory registered for the service
4. Look for an invokable registered for the service
5. Try each abstract factory to see if it can create the service
6. Throw an exception if the service cannot be created

### Using the InvokableFactory

The `InvokableFactory` is a built-in factory that can create instances of classes directly:

```php
use BlackBonjour\ServiceManager\InvokableFactory;

// For classes without constructor arguments
$serviceManager->addFactory(SimpleClass::class, new InvokableFactory());

// For classes with constructor arguments
$serviceManager->addFactory(ParameterizedClass::class, new InvokableFactory());

// When retrieving, you can pass constructor arguments
$instance = $serviceManager->createService(ParameterizedClass::class, ['param1', 'param2']);
```

## API Documentation

### ServiceManagerInterface

The main interface that defines all the service container functionality:

- `addAbstractFactory(AbstractFactoryInterface|string $abstractFactory): void`
- `addFactory(string $id, FactoryInterface|callable|string $factory): void`
- `addInvokable(string $id): void`
- `addService(string $id, mixed $service): void`
- `createService(string $id, ?array $options = null): mixed`
- `get(string $id): mixed` (from PSR-11 ContainerInterface)
- `has(string $id): bool` (from PSR-11 ContainerInterface)
- `removeService(string $id): void`

### FactoryInterface

Interface for factories that create services:

- `__invoke(ContainerInterface $container, string $service, ?array $options = null): mixed`

### AbstractFactoryInterface

Interface for abstract factories that can dynamically create services:

- `canCreate(ContainerInterface $container, string $service): bool`
- `__invoke(ContainerInterface $container, string $service, ?array $options = null): mixed`

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
