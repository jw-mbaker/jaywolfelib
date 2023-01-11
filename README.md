# JayWolfeLib Libary for Jay Wolfe Plugins

This library is useful to help WordPress plugins follow the MVC design pattern.

## Installation

First, make sure you have [composer](https://getcomposer.org) installed.

Then, add JayWolfeLib as a dependency:
```
composer require jw-mbaker/jaywolfelib
```

Make sure your plugin is loading its autoloader:
```php
require_once 'vendor/autoload.php';
```

## How to use

Create a `config.php` file in your main plugin folder. This file will be used to bootstrap the library.

### The Configuration File

The `config.php` file should return an array of configuration settings unique to your plugin:
```php
// config.php

return [
    'plugin_file' => __DIR__ . '/index.php',

    'version' => get_plugin_data(__DIR__ . '/index.php')['Version'],

    'author' => 'Craig Misak',

    'paths' => [
        'templates' => __DIR__ . '/src/Plugin/templates',
        'arrays' => __DIR__ . '/src/Plugin/arrays'
    ],

    'dependencies' => [
        'plugins' => [
            'plugin/slug1',
            'plugin/slug2'
        ],

        'min_php_version' => '7.4',

        'min_wp_version' => '5.0'
    ]
];
```

And then point to the config file when you bootstrap the library:
```php
use JayWolfeLib\JayWolfeLib;

JayWolfeLib::load(__DIR__ . '/config.php');
```

### Writing Controllers

A controller can be any function or method that takes information from a hook and returns a response.

```php
namespace Plugin\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PluginController
{
    public function index(Request $request): Response
    {
        // ...

        return new Response('Hello world!');
    }
}
```

### The Base Controller Class

This library provides a base controller class called `AbstractController`. It can be extended to gain access to helper methods.

```php
use JayWolfeLib\Controllers\AbstractController;

class PluginController extends AbstractController
{
    // ...
}
```

If you do extend the `AbstractController` class, you will have to inject your configuration into the controller.

You can do this by using [PHP DI](https://php-di.org)'s `@Inject` API.

```php
use JayWolfeLib\Controllers\AbstractController;
use JayWolfeLib\Config\ConfigInterface;

class PluginController extends AbstractController
{
    /**
     * @Inject("plugin/index.php") 
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
    }
}
```

Your configuration will be automatically injected into the method when the instance is created.

### Rendering Templates

The `render()` method renders a template and puts that content into a `Response` object for you.

```php
return $this->render('template', ['foo' => 'bar']);
```

### Writing Actions and Filters

You can hook into the `jwlib_hooks` action to add actions and filters.

```php
use JayWolfeLib\WordPress\Filter\FilterCollection;
use JayWolfeLib\WordPress\Filter\Action;
use JayWolfeLib\WordPress\Filter\Filter;
use JayWolfeLib\WordPress\Filter\Api;
use Plugin\Controllers\PluginController;
use Symfony\Component\HttpFoundation\Request;

add_action('jwlib_hooks', function(FilterCollection $collection) {
    $action = Action::create([
        Action::HOOK => 'admin_enqueue_scripts',
        Action::CALLABLE => [PluginController::class, 'loadScripts']
    ]);

    $filter = Filter::create([
        Filter::HOOK => 'plugin_action_links',
        Filter::CALLABLE => [PluginController::class, 'actionLinks']
    ]);

    $ajax = Action::create([
        Action::HOOK => 'wp_ajax_do_something',
        Action::CALLABLE => function(Request $request) {
            // ...
        }
    ]);

    $api = Api::create([
        Api::HOOK => 'some_api_hook',
        Api::CALLABLE => [PluginController::class, 'handleApi'],
        Api::METHOD => 'GET',
        Api::API_KEY => 'some_key'
    ]);

    $collection->addAction($action);
    $collection->addFilter($filter);
    $collection->addAction($ajax);
    $collection->addAction($api);
});
```

You do not need to pass an instance of a controller. The service container will resolve the class and autowire the instance.

### Fetching services

You are able to inject services in addition to the default arguments that are passed into the controller from an action/filter hook.

If you need a service in a controller, type-hint an argument with its class (or interface) name.

```php
use Psr\Log\LoggerInterface;

// ...

public function loadScripts(LoggerInterface $logger, string $hook)
{
    $logger->info('foo');
    // ...
}
```

And then when make sure you map it to your action hook using `\DI\get()`:

```php
use Plugin\Controllers\PluginController;
use Psr\Log\LoggerInterface;
use JayWolfeLib\WordPress\Filter\FilterCollection;
use JayWolfeLib\WordPress\Filter\Action;

add_action('jwlib_hooks', function(FilterCollection $collection) {
    $action = Action::create([
        Action::HOOK => 'admin_enqueue_scripts',
        Action::CALLABLE => [PluginController::class, 'loadScripts'],
        Action::MAP => [\DI\get(LoggerInterface::class)]
    ]);
});
```

The service container will automatically inject the service into the controller.

### Writing Admin Menu Pages

You can hook into the `jwlib_admin_menu` hook to add admin menu pages.

```php
use Plugin\Controllers\PluginController;
use JayWolfeLib\WordPress\AdminMenu\MenuCollection;
use JayWolfeLib\WordPress\AdminMenu\MenuPage;
use JayWolfeLib\WordPress\AdminMenu\SubMenuPage;

add_action('jwlib_admin_menu', function(MenuCollection $collection) {
    $mp = MenuPage::create([
        MenuPage::SLUG => 'my-plugin',
        MenuPage::CALLABLE => [PluginController::class, 'index'],
        MenuPage::PAGE_TITLE => 'Home',
        MenuPage::MENU_TITLE => 'Home',
        MenuPage::CAPABILITY => 'administrator',
    ]);

    $smp = SubMenuPage::create([
        SubMenuPage::SLUG => 'some-other-page',
        SubMenuPage::PARENT_SLUG => 'my-plugin',
        SubMenuPage::CALLABLE => [PluginController::class, 'someOtherPage']
    ]);

    $collection->menuPage($mp);
    $collection->subMenuPage($smp);
});
```