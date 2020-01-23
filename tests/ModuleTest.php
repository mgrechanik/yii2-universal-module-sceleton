<?php
/**
 * This file is part of the mgrechanik/yii2-universal-module-sceleton library
 *
 * @copyright Copyright (c) Mikhail Grechanik <mike.grechanik@gmail.com>
 * @license https://github.com/mgrechanik/yii2-universal-module-sceleton/blob/master/LICENCE.md
 * @link https://github.com/mgrechanik/yii2-universal-module-sceleton
 */

namespace mgrechanik\yiiuniversalmodule\tests;

use Yii;
use mgrechanik\yiiuniversalmodule\tests\modules\alpha\Module as AlphaModule;
use mgrechanik\yiiuniversalmodule\tests\modules\beta\Module as BetaModule;
use mgrechanik\yiiuniversalmodule\tests\modules\gamma\Module as GammaModule;
use mgrechanik\yiiuniversalmodule\UniversalModule;

/**
 * Testing Universal module
 */
class ModuleTest extends TestWebAppCase
{
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    // test mode
    
    public function testRunningInConsoleMode()
    {
        $this->mockApplication('yii\console\Application');
        $module = $this->getAlphaModule();
        $this->assertEquals(UniversalModule::MODULE_CONSOLE_MODE, $module->mode);
    }    
    
    public function testRunningInFrontendMode()
    {
        $module = $this->getAlphaModule(['mode' => UniversalModule::MODULE_FRONTEND_MODE]);
        $this->assertEquals(UniversalModule::MODULE_FRONTEND_MODE, $module->mode);
    } 
    
    public function testWrongMode()
    {
        $this->expectExceptionMessage('Wrong application mode');
        $module = $this->getAlphaModule(['mode' => 'wrong mode']);
    }    
    
    // end test mode
    
    // test count controllers
    
    public function testNoControllerMapByDefault()
    {
        $module = $this->getAlphaModule(['mode' => UniversalModule::MODULE_BACKEND_AND_FRONTEND_MODE]);
        $this->assertEmpty($module->controllerMap);
    } 
    
    public function testOnlyTwoControllersInFrontendMode()
    {
        $module = $this->getAlphaModule([
            'mode' => UniversalModule::MODULE_FRONTEND_MODE,
            'frontendControllers' => ['c1', 'c2'],
        ]);
        //var_dump($module->controllerMap);
        $this->assertCount(2, $module->controllerMap);
    }     
    
    public function testNoControllersInFrontendMode()
    {
        $module = $this->getAlphaModule([
            'mode' => UniversalModule::MODULE_FRONTEND_MODE,
            'backendControllers' => ['c1', 'c2'],
        ]);
        $this->assertCount(0, $module->controllerMap);
    } 
    
    public function testOnlyTwoControllersInFrontendModeWithBackControllers()
    {
        $module = $this->getAlphaModule([
            'mode' => UniversalModule::MODULE_FRONTEND_MODE,
            'frontendControllers' => ['c1', 'c2'],
            'backendControllers' => ['c3', 'c4'],
        ]);
        $this->assertCount(2, $module->controllerMap);
    }   
    
    public function testOnlyOneControllersInBackendMode()
    {
        $module = $this->getAlphaModule([
            'mode' => UniversalModule::MODULE_BACKEND_MODE,
            'frontendControllers' => ['c1', 'c2'],
            'backendControllers' => ['c3'],
        ]);
        $this->assertCount(1, $module->controllerMap);
    }     
    
    public function testFourControllersInFrontendAndBackendMode()
    {
        $module = $this->getAlphaModule([
            'mode' => UniversalModule::MODULE_BACKEND_AND_FRONTEND_MODE,
            'frontendControllers' => ['c1', 'c2'],
            'backendControllers' => ['c3', 'c4'],
        ]);
        $this->assertCount(4, $module->controllerMap);
    } 
    
    public function testNotEmptyControllerMap()
    {
        $module = $this->getAlphaModule([
            'mode' => UniversalModule::MODULE_BACKEND_AND_FRONTEND_MODE,
            'frontendControllers' => ['c1', 'c2'],
            'backendControllers' => ['c3', 'c4'],
            'controllerMap' => ['action' => 'controller'],
        ]);
        $this->assertCount(1, $module->controllerMap);
    }    
    
    // end test count controllers
    
    // test controller namespace
    
    public function testMiddlePartControllersNamespace()
    {
        $module = $this->getAlphaModule([
            'mode' => UniversalModule::MODULE_BACKEND_AND_FRONTEND_MODE,
            'frontendControllers' => ['c1'],
            'backendControllers' => ['c3'],
        ]);
        $this->assertStringEndsWith('\ui\controllers\frontend\C1Controller', $module->controllerMap['c1']);
        $this->assertStringEndsWith('\ui\controllers\backend\C3Controller', $module->controllerMap['c3']);
    } 
    
    public function testFirstPartControllersNamespaceByDefaultFromModule()
    {
        $module = $this->getAlphaModule([
            'mode' => UniversalModule::MODULE_BACKEND_AND_FRONTEND_MODE,
            'frontendControllers' => ['c1'],
            'backendControllers' => ['c3'],
        ]);
        $this->assertStringStartsWith($this->getNamespace($module) . '\\ui', $module->controllerMap['c1']);
        $this->assertStringStartsWith($this->getNamespace($module) . '\\ui', $module->controllerMap['c3']);
    }    
    
    public function testFirstPartControllersNamespaceWithBaseControllerNamespace()
    {
        $module = $this->getAlphaModule([
            'mode' => UniversalModule::MODULE_BACKEND_AND_FRONTEND_MODE,
            'baseControllerNamespace' => 'mgrechanik\\somenamespace',
            'frontendControllers' => ['c1'],
            'backendControllers' => ['c3'],
        ]);
        $this->assertStringStartsWith('mgrechanik\\somenamespace\\ui', $module->controllerMap['c1']);
        $this->assertStringStartsWith('mgrechanik\\somenamespace\\ui', $module->controllerMap['c3']);
    } 
    
    // beta module
    
    public function testFirstPartControllersNamespaceOfBetaModuleByDefault()
    {
        $module = $this->getBetaModule([
            'mode' => UniversalModule::MODULE_BACKEND_AND_FRONTEND_MODE,
            'frontendControllers' => ['c1'],
            'backendControllers' => ['c3'],
        ]);
        $this->assertStringStartsWith($this->getNamespace($module) . '\\ui', $module->controllerMap['c1']);
        $this->assertStringStartsWith($this->getNamespace($module) . '\\ui', $module->controllerMap['c3']);
    }    

    public function testFirstPartControllersNamespaceOfBetaWithTakeControllersFromParentModule()
    {
        $module = $this->getBetaModule([
            'mode' => UniversalModule::MODULE_BACKEND_AND_FRONTEND_MODE,
            'frontendControllers' => ['c1'],
            'backendControllers' => ['c3'],
            'takeControllersFromParentModule' => true,
        ]);
        $alpha = $this->getAlphaModule();
        $this->assertStringStartsWith($this->getNamespace($alpha) . '\\ui', $module->controllerMap['c1']);
        $this->assertStringStartsWith($this->getNamespace($alpha) . '\\ui', $module->controllerMap['c3']);
    }    
    // end beta module
     
    // end test controller namespace
    
    // test controller name
    
    public function testControllersName()
    {
        $module = $this->getAlphaModule([
            'mode' => UniversalModule::MODULE_FRONTEND_MODE,
            'frontendControllers' => [
                'c1', 'two-words', 'some' => 'SomeSpecificController'],
        ]);
        $this->assertStringEndsWith('\\C1Controller', $module->controllerMap['c1']);
        $this->assertStringEndsWith('\\TwoWordsController', $module->controllerMap['two-words']);
        $this->assertStringEndsWith('\\SomeSpecificController', $module->controllerMap['some']);
    }     
    
    // end test controller name
    
    // test view path
    
    public function testViewDefaultPathAtFront()
    {
        $module = $this->getGammaModule([
            'mode' => UniversalModule::MODULE_FRONTEND_MODE,
        ]);
        $module->createController('site/index');
        $this->assertStringEndsWith('modules/gamma/ui/views/frontend', $module->viewPath);
    } 
    
    public function testViewDefaultPathAtBack()
    {
        $module = $this->getGammaModule([
            'mode' => UniversalModule::MODULE_BACKEND_MODE,
        ]);
        $module->createController('admin-default/index');
        $this->assertStringEndsWith('modules/gamma/ui/views/backend', $module->viewPath);
    }  
    
    public function testViewPathWithBaseControllerNamespace()
    {
        $gamma = $this->getGammaModule();
        $module = $this->getAlphaModule([
            'mode' => UniversalModule::MODULE_FRONTEND_MODE,
            'frontendControllers' => ['site'],
            'baseControllerNamespace' => $this->getNamespace($gamma)
        ]);
        $module->createController('site/index');
        $this->assertStringEndsWith('modules/gamma/ui/views/frontend', $module->viewPath);
    } 

    public function testCustomBaseViewsDir()
    {
        $module = $this->getGammaModule([
            'mode' => UniversalModule::MODULE_FRONTEND_MODE,
            'baseViewsPath' => '@app/some_custom_dir'
        ]);
        $module->createController('site/index');
        $this->assertStringEndsWith('/some_custom_dir/ui/views/frontend', $module->viewPath);
    }     
    
    // end test view path
    
    
    protected function getAlphaModule($moduleDefinition = [])
    {
        $moduleDefinition['class'] = AlphaModule::class;
        Yii::$app->setModule('alpha', $moduleDefinition);

        return Yii::$app->getModule('alpha');
    }
    
    protected function getBetaModule($moduleDefinition = [])
    {
        $moduleDefinition['class'] = BetaModule::class;
        Yii::$app->setModule('beta', $moduleDefinition);

        return Yii::$app->getModule('beta');
    }
    
    protected function getGammaModule($moduleDefinition = [])
    {
        $moduleDefinition['class'] = GammaModule::class;
        Yii::$app->setModule('gamma', $moduleDefinition);

        return Yii::$app->getModule('gamma');
    }    
    
    
    protected function getNamespace($object)
    {
        $className = get_class($object);
        return substr($className, 0, strripos($className, '\\'));
    }

}