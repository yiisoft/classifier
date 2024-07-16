<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Filter\Condition;

use ReflectionClass;
use Yiisoft\Classifier\Filter\FilterInterface;

class FilterAnd implements FilterInterface
{
    /**
     * @var FilterInterface[]
     */
    private array $filters;

    public function __construct(FilterInterface ...$filters)
    {
        if (count($filters) < 2) {
            throw new \InvalidArgumentException('At least 2 filters should be provided.');
        }
        $this->filters = $filters;
    }

    /**
     * @inheritDoc
     */
    public function match(ReflectionClass $reflectionClass): bool
    {
        foreach ($this->filters as $filter) {
            if (!$filter->match($reflectionClass)) {
                return false;
            }
        }

        return true;
    }
}
