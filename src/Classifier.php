<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

use ReflectionAttribute;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

final class Classifier extends AbstractClassifier
{
    /**
     * @psalm-suppress UnresolvableInclude
     */
    protected function getAvailableClasses(): iterable
    {
        $files = $this->getFiles();

        foreach ($files as $file) {
            require_once $file;
        }

        foreach (get_declared_classes() as $className) {
            if ($this->skipClass($className)) {
                continue;
            }

            yield $className;
        }
    }
}
