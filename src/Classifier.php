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
     * @var class-string
     */
    private ?string $targetClass = null;
    /**
     * @var string[]
     */
    private array $dirs;

    public function __construct(string ...$directory)
    {
        $this->dirs = $directory;
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
     * @psalm-param class-string $targetClass
     */
    public function withTargetClass(string $targetClass): self
    {
        $new = clone $this;
        $new->targetClass = $targetClass;
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
     * @return iterable<class-string>
     */
    public function find(): iterable
    {
        $countInterfaces = count($this->interfaces);
        $countAttributes = count($this->attributes);

        if ($countInterfaces === 0 && $countAttributes === 0 && $this->targetClass === null) {
            return [];
        }

        $this->scanFiles();

        $classesToFind = get_declared_classes();

        foreach ($classesToFind as $className) {
            $reflection = new ReflectionClass($className);

            if (!$reflection->isUserDefined()) {
                continue;
            }

            $matchedDirs = array_filter(
                $this->dirs,
                static fn($directory) => str_starts_with($reflection->getFileName(), $directory)
            );

            if (count($matchedDirs) === 0) {
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

            if (($this->targetClass !== null) && !is_subclass_of($className, $this->targetClass)) {
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
            ->in($this->dirs)
            ->name('*.php')
            ->sortByName()
            ->files();

        foreach ($files as $file) {
            require_once $file;
        }
    }
}
