<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

use Symfony\Component\Finder\Finder;

abstract class AbstractClassifier implements ClassifierInterface
{
    /**
     * @var string[]
     */
    protected array $interfaces = [];
    /**
     * @var string[]
     */
    protected array $attributes = [];
    /**
     * @psalm-var class-string
     */
    protected ?string $parentClass = null;
    /**
     * @var string[]
     */
    protected array $directories;

    public function __construct(string $directory, string ...$directories)
    {
        $this->directories = [$directory, ...array_values($directories)];
    }

    /**
     * @psalm-param class-string ...$interfaces
     */
    public function withInterface(string ...$interfaces): self
    {
        $new = clone $this;
        array_push($new->interfaces, ...array_values($interfaces));

        return $new;
    }

    /**
     * @psalm-param class-string $parentClass
     */
    public function withParentClass(string $parentClass): self
    {
        $new = clone $this;
        $new->parentClass = $parentClass;
        return $new;
    }

    /**
     * @psalm-param class-string ...$attributes
     */
    public function withAttribute(string ...$attributes): self
    {
        $new = clone $this;
        array_push($new->attributes, ...array_values($attributes));

        return $new;
    }

    /**
     * @psalm-return iterable<class-string>
     */
    public function find(): iterable
    {
        if (empty($this->interfaces) && empty($this->attributes) && $this->parentClass === null) {
            return [];
        }

        yield from $this->getAvailableClasses();
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
     * @return iterable<class-string>
     */
    abstract protected function getAvailableClasses(): iterable;
}
