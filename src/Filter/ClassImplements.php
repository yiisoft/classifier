<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Filter;

use ReflectionClass;

final class ClassImplements implements FilterInterface
{
    private array $interfaces;

    public function __construct(string ...$interfaces)
    {
        $this->interfaces = $interfaces;
    }

    public function match(ReflectionClass $reflectionClass): bool
    {
        if (empty($this->interfaces) || $reflectionClass->isInterface()) {
            return false;
        }
        $interfaces = $reflectionClass->getInterfaceNames();

        return count(array_intersect($this->interfaces, $interfaces)) === count($this->interfaces);
    }
}
