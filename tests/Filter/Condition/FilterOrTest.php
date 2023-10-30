<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests\Filter\Condition;

use Yiisoft\Classifier\Filter\ClassAttributes;
use Yiisoft\Classifier\Filter\ClassImplements;
use Yiisoft\Classifier\Filter\Condition\FilterAnd;
use Yiisoft\Classifier\Filter\Condition\FilterOr;
use Yiisoft\Classifier\Filter\SubclassOf;
use Yiisoft\Classifier\Filter\TargetAttribute;
use Yiisoft\Classifier\Tests\Declarations\Car;
use Yiisoft\Classifier\Tests\Filter\BaseFilterTest;
use Yiisoft\Classifier\Tests\Support\Attributes\AuthorAttribute;
use Yiisoft\Classifier\Tests\Support\Author;
use Yiisoft\Classifier\Tests\Support\AuthorPost;
use Yiisoft\Classifier\Tests\Support\Interfaces\PostInterface;

class FilterOrTest extends BaseFilterTest
{
    public function testLessFilters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least 2 filters should be provided.');

        new FilterOr(new SubclassOf(Car::class));
    }

    public function matchProvider(): iterable
    {
        yield [
            new FilterOr(new SubclassOf(AuthorPost::class), new TargetAttribute(AuthorAttribute::class)),
            new \ReflectionClass(Author::class),
            true,
        ];
        yield [
            new FilterOr(new SubclassOf(AuthorPost::class), new ClassImplements(PostInterface::class)),
            new \ReflectionClass(Author::class),
            false,
        ];
    }
}
