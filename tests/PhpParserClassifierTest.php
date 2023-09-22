<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests;

use Yiisoft\Classifier\ClassifierInterface;
use Yiisoft\Classifier\PhpParserClassifier;

class PhpParserClassifierTest extends BaseClassifierTest
{

    protected function createClassifier(string ...$dirs): ClassifierInterface
    {
        return new PhpParserClassifier(...$dirs);
    }
}
