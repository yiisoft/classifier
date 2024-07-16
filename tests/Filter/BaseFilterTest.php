<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests\Filter;

use PHPUnit\Framework\TestCase;
use Yiisoft\Classifier\Filter\FilterInterface;

abstract class BaseFilterTest extends TestCase
{
    /**
     * @dataProvider matchProvider
     */
    public function testMatch(FilterInterface $filter, \ReflectionClass $reflectionClass, bool $expectedResult): void
    {
        $this->assertSame($expectedResult, $filter->match($reflectionClass));
    }

    abstract public function matchProvider(): iterable;
}
