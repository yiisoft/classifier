<?php

declare(strict_types=1);

namespace Benchmark;

use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use Yiisoft\Classifier\NativeClassifier;
use Yiisoft\Classifier\TokenizerClassifier;

#[Iterations(5)]
#[Revs(1000)]
final class ClassifierBench
{
    public function provideClassifiers(): \Generator
    {
        $dirs = [
            dirname(__DIR__, 2) . '/vendor',
            dirname(__DIR__) . '/Declarations',
        ];

        yield 'Native' => ['classifier' => NativeClassifier::class, 'dirs' => $dirs];
        yield 'Tokenizer' => ['classifier' => TokenizerClassifier::class, 'dirs' => $dirs];
    }

    #[ParamProviders(['provideClassifiers'])]
    public function benchClassifier(array $params): void
    {
        $classifier = $params['classifier'];
        $classifierInstance = new $classifier(...$params['dirs']);
        $classifierInstance->find();
    }
}
