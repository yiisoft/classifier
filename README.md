<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px" alt="Yii">
    </a>
    <h1 align="center">Yii Classifier</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/classifier/v/stable.png)](https://packagist.org/packages/yiisoft/classifier)
[![Total Downloads](https://poser.pugx.org/yiisoft/classifier/downloads.png)](https://packagist.org/packages/yiisoft/classifier)
[![Build status](https://github.com/yiisoft/classifier/workflows/build/badge.svg)](https://github.com/yiisoft/classifier/actions?query=workflow%3Abuild)
[![Code Coverage](https://codecov.io/gh/yiisoft/classifier/branch/master/graph/badge.svg)](https://codecov.io/gh/yiisoft/classifier)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fclassifier%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/classifier/master)
[![static analysis](https://github.com/yiisoft/classifier/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/classifier/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/classifier/coverage.svg)](https://shepherd.dev/github/yiisoft/classifier)
[![psalm-level](https://shepherd.dev/github/yiisoft/classifier/level.svg)](https://shepherd.dev/github/yiisoft/classifier)

Classifier traverses file system to find classes by a certain criteria.

## Requirements

- PHP 8.0 or higher.

## Installation

The package could be installed with [Composer](https://getcomposer.org):

```shell
composer require yiisoft/classifier
```

## Documentation

Usage of classifier is the following:

```php
use \Yiisoft\Classifier\Classifier;
use \Psr\SimpleCache\CacheInterface;

$cacheInstances = (new Classifier('src'))
    ->withInterface(CacheInterface::class) // can use ->withParentClass() instead
    ->withAttribute(MyAttribute::class)
    ->find();
```

You specify one more directories to traverse, interfaces, base classes, attributes to search for and call `find()`
method which returns a list of classes found.

- [Internals](docs/internals.md)

If you need help or have a question, the [Yii Forum](https://forum.yiiframework.com/c/yii-3-0/63) is a good place for that.
You may also check out other [Yii Community Resources](https://www.yiiframework.com/community).

## License

The Yii Classifier is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
