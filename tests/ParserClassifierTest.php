<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests;

use Yiisoft\Classifier\ClassifierInterface;
use Yiisoft\Classifier\ParserClassifier;

class ParserClassifierTest extends BaseClassifierTest
{
    protected function createClassifier(string ...$dirs): ClassifierInterface
    {
        return new ParserClassifier(...$dirs);
    }
}
