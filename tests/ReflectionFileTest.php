<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Classifier\ReflectionFile;
use Yiisoft\Classifier\Tests\Support\User;

class ReflectionFileTest extends TestCase
{
    public function testPsr4File(): void
    {
        $reflectionFile = new ReflectionFile(__DIR__ . '/Support/User.php');

        $this->assertNotEmpty($reflectionFile->getDeclarations());
        $this->assertContains(User::class, $reflectionFile->getDeclarations());
    }

    public function testNamespaceDeclaration(): void
    {
        $reflectionFile = new ReflectionFile(__DIR__ . '/Support/namespace.php');

        $this->assertCount(2, $reflectionFile->getDeclarations());
        $this->assertContains(\Support\Entity\Person::class, $reflectionFile->getDeclarations());
    }
}
