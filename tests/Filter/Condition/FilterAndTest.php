<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests\Filter\Condition;

use Yiisoft\Classifier\Filter\ClassAttributes;
use Yiisoft\Classifier\Filter\ClassImplements;
use Yiisoft\Classifier\Filter\Condition\FilterAnd;
use Yiisoft\Classifier\Filter\SubclassOf;
use Yiisoft\Classifier\Tests\Declarations\Car;
use Yiisoft\Classifier\Tests\Filter\BaseFilterTest;
use Yiisoft\Classifier\Tests\Support\Attributes\AuthorAttribute;
use Yiisoft\Classifier\Tests\Support\Author;
use Yiisoft\Classifier\Tests\Support\Interfaces\PostInterface;
use Yiisoft\Classifier\Tests\Support\SuperUser;
use Yiisoft\Classifier\Tests\Support\User;
use Yiisoft\Classifier\Tests\Support\UserSubInterface;

class FilterAndTest extends BaseFilterTest
{
    public function testLessFilters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least 2 filters should be provided.');

        new FilterAnd(new SubclassOf(Car::class));
    }

    public function matchProvider(): iterable
    {
        yield [
            new FilterAnd(new SubclassOf(User::class), new ClassImplements(UserSubInterface::class)),
            new \ReflectionClass(SuperUser::class),
            true,
        ];
        yield [
            new FilterAnd(new SubclassOf(PostInterface::class), new ClassAttributes(AuthorAttribute::class)),
            new \ReflectionClass(Author::class),
            false,
        ];
    }
}
