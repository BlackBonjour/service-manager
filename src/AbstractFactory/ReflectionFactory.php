<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager\AbstractFactory;

use BlackBonjour\ServiceManager\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  30.09.2019
 */
class ReflectionFactory implements AbstractFactoryInterface
{
    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function __invoke(ContainerInterface $container, string $service, array $options = [])
    {
        $reflectionClass = new ReflectionClass($service);
        $constructor     = $reflectionClass->getConstructor();

        if ($constructor === null) {
            return new $service();
        }

        $parameters = $constructor->getParameters();

        if (empty($parameters)) {
            return new $service();
        }

        $resolvedParameters = array_map($this->getParameterResolver($container, $service), $parameters);

        return new $service(...$resolvedParameters);
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function canCreate(ContainerInterface $container, string $service): bool
    {
        return class_exists($service) && $this->isConstructorCallable($service);
    }

    private function getParameterResolver(ContainerInterface $container, string $service): callable
    {
        return fn (ReflectionParameter $parameter) => $this->resolveParameter($parameter, $container, $service);
    }

    /**
     * @throws ReflectionException
     */
    private function isConstructorCallable(string $service): bool
    {
        $constructor = (new ReflectionClass($service))->getConstructor();

        return $constructor === null || $constructor->isPublic();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function resolveParameter(ReflectionParameter $parameter, ContainerInterface $container, string $service)
    {
        $type = $parameter->getType()?->getName();

        if ($type === 'array') {
            return [];
        }

        if (
            $type === null
            || (class_exists($type) === false && interface_exists($type) === false)
        ) {
            if ($parameter->isDefaultValueAvailable() === false) {
                throw new NotFoundException(
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
