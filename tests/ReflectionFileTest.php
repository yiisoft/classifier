<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Classifier\ReflectionFile;
use Yiisoft\Classifier\Tests\Declarations\StatusEnum;
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
        $reflectionFile = new ReflectionFile(__DIR__ . '/Declarations/namespace.php');

        $this->assertCount(2, $reflectionFile->getDeclarations());
        $this->assertContains(\Support\Entity\Person::class, $reflectionFile->getDeclarations());
    }


    public function testEnumDeclaration(): void
    {
        $reflectionFile = new ReflectionFile(__DIR__ . '/Declarations/StatusEnum.php');

        $this->assertCount(1, $reflectionFile->getDeclarations());
        $this->assertEquals(StatusEnum::class, $reflectionFile->getDeclarations()[0]);
    }

    public function testWithoutNamespace(): void
    {
        $reflectionFile = new ReflectionFile(__DIR__ . '/Declarations/ClassWithoutNamespace.php');

        $this->assertCount(1, $reflectionFile->getDeclarations());
        $this->assertEquals(\Person::class, $reflectionFile->getDeclarations()[0]);
    }

    public function testContainingClassKeyword(): void
    {
        $reflectionFile = new ReflectionFile(__DIR__ . '/Declarations/Car.php');

        $this->assertCount(1, $reflectionFile->getDeclarations());
        $this->assertEquals(\Car::class, $reflectionFile->getDeclarations()[0]);
    }

    public function testBrokenClass(): void
    {
        $reflectionFile = new ReflectionFile(__DIR__ . '/Declarations/ClassWithAnonymous.php');

        $this->assertCount(1, $reflectionFile->getDeclarations());
    }
}
