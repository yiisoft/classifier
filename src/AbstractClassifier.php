<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Yiisoft\Classifier\Filter\FilterInterface;

/**
 * Base implementation for {@see ClassifierInterface} with common filters.
 */
abstract class AbstractClassifier implements ClassifierInterface
{
    /**
     * @var array<class-string|trait-string, ReflectionClass>
     */
    protected static array $reflectionsCache = [];

    /**
     * @var FilterInterface[]
     */
    private array $filters = [];
    /**
     * @var string[]
     */
    protected array $directories;

    public function __construct(string $directory, string ...$directories)
    {
        $this->directories = [$directory, ...array_values($directories)];
    }

    public function withFilter(FilterInterface ...$filter): static
    {
        $new = clone $this;
        array_push($new->filters, ...array_values($filter));

        return $new;
    }

    /**
     * @return iterable<class-string>
     */
    public function find(): iterable
    {
        foreach ($this->getAvailableDeclarations() as $declaration) {
            if ($this->skipDeclaration($declaration)) {
                continue;
            }
            yield $declaration;
        }
    }

    protected function getFiles(): Finder
    {
        return (new Finder())
            ->in($this->directories)
            ->name('*.php')
            ->sortByName()
            ->files();
    }

    /**
     * @param class-string|trait-string $declaration
     */
    private function skipDeclaration(string $declaration): bool
    {
        try {
            $reflectionClass = self::$reflectionsCache[$declaration] ??= new ReflectionClass($declaration);
        } catch (\Throwable) {
            return true;
        }

        if ($reflectionClass->isInternal() || $reflectionClass->isAnonymous()) {
            return true;
        }

        foreach ($this->filters as $filter) {
            if (!$filter->match($reflectionClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return iterable<class-string|trait-string>
     */
    abstract protected function getAvailableDeclarations(): iterable;
}
