<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use Symfony\Component\Finder\Finder;

final class PhpParserClassifier
{
    /**
     * @var string[]
     */
    private array $interfaces = [];
    /**
     * @var string[]
     */
    private array $attributes = [];
    private \PhpParser\Parser $parser;
    private NodeTraverser $traverser;
    private NodeFinder $nodeFinder;

    public function __construct(private string $directory)
    {
        $traverser = new NodeTraverser();
        $nameResolver = new NameResolver();
        $traverser->addVisitor($nameResolver);
        $this->parser = (new ParserFactory())
            ->create(ParserFactory::PREFER_PHP7);
        $this->traverser = $traverser;
        $this->nodeFinder = new NodeFinder();
    }

    public function withInterface(string|array $interfaces): self
    {
        $new = clone $this;
        foreach ((array) $interfaces as $interface) {
            $new->interfaces[] = $interface;
        }
        return $new;
    }

    public function withAttribute(string|array $attributes): self
    {
        $new = clone $this;
        foreach ((array) $attributes as $attribute) {
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

        $files = (new Finder())
            ->in($this->directory)
            ->name('*.php')
            ->sortByName()
            ->files();

        $interfaces = $this->interfaces;
        $attributes = $this->attributes;

        foreach ($files as $file) {
            $nodes = $this->parser->parse(file_get_contents($file->getRealPath()));
            $this->traverser->traverse($nodes);
            /**
             * @var $result Node\Stmt\Class_[]
             */
            $result = $this->nodeFinder->find(
                $nodes,
                function (Node $node) use ($interfaces, $countInterfaces, $attributes, $countAttributes) {
                    if (!$node instanceof Node\Stmt\Class_) {
                        return false;
                    }
                    $interfacesNames = array_map(fn (Node\Name $name) => $name->toString(), $node->implements);
                    if (count(array_intersect($interfaces, $interfacesNames)) !== $countInterfaces) {
                        return false;
                    }
                    $attributesNames = [];
                    foreach ($node->attrGroups as $attrGroup) {
                        foreach ($attrGroup->attrs as $attr) {
                            $attributesNames[] = $attr->name->toString();
                        }
                    }
                    return !(count(array_intersect($attributes, $attributesNames)) !== $countAttributes)


                     ;
                }
            );
            foreach ($result as $class) {
                yield $class->namespacedName->toString();
            }
        }
    }
}
