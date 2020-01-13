# Универсальный модуль под Yii2

[English version](../README.md)

## Содержание

* [Цель](#goal)
* [Установка](#installing)
* [Суть идеи](#gist)
* [Использование](#using)
* [Настройка модуля](#settings)
* [Пример подключения на *Basic* шаблоне](#example-basic)
* [Рецепты](#recipe)


---

## Цель <span id="goal"></span>

Данное расширение предоставляет структуру модуля, который:

1. Будет полностью самодостаточным и переносимым, т.к. в одной папке содержит весь свой функционал
2. Логически разделен на **backend** и **frontend** части
3. Легко подключается как на *Advanced* так и на *Basic* шаблоны приложений
4. На *Basic* шаблоне приложения:

    * Подключается в секцию модулей один раз
    * Имеется возможность защиты всех **backend** контроллеров  (для админок) 
	
---
    
## Установка <span id="installing"></span>

Установка через composer:

Выполните
```
composer require --prefer-dist mgrechanik/yii2-universal-module-sceleton
```

или добавьте
```
"mgrechanik/yii2-universal-module-sceleton" : "~1.0.0"
```
в  `require` секцию вашего `composer.json` файла.

---

## Суть идеи  <span id="gist"></span>    

* По умолчанию в Yii2 модулях контроллеры ищутся автоматически в ```$controllerNamespace```
* Мы намеренно не используем эту функцию, а задаем все контроллеры через ```$controllerMap``` модуля
* Но делаем это не через ```$controllerMap``` свойство напрямую, а отдельно указываем какие контроллеры для **backend**, а какие для **frontend**
* У модуля есть **режим работы** <span id="mode"></span>, выставляемый в конфиге, в соответствии с которым в карту контроллеров
попадут **только соответствующие** данному режиму контроллеры:
    * В **frontend** приложении *Advanced* шаблона, мы подключаем модуль в режиме ```'frontend'```
    * В **backend** приложении *Advanced* шаблона, мы подключаем модуль в режиме ```'backend'```    
    * В *Basic* шаблоне, мы можем подключить модуль как в вышеописанных режимах так и в режиме ```'backend and frontend'```, т.е. когда оба типа контроллеров доступны
* Модуль, получая запрос, создает контроллер из их карты, при этом по его пространству имен зная <span id="mknows"></span>  
**backend** или **frontend** это контроллер, для дополнительной настройки
* Модуль ожидает следующую структуру папок: <span id="dir-structure"></span>
```
Папка_с_модулем/
   ui/                                  // User Interface данного модуля
      controllers/
          backend/                      // Backend контроллеры, например следующий:
            AdminDefaultController.php  
            ...
          frontend/                     // Frontend контроллеры, например следующий: 
            DefaultController.php       
            ...
      views/                            // Views для соответствующих контроллеров  
          backend/
            admin-default/
          frontend/        
            default/
   Module.php                           // класс модуля
```   
---

## Использование  <span id="using"></span>    

1) Сгенерируйте, или создайте сами, класс своего модуля

2) Отнаследуйтесь вашим модулем от универсального модуля
```php
use mgrechanik\yiiuniversalmodule\UniversalModule;

class YourModule extends UniversalModule
{
```
3) Теперь создадим (или сгенерируем) **frontend** контроллер  <span id="fcontroller"></span> 
* Учитываем что его ```namespace``` должен быть ```yourModuleNamespace\ui\controllers\frontend```
* Создайте предварительно все упоминаемые подпапки 
* Соответственно контроллеру views складываются в ```@yourModuleNamespace/ui/views/frontend/YourControllerName/```
* Мы должны указать этот контроллер в карте **frontend** контроллеров модуля:
```php
class YourModule extends UniversalModule
{
    public $frontendControllers = [
        'default',
    ];
```
, где ```'default'``` обозначает соответственно ```yourModuleNamespace\ui\controllers\frontend\DefaultController```.  
Если ваши имена контроллера и класса не совпадают, задайте в форме ```'default2' => 'SomeDefaultController'```.

> **Всегда, создавая свои контроллеры, не забывайте прописать их в соответствующую карту контроллеров модуля.**

Классы контроллеров не требуется наследовать от какого то общего типа.

4) Теперь создадим (или сгенерируем) **backend** контроллер <span id="bcontroller"></span>
* Логика по аналогии с пунктом 3), но его ```namespace``` должен быть ```yourModuleNamespace\ui\controllers\backend```
* Указывать в модуле в:
```php
class YourModule extends UniversalModule
{
    public $backendControllers = [
        'admin-default',
    ];
```
* Именовать **backend** контроллеры удобно с помощью префикса **Admin**, чтобы всем таким адресам потом единообразно 
[настроить урлы](#recipe-admin-url) на начинающиеся не с имени модуля, а с **admin/**

5) Все, ваш модуль готов, можно **подключить его в приложение**: <span id="setup"></span>

**config/main.php:**
```php
    // ...
    'modules' => [
        'yourModule' => [
            'class' => 'yourModuleNamespace\YourModule',
            'mode' => 'frontend',
        ],
```
, не забывая выставить его mode - [режим](#mode)

> Все такие модули удобно подключать на первом уровне модулей, без всяких вложенных структур, а как список модулей, который
> мы привыкли видеть в админках популярных **CMS**-ок, что к тому же дает короткие урлы.

---

## Настройка модуля <span id="settings"></span>

[Подключяя](#setup) модуль в приложение мы можем воспользоваться следующими его свойствами:

#### ```$mode``` - режим работы модуля
Обязательное свойство для установки. [Подробнее](#mode)

#### ```$backendLayout``` - layout для backend контроллеров
Указывается ```layout```, устанавливаемый модулю, когда обращение идет к **backend** контроллеру.  
Полезно для *Basic* шаблона приложения.

#### ```$frontendControllers``` - карта frontend контроллеров
[Подробнее](#fcontroller)

#### ```$backendControllers``` - карта backend контроллеров
[Подробнее](#bcontroller)

#### ```$controllerMapAdjustCallback``` - callback для финальной настройки карты контроллеров

После того как карта контроллеров модуля сгенерирована, ее можно тонко настроить этой функцией, 
ее сигнатура: ```function($map) { ...; return $map; }```

#### ```$backendControllerConfig``` - настройки **backend** контроллеров
Когда модуль [создает](#mknows) **backend** контроллер он перед работой может настроить его данными свойствами.

Удобно, например, чтобы через поведение закрыть доступ к таким контроллерам через фильтры доступа.

[Пример использования](#example-basic). 

#### ```$frontendControllerConfig``` - настройки **frontend** контроллеров
По аналогии с ```$backendControllerConfig```

---

## Пример подключения на *Basic* шаблоне <span id="example-basic"></span>

Предположим что у нас имеется два таких наших модуля - ```example``` и ```omega```.  
Для их подключения вот пример рабочих конфигов:

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
В данном примере мы разрешили доступ "в админку" только одному указанному пользователю ```(id==1)```, плюс ограничиваем по ```ip```.


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

## Рецепты <span id="recipe"></span>

#### Делаем чтобы все админские адреса начинались с ```/admin```  <span id="recipe-admin-url"></span>
Рассмотрим *Basic* приложение, к которому мы подключили два наших модуля:
```php
    'modules' => [
        'example' => [
            ...
        ],
        'omega' => [
            ...
        ],  
```
Если мы следовали [совету](#bcontroller) насчет админских контроллеров, то все они именуются по шаблону ```Admin...Controller```.  
Соответственно адреса к ним будут вида ```example/admin-default``` и ```omega/admin-default```.  
Мы же хотим чтобы все админские адреса начинались с ```admin/```.

Это легко достигается следующими двумя правилами вашего ```urlManager```:
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

#### Генерация функционала **backend**-а с помощью Gii CRUD generator   <span id="recipe-crud"></span>

Вы легко можете сгенерировать функционал CRUD-а как обычно, учитывая:
* Имя контроллера и его пространство имен выбирайте соответственно [документации](#bcontroller)
* ```View Path``` указывайте соответственно [структуре папок](#dir-structure) требуемой модулем

#### Как подключать модуль в консольное приложение?   <span id="recipe-other-console"></span>

Если в вашем модуле предусмотрены консольные команды, которые располагаются например тут:
```
Папка_с_модулем/
  console/
    commands/                // Папка для консольных комманд
      HelloController.php
  Module.php
```	  
, то в конфиге консольного приложения данный модуль подключается:

```php
    'modules' => [
        'example' => [
            'class' => 'modules\example\Module',
            'controllerNamespace' => 'yourModuleNamespace\console\commands',
        ],
    ],
```

#### Куда располагать остальной функционал модуля?   <span id="recipe-other-functionality"></span>

Данный модуль регламентирует только указанную выше [структуру папок](#dir-structure), где только от контроллеров
и их виевсов ожидается конкретное расположение.  
По остальному функционалу вы можете придерживаться следующих правил:
* Если компонент четко относится только к одному типу - **backend** или **frontend**, то и помещайте его 
в соответствующую подпапку
* Если же такого разделения нет, то располагайте его в корне его папки.  
Например для моделей
```
  models/
    backend/
      SomeBackendModel.php
    frontend/
      SomeFrontendModel.php	  
    SomeCommonModel.php  
```
* Т.к. для пользовательского интерфейса мы в модуле уже создали подпапку ```ui/```, то **формы** и **виджеты**
следует располагать там