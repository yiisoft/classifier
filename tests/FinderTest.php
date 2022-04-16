<?php
declare(strict_types=1);

namespace Yiisoft\Classifier\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Classifier\Classifier;
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
        $finder = $finder->implements($interfaces);

        $iterable = $finder->find();

        $this->assertEquals($expectedClasses, iterator_to_array($iterable));
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
                [Post::class, PostUser::class],
            ],
            [
                [PostInterface::class],
                [Post::class, PostUser::class],
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
}
