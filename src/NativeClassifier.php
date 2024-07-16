<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

/**
 * `NativeClassifier` is a classifier that finds classes, interfaces, traits and enums using PHP's native functions:
 * - {@see get_declared_classes()}
 * - {@see get_declared_interfaces()}
 * - {@see get_declared_traits()}
 */
final class NativeClassifier extends AbstractClassifier
{
    /**
     * @psalm-suppress UnresolvableInclude
     */
    protected function getAvailableDeclarations(): iterable
    {
        $files = $this->getFiles();

        foreach ($files as $file) {
            try {
                require_once $file;
            } catch (\Throwable) {
                // Ignore syntax errors
            }
        }

        /** @var array<class-string|trait-string> $declarations */
        $declarations = [...get_declared_classes(), ...get_declared_interfaces(), ...get_declared_traits()];

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

        foreach ($declarations as $declaration) {
            $reflectionClass = self::$reflectionsCache[$declaration] ??= new \ReflectionClass($declaration);

            $matchedDirs = array_filter(
                $directories,
                static fn($directory) => $reflectionClass->getFileName() && str_starts_with($reflectionClass->getFileName(), $directory)
            );

            if (count($matchedDirs) === 0) {
                continue;
            }
            yield $reflectionClass->getName();
        }
    }
}
