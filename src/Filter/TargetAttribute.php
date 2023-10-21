<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Filter;

use ReflectionAttribute;
use ReflectionClass;

final class TargetAttribute implements FilterInterface
{
    public function __construct(private string $target)
    {
    }

    public function match(ReflectionClass $reflectionClass): bool
    {
        $attributes = $reflectionClass->getAttributes($this->target, ReflectionAttribute::IS_INSTANCEOF);
        $attributeNames = array_map(
            static fn(ReflectionAttribute $attribute) => $attribute->getName(),
            $attributes
        );

        return !empty($attributeNames);
    }
}