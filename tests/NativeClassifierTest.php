<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests;

use Yiisoft\Classifier\NativeClassifier;
use Yiisoft\Classifier\ClassifierInterface;

class NativeClassifierTest extends BaseClassifierTest
{
    protected function createClassifier(string ...$dirs): ClassifierInterface
    {
        return new NativeClassifier(...$dirs);
    }
}
