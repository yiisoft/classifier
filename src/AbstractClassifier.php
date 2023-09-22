<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

use ReflectionAttribute;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

abstract class AbstractClassifier implements ClassifierInterface
{
    /**
     * @psalm-var array<class-string, ReflectionClass>
     */
    private static array $reflectionsCache = [];

    /**
     * @var string[]
     */
    private array $interfaces = [];
    /**
     * @var string[]
     */
    private array $attributes = [];
    /**
     * @psalm-var class-string
     */
    private ?string $parentClass = null;
    /**
     * @var string[]
     */
    private array $directories;

    public function __construct(string $directory, string ...$directories)
    {
        $this->directories = [$directory, ...array_values($directories)];
        $isWindows = DIRECTORY_SEPARATOR === '\\';

        if ($isWindows) {
            $this->directories = str_replace('/', '\\', $this->directories);
        }
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
        if (count($this->interfaces) === 0 && count($this->attributes) === 0 && $this->parentClass === null) {
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
     * @psalm-param class-string $className
     */
    protected function skipClass(string $className): bool
    {
        $reflectionClass = self::$reflectionsCache[$className] ??= new ReflectionClass($className);

        if ($reflectionClass->isInternal()) {
            return true;
        }
        $countInterfaces = count($this->interfaces);
        $countAttributes = count($this->attributes);
        $directories = $this->directories;

        $matchedDirs = array_filter(
            $directories,
            static fn($directory) => str_starts_with($reflectionClass->getFileName(), $directory)
        );

        if (count($matchedDirs) === 0) {
            return true;
        }

        if ($countInterfaces > 0) {
            $interfaces = $reflectionClass->getInterfaces();
            $interfaces = array_map(static fn(ReflectionClass $class) => $class->getName(), $interfaces);

            if (count(array_intersect($this->interfaces, $interfaces)) !== $countInterfaces) {
                return true;
            }
        }

        if ($countAttributes > 0) {
            $attributes = $reflectionClass->getAttributes();
            $attributes = array_map(
                static fn(ReflectionAttribute $attribute) => $attribute->getName(),
                $attributes
            );

            if (count(array_intersect($this->attributes, $attributes)) !== $countAttributes) {
                return true;
            }
        }

        return ($this->parentClass !== null) && !is_subclass_of($reflectionClass->getName(), $this->parentClass);
    }

    /**
     * @return iterable<class-string>
     */
    abstract protected function getAvailableClasses(): iterable;
}
