<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

/**
 * `TokenizerClassifier` is a classifier that finds classes, interfaces, traits and enums using PHP tokenizer.
 */
final class TokenizerClassifier extends AbstractClassifier
{
    /**
     * @psalm-suppress UnresolvableInclude
     *
     * @return array<array-key, class-string|trait-string>
     */
    protected function getAvailableDeclarations(): iterable
    {
        $files = $this->getFiles();
        $declarations = [];

        foreach ($files as $file) {
            $reflectionFile = new ReflectionFile($file->getPathname());
            array_push($declarations, ...$reflectionFile->getDeclarations());
        }

        return $declarations;
    }
}
