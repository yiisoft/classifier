<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

/**
 * `Classifier` is a class finder that represents the classes found.
 */
interface ClassifierInterface
{
    /**
     * Returns all the class names found.
     *
     * @return iterable List of class names.
     * @psalm-return iterable<class-string>
     */
    public function find(): iterable;
}
