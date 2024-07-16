<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests\Declarations;

class ClassWithAnonymous
{
    public function bar()
    {
        $class = new class () {
        };
    }
}
