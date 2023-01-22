<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests\Benchmark;

use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use Yiisoft\Classifier\Classifier;
use Yiisoft\Classifier\Tests\Support\Interfaces\PostInterface;
use Yiisoft\Classifier\Tests\Support\Interfaces\UserInterface;

final class EngineBench
{
    /**
     * @ParamProviders("dataProviderInterfaces")
     * @Revs(1000)
     */
    public function benchFind(array $interfaces): void
    {
        $finder = new Classifier(__DIR__);
        $finder = $finder->withInterface($interfaces);

        $finder->find();
    }

    public function dataProviderInterfaces(): array
    {
        return [
            [PostInterface::class],
            [UserInterface::class]
        ];
    }
}
