<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager\AbstractFactory;

use BlackBonjour\ServiceManager\Exception\ContainerException;
use BlackBonjour\ServiceManager\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  30.09.2019
 */
class ReflectionFactory implements AbstractFactoryInterface
{
    /**
     * @inheritDoc
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function __invoke(ContainerInterface $container, string $service, ?array $options = null)
    {
        if ($this->canCreate($container, $service) === false) {
            throw new ContainerException(sprintf('Cannot create service "%s"!', $service));
        }

        $reflectionClass = new ReflectionClass($service);
        $parameters      = $reflectionClass->getConstructor()?->getParameters() ?? [];

        if ($parameters) {
            $resolvedParameters = array_map(
                fn (ReflectionParameter $parameter) => $this->resolveParameter($parameter, $container, $service),
                $parameters
            );

            return new $service(...$resolvedParameters);
        }

        return new $service();
    }

    /**
     * @inheritDoc
     */
    public function canCreate(ContainerInterface $container, string $service): bool
    {
        if (class_exists($service)) {
            $reflectionClass = new ReflectionClass($service);
            $constructor     = $reflectionClass->getConstructor();

            return $constructor === null || $constructor->isPublic();
        }

        return false;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function resolveParameter(ReflectionParameter $parameter, ContainerInterface $container, string $service)
    {
        $reflectionType = $parameter->getType();
        $type           = $reflectionType instanceof ReflectionNamedType
            ? $reflectionType->getName()
            : null;

        if ($type === 'array') {
            return [];
        }

        if (
            $type === null
            || (class_exists($type) === false && interface_exists($type) === false)
        ) {
            if ($parameter->isDefaultValueAvailable() === false) {
                throw new ContainerException(
                    sprintf(
                        'Unable to create service "%s": Cannot resolve parameter "%s" to a class or interface!',
                        $service,
                        $parameter->getName()
                    )
                );
            }

            return $parameter->getDefaultValue();
        }

        if ($container->has($type)) {
            return $container->get($type);
        }

        if ($parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }

        throw new NotFoundException(
            sprintf(
                'Unable to create service "%s": Cannot resolve parameter "%s" using type hint "%s"!',
                $service,
                $parameter->getName(),
                $type
            )
        );
    }
}
