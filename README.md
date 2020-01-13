# Yii2 universal module sceleton

[Русская версия](docs/README_ru.md)


## Table of contents

* [Goal](#goal)
* [Installing](#installing)
* [What it is about](#gist)
* [Using](#using)
* [Module settings](#settings)
* [Example with *Basic* template](#example-basic)
* [How-to](#recipe)


---

## Goal <span id="goal"></span>

This extension gives the structure of the module which:

1. will be self-sufficient and portable because it holds all his functionality in one place
2. logically divided at **backend** and **frontend** parts
3. easily connects both to *Advanced* and *Basic* application templates
4. with *Basic* application template:

    * connects to module section only one time
    * there is functionality of protection to all **backend** controllers  (for admin part of your web site) 
	
---
    
## Installing <span id="installing"></span>

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).:

Either run
```
composer require --prefer-dist mgrechanik/yii2-universal-module-sceleton
```

or add
```
"mgrechanik/yii2-universal-module-sceleton" : "~1.0.0"
```
to the require section of your `composer.json`

---

## What it is about  <span id="gist"></span>    

* By default module controllers are being searched automatically in it's ```$controllerNamespace```
* We do not use this functionality but define all our controllers in module's ```$controllerMap```
* But we do this not by ```$controllerMap``` property explicitly but define **backend** and **frontend** controllers separately
* Module has **the mode** <span id="mode"></span> which is set in config; according to this mode ```Controller Map```
will have **only** those controllers who fit the mode:
    * With **frontend** application of *Advanced* template we connect our module in ```'frontend'``` mode
    * With **backend** application of *Advanced* template we connect our module in  ```'backend'```  mode
    * With *Basic* template we can connect our module in two modes described above and also in ```'backend and frontend'``` mode when both controller types are accessible
* When module get the request it creates the controller from their map, figuring by it's namespace <span id="mknows"></span>  
whether it is **backend** or **frontend** controller to perform additional set up
* Module expects the next directory structure: <span id="dir-structure"></span>
```
Module_directory/
   ui/                                  // User Interface of the module
      controllers/
          backend/                      // Backend controllers like the next:
            AdminDefaultController.php  
            ...
          frontend/                     // Frontend controllers like the next: 
            DefaultController.php       
            ...
      views/                            // Views for corresponding controllers 
          backend/
            admin-default/
          frontend/        
            default/
   Module.php                           // module class
```   
---

## Using  <span id="using"></span>    

1) Generate, or create manually, your module class

2) Inherit your module class from universal module class
```php
use mgrechanik\yiiuniversalmodule\UniversalModule;

class YourModule extends UniversalModule
{
```
3) Now create (or generate) **frontend** controller  <span id="fcontroller"></span> 
* Take into consideration that it's ```namespace``` should be ```yourModuleNamespace\ui\controllers\frontend```
* Create all subdirs needed
* According to controller it's views will reside in ```@yourModuleNamespace/ui/views/frontend/YourControllerName/```
* We need to define this controller in **frontend** controller map of this module:
```php
class YourModule extends UniversalModule
{
    public $frontendControllers = [
        'default',
    ];
```
, where ```'default'``` match ```yourModuleNamespace\ui\controllers\frontend\DefaultController```.  
When the name and class of controller do not match use next definition form: ```'default2' => 'SomeDefaultController'```.

> **Always when you create new controller do not forget to define it in appropriate controller map of your module.**

You are not required to inherit your controller classes from any parent type.

4) Now create (or generate) **backend** controller <span id="bcontroller"></span>
* Logic is the same with 3), but it's ```namespace``` should be ```yourModuleNamespace\ui\controllers\backend```
* Define it in module at:
```php
class YourModule extends UniversalModule
{
    public $backendControllers = [
        'admin-default',
    ];
```
* It is handy to prefix **backend** controller names with **Admin**, so all backend urls could be
[set up](#recipe-admin-url) the way all of them will start with **admin/**

5) Done, your module is ready, you can **connect it to application**: <span id="setup"></span>

**config/main.php:**
```php
    // ...
    'modules' => [
        'yourModule' => [
            'class' => 'yourModuleNamespace\YourModule',
            'mode' => 'frontend',
        ],
```
, do not forget to define - [mode](#mode)

> It is comfortable to connect all such modules at first level of application modules, without nested modules 
> but like a simple list of modules we used to see at admin pages of popular **CMS**s, which also gives short urls.

---

## Module settings <span id="settings"></span>

[Connecting](#setup) module to application we can use next it's properties:

#### ```$mode``` - mode in which this module works
You are required to set up it. [Details](#mode)

#### ```$backendLayout``` - layout for backend controllers
Sets up ```layout``` to module when **backend** controller is requested.  
It is useful for *Basic* application template.

#### ```$frontendControllers``` - frontend controller map
[Details](#fcontroller)

#### ```$backendControllers``` - backend controller map
[Details](#bcontroller)

#### ```$controllerMapAdjustCallback``` - callback for final adjustment of controller map

After module's controller map is generated you can adjust it with this function 
which signature is: ```function($map) { ...; return $map; }```

#### ```$backendControllerConfig``` - **backend** controllers settings
When module [creates](#mknows) **backend** controller it could set up controller with these properties.

It is handy, for example, to restrict access to such controllers using yii filters connected like behaviors.

[Example of using](#example-basic). 

#### ```$frontendControllerConfig``` - **frontend** controllers settings
It is the same like ```$backendControllerConfig```

---

## Example of module's set up with *Basic* application template <span id="example-basic"></span>

Lets suppose that we have two modules we are talking about  - ```example``` and ```omega```.  
Here is working configs to set up these modules:

**config/params.php:**
```php
return [
    'backendLayout' => '//lte/main',
    'backendControllerConfig' => [
        'as backendaccess' => [
            'class' => \yii\filters\AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'ips' => ['54.54.22.44'],
                    'matchCallback' => function ($rule, $action){
                        $user = \Yii::$app->user;
                        return !$user->isGuest &&
                            ($user->id == 1);
                },
                ]
            ],
        ],
    ],	
  
];
```
At this config we gave permission to "admin pages" only to one user ```(id==1)```, with additional check for ```ip```.


**config/web.php:**
```php
    'components' => [
	//...
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'admin/<module:(example|omega)>-<controllersuffix>/<action:\w*>' =>
                    '<module>/admin-<controllersuffix>/<action>',
                'admin/<module:(example|omega)>-<controllersuffix>' =>
                    '<module>/admin-<controllersuffix>',
            ],
        ],	
    ],
    'modules' => [
        'example' => [
            'class' => 'modules\example\Module',
            'mode' => 'backend and frontend',
            'backendLayout' => $params['backendLayout'],
            'backendControllerConfig' => $params['backendControllerConfig'],
        ],
        'omega' => [
            'class' => 'modules\username1\omega\Module',
            'mode' => 'backend and frontend',
            'backendLayout' => $params['backendLayout'],
            'backendControllerConfig' => $params['backendControllerConfig'],
        ],        
    ], 
```

---

## How-to <span id="recipe"></span>

#### Make all admin urls start with ```/admin```  <span id="recipe-admin-url"></span>
Lets see *Basic* application template with two "our" modules connected to it:
```php
    'modules' => [
        'example' => [
            ...
        ],
        'omega' => [
            ...
        ],  
```
If we followed [advice](#bcontroller) above about naming of **backend** controllers  all of them have names like ```Admin...Controller```.  
So urls to them will be ```example/admin-default``` and ```omega/admin-default```.  
And we want all our admin urls to start with ```admin/```.

It is easily achived with the next two ```Url Rules``` for your ```urlManager```:
```php
	'urlManager' => [
		'enablePrettyUrl' => true,
		'showScriptName' => false,
		'rules' => [
			'admin/<module:(example|omega)>-<controllersuffix>/<action:\w*>' =>
				'<module>/admin-<controllersuffix>/<action>',
			'admin/<module:(example|omega)>-<controllersuffix>' =>
				'<module>/admin-<controllersuffix>',
		],
	],
```

#### Generating **backend** functionality with Gii CRUD generator   <span id="recipe-crud"></span>

You can easily generate CRUD functionality considering that:
* The ```name``` and the ```namespace``` of the **controller** should be choosen according to [documentation](#bcontroller)
* ```View Path``` should match [directory structure](#dir-structure) the module demands

#### How to connect the module to console application?   <span id="recipe-other-console"></span>

If our module has console commands who reside for example here:
```
Module_directory/
  console/
    commands/                // Directory for console commands
      HelloController.php
  Module.php
```	  
, then in the console application config this module is connected like:

```php
    'modules' => [
        'example' => [
            'class' => 'modules\example\Module',
            'controllerNamespace' => 'yourModuleNamespace\console\commands',
        ],
    ],
```

#### Where to put all other module's functionality?   <span id="recipe-other-functionality"></span>

This module regulates only [directory structure](#dir-structure) described above where only
from **controllers** and **views** their concrete positions are expected.  
When writing the rest of functionality you may follow the next advices:
* If a component is definitely related only to one part of application - **backend** or **frontend**
then put it in the corresponding subdirectory
* If there is no such definite separation put it in the root of his directory  

For example for models:
```
  models/
    backend/
      SomeBackendModel.php
    frontend/
      SomeFrontendModel.php	  
    SomeCommonModel.php  
```
* Since for all user interface of our module we have already created ```ui/``` subdirectory 
then put **forms** and **widgets** there

