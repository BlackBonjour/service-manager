<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

/**
 * A class with optional parameters in the constructor for testing ReflectionFactory
 */
class ClassWithOptionalParameters
{
    /**
     * @param string $name Optional name parameter
     * @param int $value Optional value parameter
     * @param FooBar|null $fooBar Optional object parameter
     */
    public function __construct(
        private string $name = 'default',
        private int $value = 42,
        private ?FooBar $fooBar = null
    ) {
    }

    /**
     * Get the name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the value
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Get the FooBar instance
     */
    public function getFooBar(): ?FooBar
    {
        return $this->fooBar;
    }
}
