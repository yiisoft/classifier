<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal for PhpParserClassifier
 */
final class ClassifierVisitor extends NodeVisitorAbstract
{
    /**
     * @var array<class-string>
     */
    private array $classNames = [];

    /**
     * @psalm-param class-string $allowedParentClass
     */
    public function __construct(
        private array $allowedInterfaces,
        private array $allowedAttributes,
        private ?string $allowedParentClass = null
    ) {
    }

    public function enterNode(Node $node)
    {
        if (($node instanceof Class_) && !$this->skipClass($node)) {
            /**
             * @var class-string $className
             * @psalm-suppress PossiblyNullReference checked in {@see skipClass} method.
             */
            $className = $node->namespacedName->toString();
            $this->classNames[] = $className;
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

    private function skipClass(Class_ $class): bool
    {
        if ($class->namespacedName === null || $class->isAnonymous()) {
            return true;
        }
        $className = $class->namespacedName->toString();
        $interfacesNames = class_implements($className);
        if (
            $interfacesNames !== false &&
            count(array_intersect($this->allowedInterfaces, $interfacesNames)) !== count($this->allowedInterfaces)
        ) {
            return true;
        }
        $attributesNames = [];
        foreach ($class->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $attributesNames[] = $attr->name->toString();
            }
        }
        if (count(array_intersect($this->allowedAttributes, $attributesNames)) !== count($this->allowedAttributes)) {
            return true;
        }

        $classParents = class_parents($className);

        return ($this->allowedParentClass !== null && $classParents !== false) &&
            !in_array($this->allowedParentClass, $classParents, true);
    }
}
