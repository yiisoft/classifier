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
     * @return iterable List of class names.
     * @psalm-return iterable<class-string>
     */
    public function find(): iterable;
}
