<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests\Benchmark;

use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use Yiisoft\Classifier\Classifier;
use Yiisoft\Classifier\Tests\Support\Interfaces\PostInterface;
use Yiisoft\Classifier\Tests\Support\Interfaces\UserInterface;

final class EngineBench
{
    private Classifier $finder;

    public function beforeTest()
    {
        $this->finder = new Classifier(__DIR__);
    }

    /**
     * @BeforeMethods("beforeTest")
     * @ParamProviders("dataProviderInterfaces")
     * @Revs(1000)
     */
    public function benchTest(array $interfaces): void
    {
        $finder = $this->finder->withInterface($interfaces);

        $finder->find();
    }

    public function dataProviderInterfaces(): array
    {
        return [
            [PostInterface::class],
            [UserInterface::class],
        ];
    }
}
