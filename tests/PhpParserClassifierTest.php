<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests;

use Yiisoft\Classifier\PhpParserClassifier;

final class PhpParserClassifierTest extends BaseClassifierTest
{
    protected function createClassifier(string $directory): PhpParserClassifier
    {
        return new PhpParserClassifier($directory);
    }
}
