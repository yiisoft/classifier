<?php

declare(strict_types=1);

namespace Yiisoft\Classifier\Tests\Support;

use Yiisoft\Classifier\Tests\Support\Interfaces\PostInterface;
use Yiisoft\Classifier\Tests\Support\Interfaces\UserInterface;

class PostUser implements UserInterface, PostInterface {}
