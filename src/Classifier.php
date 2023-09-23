<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

use ReflectionAttribute;
use ReflectionClass;

final class Classifier extends AbstractClassifier
{
    /**
     * @psalm-var array<class-string, ReflectionClass>
     */
    private static array $reflectionsCache = [];

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

    /**
     * @psalm-param class-string $className
     */
    private function skipClass(string $className): bool
    {
        $reflectionClass = self::$reflectionsCache[$className] ??= new ReflectionClass($className);

        if ($reflectionClass->isInternal() || $reflectionClass->isAnonymous()) {
            return true;
        }
        $directories = $this->directories;
        $isWindows = DIRECTORY_SEPARATOR === '\\';

        if ($isWindows) {
            /**
             * @psalm-var string[] $directories
             */
            // @codeCoverageIgnoreStart
            $directories = str_replace('/', '\\', $directories);
            // @codeCoverageIgnoreEnd
        }

        $matchedDirs = array_filter(
            $directories,
            static fn($directory) => str_starts_with($reflectionClass->getFileName(), $directory)
        );

        if (count($matchedDirs) === 0) {
            return true;
        }

        if (!empty($this->interfaces)) {
            $interfaces = $reflectionClass->getInterfaces();
            $interfaces = array_map(static fn(ReflectionClass $class) => $class->getName(), $interfaces);

            if (count(array_intersect($this->interfaces, $interfaces)) !== count($this->interfaces)) {
                return true;
            }
        }

        if (!empty($this->attributes)) {
            $attributes = $reflectionClass->getAttributes();
            $attributes = array_map(
                static fn(ReflectionAttribute $attribute) => $attribute->getName(),
                $attributes
            );

            if (count(array_intersect($this->attributes, $attributes)) !== count($this->attributes)) {
                return true;
            }
        }

        return ($this->parentClass !== null) && !is_subclass_of($reflectionClass->getName(), $this->parentClass);
    }
}
