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
    private array $interfaces;
    /**
     * @var string[]
     */
    private array $attributes;
    private string $directory;

    public function __construct(string $directory)
    {
        $this->interfaces = [];
        $this->attributes = [];
        $this->directory = $directory;
    }

    public function withInterface(string|array $interfaces): self
    {
        $new = clone $this;
        foreach ((array)$interfaces as $interface) {
            $new->interfaces[] = $interface;
        }
        return $new;
    }

    public function withAttribute(string|array $attributes): self
    {
        $new = clone $this;
        foreach ((array)$attributes as $attribute) {
            $new->attributes[] = $attribute;
        }
        return $new;
    }

    public function find(): iterable
    {
        $countInterfaces = count($this->interfaces);
        $countAttributes = count($this->attributes);

        if ($countInterfaces === 0 && $countAttributes === 0) {
            return [];
        }

        $this->scanFiles();

        $classesToFind = get_declared_classes();

        foreach ($classesToFind as $className) {
            $reflection = new ReflectionClass($className);

            if ($countInterfaces > 0) {
                $interfaces = $reflection->getInterfaces();
                $interfaces = array_map(fn (ReflectionClass $class) => $class->getName(), $interfaces);

                if (count(array_intersect($this->interfaces, $interfaces)) !== $countInterfaces) {
                    continue;
                }
            }

            if ($countAttributes) {
                $attributes = $reflection->getAttributes();
                $attributes = array_map(fn (ReflectionAttribute $attribute) => $attribute->getName(), $attributes);

                if (count(array_intersect($this->attributes, $attributes)) !== $countAttributes) {
                    continue;
                }
            }

            yield $className;
        }
    }

    private function scanFiles(): void
    {
        /** @psalm-var string[] $files */
        $files = (new Finder())->in($this->directory)->name('*.php')->sortByName()->files();

        foreach ($files as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}
