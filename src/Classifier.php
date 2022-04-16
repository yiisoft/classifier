<?php
declare(strict_types=1);

namespace Yiisoft\Classifier;

use Symfony\Component\Finder\Finder;

final class Classifier
{
    /**
     * @var string[]
     */
    private array $interfaces;
    private string $directory;

    public function __construct(string $directory)
    {
        $this->interfaces = [];
        $this->directory = $directory;
    }

    public function implements(string|array $interfaces): Classifier
    {
        $new = clone $this;
        foreach ((array)$interfaces as $interface) {
            $new->interfaces[] = $interface;
        }
        return $new;
    }

    public function find(): iterable
    {
        $countInterfaces = count($this->interfaces);

        if ($countInterfaces === 0) {
            return [];
        }

        $this->scanFiles();

        $classesToFind = get_declared_classes();

        foreach ($classesToFind as $className) {
            $interfaces = class_implements($className);
            if (count(array_intersect($this->interfaces, $interfaces)) === $countInterfaces) {
                yield $className;
            }
        }
    }

    private function scanFiles(): void
    {
        $files = (new Finder())->in($this->directory)->name('*.php')->files();

        foreach ($files as $file) {
            require_once $file;
        }
    }
}
