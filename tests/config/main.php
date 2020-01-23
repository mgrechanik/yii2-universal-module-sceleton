<?php
/**
 * This file is part of the mgrechanik/yii2-universal-module-sceleton library
 *
 * @copyright Copyright (c) Mikhail Grechanik <mike.grechanik@gmail.com>
 * @license https://github.com/mgrechanik/yii2-universal-module-sceleton/blob/master/LICENCE.md
 * @link https://github.com/mgrechanik/yii2-universal-module-sceleton
 */


return [
    'id' => 'testapp',
    'basePath' => __DIR__ . '/..',
    'vendorPath' => dirname(__DIR__) . '/../vendor',
    'aliases' => [
        '@mgrechanik/yiiuniversalmodule/tests' => '@app',
    ],
    'components' => [
    ],
];
