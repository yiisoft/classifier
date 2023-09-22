<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

interface ClassifierInterface
{
    public function find(): iterable;
}
