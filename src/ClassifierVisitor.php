<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal Visitor for Classifier
 */
final class ClassifierVisitor extends NodeVisitorAbstract
{
    /**
     * @var array<class-string>
     */
    private array $classNames = [];

    /**
     * @param \Closure(class-string): bool $shouldSkipClass
     */
    public function __construct(private \Closure $shouldSkipClass)
    {
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            /**
             * @psalm-var class-string|null $className
             */
            $className = $node->namespacedName?->toString();
            if ($className !== null && !($this->shouldSkipClass)($className)) {
                $this->classNames[] = $className;
            }
        }

        return parent::enterNode($node);
    }

    /**
     * @return array<class-string>
     */
    public function getClassNames(): array
    {
        return $this->classNames;
    }
}
