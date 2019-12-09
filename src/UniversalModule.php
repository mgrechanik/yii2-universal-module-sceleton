<?php

namespace mgrechanik\yiiuniversalmodule;

use Yii;
use yii\console\Application as ConsoleApplication;

/**
 * Universal module is a sceleton of the module which is easy to be used
 * both with Advanced and Basic application templates
 */
class UniversalModule extends \yii\base\Module
{
    /**
     * For situation when module is accessed from console application.
     * It is auto detected
     */
    const MODULE_CONSOLE_MODE = 'console';

    /**
     * For backend of the module
     */
    const MODULE_BACKEND_MODE = 'backend';

    /**
     * For frontend of the module
     */
    const MODULE_FRONTEND_MODE = 'frontend';

    /**
     * For both frontend and backend of the module
     * when it is set up for Basic application template
     */
    const MODULE_BACKEND_AND_FRONTEND_MODE = 'backend and frontend';

    /**
     * @var array The map of module's frontend controllers
     * it's format:
     * [
     *      // The list of Controller Ids for which Controller classes will be
     *      // defined in Yii2 way.
     *      Controller Id1, Controller Id2, ...
     *      // Or use this notation when you want to set up Controller class name manually (*).
     *      Controller Id3 => Controller Class Name,
     *      // ...
     * ]
     *
     * Controller namespace is set up automatically to
     * __ModuleNamespace__\ui\controllers\frontend
     *
     * (*) Controller class name must be without namespace
     *
     * Example: [
     *      'default',          // It will become 'DefaultController',
     *      'admin-default',    // It will become 'AdminDefaultController',
     *      // or manually
     *      'some' => 'SomeSpecificController',
     * ]
     */
    public $frontendControllers = [];

    /**
     * @var array The map of module's backend controllers
     * Look at [[$frontendControllers]] for details but namespace will be set
     * to __ModuleNamespace__\ui\controllers\backend
     */
    public $backendControllers = [];

    /**
     * @var string Use it to set up layout for backend controllers in Basic application
     */
    public $backendLayout;

    /**
     * @var array Properties to set up to each backend controller.
     * Useful for example to restrict access to all such controllers
     * via AccessControl filter (['as access' => ['class' => AccessControl::class],..])
     */
    public $backendControllerConfig =[];

    /**
     * @var array Properties to set up to each frontend controller.
     */
    public $frontendControllerConfig =[];

    /**
     * @var callable Function to adjust this module's controllerMap.
     * it's signature:
     * function($map) { ...; return $map; }
     */
    public $controllerMapAdjustCallback;

    /**
     * {@inheritdoc}
     * We do not use this functionality(*) to prevent searching controllers
     * except those we define in [[frontendControllers]] and [[backendControllers]].
     *
     * (*)Though we use it to set up controller namespace for console application
     */
    public $controllerNamespace = 'doNotUseThisFunctionality';

    /**
     * @var string Mode in which this module is executed
     */
    protected $mode;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof ConsoleApplication) {
            $this->mode = self::MODULE_CONSOLE_MODE;
            return;
        }
        // Setting up a controller map
        $this->initControllerMap();
    }

    /**
     * {@inheritdoc}
     */
    public function createController($route)
    {
        $result = parent::createController($route);
        if (!(Yii::$app instanceof ConsoleApplication)) {
            $controller = $result[0];
            $controllerClassName = get_class($controller);
            $dir = '@' . str_replace('\\', '/', $this->getNamespace());
            $controllerConfig = [];
            if (strpos($controllerClassName, 'ui\\controllers\\backend\\')) {
                // backend controller
                $viewPath = $dir . '/ui/views/backend';
                if ($this->backendLayout) {
                    $this->layout = $this->backendLayout;
                }
                if (!empty($this->backendControllerConfig)) {
                    $controllerConfig = $this->backendControllerConfig;
                }
            } elseif (strpos($controllerClassName, 'ui\\controllers\\frontend\\')) {
                // frontend controller
                $viewPath = $dir . '/ui/views/frontend';
                if (!empty($this->frontendControllerConfig)) {
                    $controllerConfig = $this->frontendControllerConfig;
                }
            } else {
                throw new \Exception('Controller namespace is defined incorrectly');
            }
            $this->setViewPath($viewPath);
            if (!empty($controllerConfig)) {
                Yii::configure($controller, $controllerConfig);
            }
        }
        return $result;
    }


    /**
     * @param string $mode The mode this module is executed with
     * @throws \yii\base\InvalidConfigException
     */
    public function setMode(string $mode)
    {
        if (in_array($mode, [
            self::MODULE_BACKEND_MODE,
            self::MODULE_FRONTEND_MODE,
            self::MODULE_BACKEND_AND_FRONTEND_MODE,
            self::MODULE_CONSOLE_MODE
        ])) {
            $this->mode = $mode;
        } else {
            throw new \yii\base\InvalidConfigException('Wrong application mode');
        }
    }

    /**
     * Initializing Controller map according to module's mode
     */
    protected function initControllerMap()
    {
        if (!empty($this->controllerMap)) {
            return;
        }

        $controllerMap = [];
        $namespace = $this->getNamespace();
        $backNS = $namespace . '\\ui\\controllers\\backend\\';
        $frontNS = $namespace . '\\ui\\controllers\\frontend\\';

        if (in_array($this->mode, [self::MODULE_FRONTEND_MODE, self::MODULE_BACKEND_AND_FRONTEND_MODE])) {
            // adding frontend controllers
            $this->addToControllerMap($controllerMap, $this->frontendControllers, $frontNS);
        }

        if (in_array($this->mode, [self::MODULE_BACKEND_MODE, self::MODULE_BACKEND_AND_FRONTEND_MODE])) {
            // adding backend controllers
            $this->addToControllerMap($controllerMap, $this->backendControllers, $backNS);
        }

        if (is_callable($this->controllerMapAdjustCallback)) {
            $controllerMap = call_user_func($this->controllerMapAdjustCallback, $controllerMap);
        }

        $this->controllerMap = $controllerMap;
    }

    /**
     * Adding controllers to controller map
     *
     * @param array $controllerMap
     * @param array $map
     * @param string $namespace
     */
    protected function addToControllerMap(&$controllerMap, $map, $namespace)
    {
        foreach ($map as $action => $controllerName) {
            if (is_int($action)) {
                $action = $controllerName;
                $controllerName = $this->getFullControllerName($action);
            }
            $controllerMap[$action] = $namespace . $controllerName;
        }
    }

    /**
     * By short name like 'admin' it will return full name like AdminController
     *
     * @param $shortName
     * @return string
     */
    private function getFullControllerName($shortName)
    {
        return \preg_replace_callback('%-([a-z0-9_])%i', function ($matches) {
            return ucfirst($matches[1]);
        }, ucfirst($shortName)) . 'Controller';
    }

    /**
     * @return string Namespace of the module
     */
    private function getNamespace()
    {
        $className = get_class($this);
        return substr($className, 0, strripos($className, '\\'));
    }
}
