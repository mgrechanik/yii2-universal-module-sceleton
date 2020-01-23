<?php
/**
 * This file is part of the mgrechanik/yii2-universal-module-sceleton library
 *
 * @copyright Copyright (c) Mikhail Grechanik <mike.grechanik@gmail.com>
 * @license https://github.com/mgrechanik/yii2-universal-module-sceleton/blob/master/LICENCE.md
 * @link https://github.com/mgrechanik/yii2-universal-module-sceleton
 */

namespace mgrechanik\yiiuniversalmodule\tests;

use yii\helpers\ArrayHelper;

/**
 * Creating applications
 */
class AppFactory
{
    /**
     * @var array Application's config after merging
     */
    protected static $config;
    
    /**
     * Get new Application 
     * @param string $appClass Application class name
     */
    public static function getApplication($appClass = '\yii\console\Application')
    {
        return new $appClass(self::getConfig());
    }
    
    /**
     * Set singleton definitions, which needs to be mocked
     */
    public static function setSingletons()
    {
        // old singletons will be replaced by empty new definitions
    }    
    
    /**
     * Get the main config
     * 
     * @return array Config
     */
    protected static function getConfig()
    {
        if (!is_null(self::$config)) {
            return self::$config;
        }
        
        $configDir = __DIR__ . '/config';
        
        if (file_exists($configDir . '/main-local.php')) {
            self::$config = ArrayHelper::merge(
                require($configDir . '/main.php'),
                require($configDir . '/main-local.php')
            );
        } else {
            self::$config = require($configDir . '/main.php');
        } 
        
        return self::$config;
    }
}