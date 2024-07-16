<?php

declare(strict_types=1);

namespace Benchmark;

use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use Yiisoft\Classifier\ClassifierInterface;
use Yiisoft\Classifier\Filter\ClassImplements;
use Yiisoft\Classifier\Filter\SubclassOf;
use Yiisoft\Classifier\NativeClassifier;
use Yiisoft\Classifier\Tests\Support\Interfaces\PostInterface;
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
            dirname(__DIR__) . '/Support',
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

    public function provideClassifiersWithFilters(): \Generator
    {
        $dirs = [
            dirname(__DIR__, 2) . '/vendor',
            dirname(__DIR__) . '/Declarations',
            dirname(__DIR__) . '/Support',
        ];
        $filters = [new ClassImplements(PostInterface::class), new SubclassOf(\Traversable::class)];
        yield 'Native' => ['classifier' => NativeClassifier::class, 'dirs' => $dirs, 'filters' => $filters];
        yield 'Tokenizer' => ['classifier' => TokenizerClassifier::class, 'dirs' => $dirs, 'filters' => $filters];
    }

    #[ParamProviders(['provideClassifiersWithFilters'])]
    public function benchClassifierWithFilters(array $params): void
    {
        /** @var class-string<ClassifierInterface> $classifier */
        $classifier = $params['classifier'];
        $classifierInstance = new $classifier(...$params['dirs']);
        $classifierInstance->withFilter(...$params['filters'])->find();
    }
}
