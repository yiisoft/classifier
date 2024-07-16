<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Classifier\ClassifierInterface;
use Yiisoft\Classifier\Filter\ClassAttributes;
use Yiisoft\Classifier\Filter\ClassImplements;
use Yiisoft\Classifier\Filter\SubclassOf;
use Yiisoft\Classifier\Filter\TargetAttribute;
use Yiisoft\Classifier\Tests\Support\Attributes\AuthorAttribute;
use Yiisoft\Classifier\Tests\Support\Attributes\UserAttribute;
use Yiisoft\Classifier\Tests\Support\Author;
use Yiisoft\Classifier\Tests\Support\AuthorPost;
use Yiisoft\Classifier\Tests\Support\Dir1\UserInDir1;
use Yiisoft\Classifier\Tests\Support\Dir2\UserInDir2;
use Yiisoft\Classifier\Tests\Support\Interfaces\PostInterface;
use Yiisoft\Classifier\Tests\Support\Interfaces\UserInterface;
use Yiisoft\Classifier\Tests\Support\Post;
use Yiisoft\Classifier\Tests\Support\PostUser;
use Yiisoft\Classifier\Tests\Support\SuperSuperUser;
use Yiisoft\Classifier\Tests\Support\SuperUser;
use Yiisoft\Classifier\Tests\Support\User;
use Yiisoft\Classifier\Tests\Support\UserSubclass;

abstract class BaseClassifierTest extends TestCase
{
    public function testMultipleUse(): void
    {
        $dirs = [__DIR__ . '/Support/Dir1', __DIR__ . '/Support/Dir2'];
        $finder = $this->createClassifier(...$dirs);
        $finder = $finder->withFilter(new ClassImplements(UserInterface::class));

        $result = $finder->find();

        $this->assertEqualsCanonicalizing(iterator_to_array($finder->find()), iterator_to_array($result));
    }

    public function testMultipleDirectories(): void
    {
        $dirs = [__DIR__ . '/Support/Dir1', __DIR__ . '/Support/Dir2'];
        $finder = $this->createClassifier(...$dirs);
        $finder = $finder->withFilter(new ClassImplements(UserInterface::class));

        $result = $finder->find();

        $this->assertEqualsCanonicalizing([UserInDir1::class, UserInDir2::class], iterator_to_array($result));
    }

    /**
     * @dataProvider interfacesDataProvider
     */
    public function testInterfaces(string $directory, array $interfaces, array $expectedClasses): void
    {
        $finder = $this->createClassifier($directory);
        $finder = $finder->withFilter(new ClassImplements(...$interfaces));

        $result = $finder->find();

        $this->assertEqualsCanonicalizing($expectedClasses, iterator_to_array($result));
    }

    public static function interfacesDataProvider(): array
    {
        return [
            [
                __DIR__,
                [],
                [],
            ],
            [
                __DIR__,
                [PostInterface::class],
                [AuthorPost::class, Post::class, PostUser::class],
            ],
            [
                __DIR__,
                [PostInterface::class],
                [AuthorPost::class, Post::class, PostUser::class],
            ],
            [
                __DIR__,
                [UserInterface::class],
                [
                    UserInDir1::class,
                    UserInDir2::class,
                    PostUser::class,
                    SuperSuperUser::class,
                    SuperUser::class,
                    User::class,
                    UserSubclass::class,
                ],
            ],
            [
                __DIR__,
                [PostInterface::class, UserInterface::class],
                [PostUser::class],
            ],
            [
                __DIR__ . '/Support/Dir1',
                [UserInterface::class],
                [UserInDir1::class],
            ],
            [
                __DIR__ . '/Support/Dir2',
                [UserInterface::class],
                [UserInDir2::class],
            ],
        ];
    }

    /**
     * @dataProvider attributesDataProvider
     */
    public function testAttributes(array $attributes, array $expectedClasses): void
    {
        $finder = $this->createClassifier(__DIR__);
        $finder = $finder->withFilter(new ClassAttributes(...$attributes));

        $result = $finder->find();

        $this->assertEqualsCanonicalizing($expectedClasses, iterator_to_array($result));
    }

    /**
     * @dataProvider targetAttributeDataProvider
     */
    public function testTargetAttribute(string $attribute, array $expectedClasses): void
    {
        $finder = $this->createClassifier(__DIR__);
        $finder = $finder->withFilter(new TargetAttribute($attribute));

        $result = $finder->find();

        $this->assertEqualsCanonicalizing($expectedClasses, iterator_to_array($result));
    }

    /**
     * @dataProvider parentClassDataProvider
     */
    public function testParentClass(string $parent, array $expectedClasses): void
    {
        $finder = $this->createClassifier(__DIR__);
        $finder = $finder->withFilter(new SubclassOf($parent));

        $result = $finder->find();

        $this->assertEqualsCanonicalizing($expectedClasses, iterator_to_array($result));
    }

    public static function attributesDataProvider(): array
    {
        return [
            [
                [],
                [],
            ],
            [
                [AuthorAttribute::class, UserAttribute::class],
                [Author::class, AuthorPost::class],
            ],
        ];
    }

    public static function targetAttributeDataProvider(): array
    {
        return [
            [
                UserSubclass::class,
                [],
            ],
            [
                UserAttribute::class,
                [Author::class, AuthorPost::class],
            ],
            [
                AuthorAttribute::class,
                [Author::class, AuthorPost::class],
            ],
        ];
    }

    /**
     * @dataProvider mixedDataProvider
     */
    public function testMixed(array $attributes, array $interfaces, array $expectedClasses): void
    {
        $finder = $this->createClassifier(__DIR__);
        $finder = $finder
            ->withFilter(new ClassAttributes(...$attributes), new ClassImplements(...$interfaces));

        $result = $finder->find();

        $this->assertEqualsCanonicalizing($expectedClasses, iterator_to_array($result));
    }

    public static function mixedDataProvider(): array
    {
        return [
            [
                [],
                [],
                [],
            ],
            [
                [AuthorAttribute::class],
                [PostInterface::class],
                [AuthorPost::class],
            ],
        ];
    }

    public static function parentClassDataProvider(): array
    {
        return [
            [
                User::class,
                [SuperSuperUser::class, SuperUser::class],
            ],
        ];
    }

    abstract protected function createClassifier(string ...$dirs): ClassifierInterface;
}
