<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

use ReflectionAttribute;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

use function count;

use const DIRECTORY_SEPARATOR;

/**
 * Classifier traverses file system to find classes by a certain criteria.
 */
final class Classifier
{
    /**
     * @var string[] Interfaces to search for.
     */
    private array $interfaces = [];
    /**
     * @var string[] Attributes to search for.
     */
    private array $attributes = [];
    /**
     * @var ?string Parent class to search for.
     * @psalm-var class-string
     */
    private ?string $parentClass = null;
    /**
     * @var string[] Directories to traverse.
     */
    private array $directories;

    /**
     * @param string $directory Directory to traverse.
     * @param string ...$directories Extra directories to traverse.
     */
    public function __construct(string $directory, string ...$directories)
    {
        $this->directories = [$directory, ...array_values($directories)];
    }

    /**
     * @param string ...$interfaces Interfaces to search for.
     * @psalm-param class-string ...$interfaces
     */
    public function withInterface(string ...$interfaces): self
    {
        $new = clone $this;
        array_push($new->interfaces, ...array_values($interfaces));

        return $new;
    }

    /**
     * @param string $parentClass Parent class to search for.
     * @psalm-param class-string $parentClass
     */
    public function withParentClass(string $parentClass): self
    {
        $new = clone $this;
        $new->parentClass = $parentClass;
        return $new;
    }

    /**
     * @para string ...$attributes Attributes to search for.
     * @psalm-param class-string ...$attributes
     */
    public function withAttribute(string ...$attributes): self
    {
        $new = clone $this;
        array_push($new->attributes, ...array_values($attributes));

        return $new;
    }

    /**
     * @return string[] Classes found.
     * @psalm-return iterable<class-string>
     */
    public function find(): iterable
    {
        $countInterfaces = count($this->interfaces);
        $countAttributes = count($this->attributes);

        if ($countInterfaces === 0 && $countAttributes === 0 && $this->parentClass === null) {
            return [];
        }

        $this->scanFiles();

        $classesToFind = get_declared_classes();
        $isWindows = DIRECTORY_SEPARATOR === '\\';
        $directories = $this->directories;

        if ($isWindows) {
            /** @var string[] $directories */
            $directories = str_replace('/', '\\', $directories);
        }

        foreach ($classesToFind as $className) {
            $reflection = new ReflectionClass($className);

            if (!$reflection->isUserDefined()) {
                continue;
            }

            $matchedDirs = array_filter(
                $directories,
                static fn($directory) => str_starts_with($reflection->getFileName(), $directory),
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
                    $attributes,
                );

                if (count(array_intersect($this->attributes, $attributes)) !== $countAttributes) {
                    continue;
                }
            }

            if (($this->parentClass !== null) && !is_subclass_of($className, $this->parentClass)) {
                continue;
            }

            yield $className;
        }
    }

    /**
     * Find all PHP files and require each one so these could be further analyzed via reflection.
     * @psalm-suppress UnresolvableInclude
     */
    private function scanFiles(): void
    {
        $files = (new Finder())
            ->in($this->directories)
            ->name('*.php')
            ->sortByName()
            ->files();

        foreach ($files as $file) {
            require_once $file;
        }
    }
}
