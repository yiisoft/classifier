<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Filter;

use ReflectionClass;

interface FilterInterface
{
    /**
     * Tests the filter against class.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return bool `true` if class matches against filter. Otherwise, `false`.
     */
    public function match(ReflectionClass $reflectionClass): bool;
}
