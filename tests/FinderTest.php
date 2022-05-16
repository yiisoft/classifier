<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Classifier\Classifier;
use Yiisoft\Classifier\Tests\Support\Attributes\AuthorAttribute;
use Yiisoft\Classifier\Tests\Support\Author;
use Yiisoft\Classifier\Tests\Support\AuthorPost;
use Yiisoft\Classifier\Tests\Support\Interfaces\PostInterface;
use Yiisoft\Classifier\Tests\Support\Interfaces\UserInterface;
use Yiisoft\Classifier\Tests\Support\Post;
use Yiisoft\Classifier\Tests\Support\PostUser;
use Yiisoft\Classifier\Tests\Support\User;
use Yiisoft\Classifier\Tests\Support\UserSubclass;

final class FinderTest extends TestCase
{
    /**
     * @dataProvider interfacesDataProvider
     */
    public function testInterfaces(string|array $interfaces, array $expectedClasses)
    {
        $finder = new Classifier(__DIR__);
        $finder = $finder->withInterface($interfaces);

        $result = $finder->find();

        $this->assertEquals($expectedClasses, iterator_to_array($result));
    }

    /**
     * @dataProvider attributesDataProvider
     */
    public function testAttributes(string|array $attributes, array $expectedClasses)
    {
        $finder = new Classifier(__DIR__);
        $finder = $finder->withAttribute($attributes);

        $result = $finder->find();

        $this->assertEquals($expectedClasses, iterator_to_array($result));
    }

    /**
     * @dataProvider mixedDataProvider
     */
    public function testMixed(array $attributes, array $interfaces, array $expectedClasses)
    {
        $finder = new Classifier(__DIR__);
        $finder = $finder->withAttribute($attributes)->withInterface($interfaces);

        $result = $finder->find();

        $this->assertEquals($expectedClasses, iterator_to_array($result));
    }

    public function interfacesDataProvider(): array
    {
        return [
            [
                [],
                [],
            ],
            [
                PostInterface::class,
                [AuthorPost::class, Post::class, PostUser::class],
            ],
            [
                [PostInterface::class],
                [AuthorPost::class, Post::class, PostUser::class],
            ],
            [
                [UserInterface::class],
                [PostUser::class, User::class, UserSubclass::class],
            ],
            [
                [PostInterface::class, UserInterface::class],
                [PostUser::class],
            ],
        ];
    }

    public function attributesDataProvider(): array
    {
        return [
            [
                [],
                [],
            ],
            [
                AuthorAttribute::class,
                [Author::class, AuthorPost::class],
            ],
        ];
    }

    public function mixedDataProvider(): array
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
}
