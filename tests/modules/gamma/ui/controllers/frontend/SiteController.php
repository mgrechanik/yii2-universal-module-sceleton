<?php
/**
 * This file is part of the mgrechanik/yii2-universal-module-sceleton library
 *
 * @copyright Copyright (c) Mikhail Grechanik <mike.grechanik@gmail.com>
 * @license https://github.com/mgrechanik/yii2-universal-module-sceleton/blob/master/LICENCE.md
 * @link https://github.com/mgrechanik/yii2-universal-module-sceleton
 */

namespace mgrechanik\yiiuniversalmodule\tests\modules\gamma\ui\controllers\frontend;

class SiteController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
