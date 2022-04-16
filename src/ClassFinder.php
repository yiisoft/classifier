<?php
declare(strict_types=1);

namespace Yiisoft\Classifier;

use Symfony\Component\Finder\Finder;

class ClassFinder
{
    /**
     * @var array
     */
    private array $interfaces;
    private string $directory;

    public function __construct(string $directory)
    {
        $this->interfaces = [];
        $this->directory = $directory;
    }

    public function implements(string|array $interfaces): ClassFinder
    {
        $new = clone $this;
        foreach ((array)$interfaces as $interface) {
            $new->interfaces[] = $interface;
        }
        return $new;
    }

    public function find(): array
    {
        $files = (new Finder())->in($this->directory)->name('*.php')->files();

        foreach ($files as $file) {
            require_once $file;
        }

        $classesToFind = get_declared_classes();

        $countInterfaces = count($this->interfaces);

        $result = [];

        foreach ($classesToFind as $className) {
            $interfaces = class_implements($className);
            if (count(array_intersect($this->interfaces, $interfaces)) >= $countInterfaces) {
                $result[] = $className;
            }
        }
        return $result;
    }
}
