<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Filter;

use ReflectionClass;

final class SubclassOf implements FilterInterface
{
    /**
     * @param class-string $class
     */
    public function __construct(private string $class)
    {
    }

    public function match(ReflectionClass $reflectionClass): bool
    {
        return $reflectionClass->isSubclassOf($this->class);
    }
}
