<?php
/**
 * This file is part of the mgrechanik/yii2-universal-module-sceleton library
 *
 * @copyright Copyright (c) Mikhail Grechanik <mike.grechanik@gmail.com>
 * @license https://github.com/mgrechanik/yii2-universal-module-sceleton/blob/master/LICENCE.md
 * @link https://github.com/mgrechanik/yii2-universal-module-sceleton
 */

namespace mgrechanik\yiiuniversalmodule\tests;

/**
 * Basic Test Case
 */
abstract class TestWebAppCase extends TestCase
{
    protected function mockApplication($appClass = '\yii\web\Application')
    {
        AppFactory::getApplication($appClass);
        AppFactory::setSingletons();
    }
}