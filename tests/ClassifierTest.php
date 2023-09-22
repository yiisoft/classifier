<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests;

use Yiisoft\Classifier\Classifier;
use Yiisoft\Classifier\ClassifierInterface;

class ClassifierTest extends BaseClassifierTest
{
    protected function createClassifier(string ...$dirs): ClassifierInterface
    {
        return new Classifier(...$dirs);
    }
}
