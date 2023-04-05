<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

use ReflectionAttribute;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

final class Classifier
{
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
    private ?string $parent = null;

    public function __construct(private string $directory)
    {
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
     * @psalm-param class-string $parent
     */
    public function withParent(string $parent): self
    {
        $new = clone $this;
        $new->parent = $parent;
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

    public function find(): iterable
    {
        $countInterfaces = count($this->interfaces);
        $countAttributes = count($this->attributes);

        if ($countInterfaces === 0 && $countAttributes === 0 && $this->parent === null) {
            return [];
        }

        $this->scanFiles();

        $classesToFind = get_declared_classes();

        $baseDirectory = $this->directory;
        if (DIRECTORY_SEPARATOR === '\\') {
            $baseDirectory = str_replace('/', '\\', $baseDirectory);
        }

        foreach ($classesToFind as $className) {
            $reflection = new ReflectionClass($className);
            $filePath = $reflection->getFileName();
            if ($filePath === false || !str_starts_with($filePath, $baseDirectory)) {
                continue;
            }

            if ($countInterfaces > 0) {
                $interfaces = $reflection->getInterfaces();
                $interfaces = array_map(static fn(ReflectionClass $class) => $class->getName(), $interfaces);

                if (count(array_intersect($this->interfaces, $interfaces)) !== $countInterfaces) {
                    continue;
                }
            }

            if ($countAttributes > 0) {
                $attributes = $reflection->getAttributes();
                $attributes = array_map(
                    static fn(ReflectionAttribute $attribute) => $attribute->getName(),
                    $attributes
                );

                if (count(array_intersect($this->attributes, $attributes)) !== $countAttributes) {
                    continue;
                }
            }

            if (($this->parent !== null) && !is_subclass_of($className, $this->parent)) {
                continue;
            }

            yield $className;
        }
    }

    /**
     * @psalm-suppress UnresolvableInclude
     */
    private function scanFiles(): void
    {
        $files = (new Finder())
            ->in($this->directory)
            ->name('*.php')
            ->sortByName()
            ->files();

        foreach ($files as $file) {
            require_once $file;
        }
    }
}
