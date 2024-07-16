<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Filter;

use ReflectionAttribute;
use ReflectionClass;

final class ClassAttributes implements FilterInterface
{
    private array $attributes;

    public function __construct(string ...$attributes)
    {
        $this->attributes = $attributes;
    }

    public function match(ReflectionClass $reflectionClass): bool
    {
        if (empty($this->attributes)) {
            return false;
        }

        $attributes = $reflectionClass->getAttributes();
        $attributeNames = array_map(
            static fn(ReflectionAttribute $attribute) => $attribute->getName(),
            $attributes
        );

        return count(array_intersect($this->attributes, $attributeNames)) === count($this->attributes);
    }
}
