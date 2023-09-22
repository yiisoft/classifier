<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;

final class PhpParserClassifier extends AbstractClassifier
{
    private Parser $parser;
    private NodeTraverser $nodeTraverser;

    public function __construct(string $directory, string ...$directories)
    {
        parent::__construct($directory, ...$directories);
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());

        $this->nodeTraverser = $traverser;
    }

    /**
     * @return iterable<class-string>
     */
    protected function getAvailableClasses(): iterable
    {
        $files = $this->getFiles();
        $visitor = new ClassifierVisitor($this->interfaces, $this->attributes, $this->parentClass);
        $this->nodeTraverser->addVisitor($visitor);

        foreach ($files as $file) {
            $nodes = $this->parser->parse($file->getContents());
            if ($nodes !== null) {
                $this->nodeTraverser->traverse($nodes);
            }
        }

        yield from new \ArrayIterator($visitor->getClassNames());
    }
}
