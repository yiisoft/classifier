<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

use Yiisoft\Classifier\Filter\FilterInterface;

/**
 * `Classifier` is a class finder that represents the classes found.
 */
interface ClassifierInterface
{
    public function withFilter(FilterInterface ...$filter): static;

    /**
     * Returns all the class names found.
     *
     * @return iterable<class-string> List of class names.
     */
    public function find(): iterable;
}
