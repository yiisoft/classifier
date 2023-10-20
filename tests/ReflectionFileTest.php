<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Classifier\ClassifierInterface;
use Yiisoft\Classifier\NativeClassifier;
use Yiisoft\Classifier\ReflectionFile;

class ReflectionFileTest extends TestCase
{
    public function testClasses()
    {
        $reflectionFile = new ReflectionFile(__DIR__ . '/Support/User.php');

        $this->assertNotEmpty($reflectionFile->getDeclarations());
    }
}
