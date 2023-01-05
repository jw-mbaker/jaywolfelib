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

### Writing a Controller

We have passed `\Plugin\Controllers\Controller` as a controller in `routes.php`.

Any controller that is part of a route (added in `routes.php`) **MUST** extend `\JayWolfeLib\Controllers\Controller` or implement the `\JayWolfeLib\Controllers\ControllerInterface` interface.

Every controller that implements `\JayWolfeLib\Controllers\ControllerInterface` **MUST** have the `init` method.

The `init` method is where callbacks for actions and filters are registered. Most of your add_action/add_filter will go into this method.

The `init` method is called automatically when the controller is loaded.

> NOTE: If you create a constructor inside a controller extending `\JayWolfeLib\Controllers\Controller`, you need to make sure to call the parent class's constructor to pass the `View` object into the instance.

<details>
    <summary><b>SHOW CONTROLLER EXAMPLE</b></summary>
    Here is an example of how a controller would look

```php
<?php
// file: PLUGIN_PATH/src/Plugin/Controllers/Controller.php

namespace Plugin\Controllers;

use Plugin\Models\Model;
use Plugin\Models\Model2;
use JayWolfeLib\Views\ViewInterface;
use JayWolfeLib\Hooks\{Hooks, Ajax, MenuPage};
use JayWolfeLib\Input;

class Controller extends \JayWolfeLib\Controllers\Controller
{
    /** @var Model */
    private $model;
    
    /** @var Model2 */
    private $model2;

    public function __construct(ViewInterface $view, Model $model, Model2 $model2)
    {
        parent::__construct($view);
        
        $this->model = $model;
        $this->model2 = $model2;
    }
    
    public function init(): void
    {
        Hooks::add_action('admin_enqueue_scripts', [$this, 'load_scripts']);
        Hooks::add_action('admin_menu', [$this, 'load_menu']);
        
        Ajax::add_ajax('save_plugin_data', [$this, 'save_data']);
    }
    
    /**
    * Load scripts.
    *
    * @param string $hook
    * @return void
    */
    public function load_scripts(string $hook): void
    {
        
    }
    
    /**
    * Load the admin menu.
    *
    * @return void
    */
    public function load_menu(): void
    {
        MenuPage::add_menu_page(
            'Example',
            'Example',
            'administrator',
            'example-plugin',
            [$this, 'main_page']
        );
    }
    
    /**
    * The main page.
    *
    * @param Input $input
    * @return void
    */
    public function main_page(Input $input): void
    {
        $this->view->main_page();
    }
    
    /**
    * Save data.
    *
    * wp_ajax_save_plugin_data
    *
    * @param Input $input
    * @return void
    */
    public function save_data(Input $input): void
    {
        $data = [];
        $id = $this->model->save($data, $input->post('id');
        
        $input->send_json(['id' => $id]);
        wp_die();
    }
}
```
</details>

### Writing a Model

All models should extend `\JayWolfeLib\Models\Model` or implement the `\JayWolfeLib\Models\ModelInterface` interface.

<details>
    <summary><b>SHOW MODEL EXAMPLE</b></summary>
    Here is an example of how a model would look
    
```php
<?php
// file: PLUGIN_PATH/src/Plugin/Models/Model.php

namespace Plugin\Models;

use JayWolfeLib\Factory\ModelfactoryInterface;

class Model extends \JayWolfeLib\Models\Model
{
    public function __construct(\WPDB $wpdb, ModelFactoryInterface $factory)
    {
        parent::__construct($wpdb, $factory, 'our_table');
    }
    
    /**
    * Save data.
    *
    * @param array $data
    * @param int|null $id
    * @return int|null
    */
    public function save(array $data, ?int $id = null): ?int
    {
        return $this->saveData($data, $id);
    }
}
```
</details>

### Writing a View

You do not have to create a separate `View` class. `\JayWolfeLib\Views\View` is passed to each controller by default.

However, writing views allows us to abstract the template path so the controller is not tied to the template file directly.

<details>
    <summary><b>SHOW VIEW EXAMPLE</b></summary>
    Here is an example of how a view would look

```php
<?php
// file: PLUGIN_PATH/src/Plugin/Views/View.php

namespace Plugin\Views;

class View extends \JayWolfeLib\Views\View
{
    public function main_page(array $data = []): void
    {
        $this->render('main-page', $data);
    }
}
```
</details>

### Writing a Template

Templates are the actual files which generate html for the `View`.

A template file can be called by invoking the `render` method on any `View` class's object.

The template path is set in the `paths -> templates` setting in `config.php`.

<details>
    <summary><b>SHOW TEMPLATE EXAMPLE</b></summary>
    Here is an example of how a template would look

```php
// file: PLUGIN_PATH/src/Plugin/templates/main-page.php

<?php echo 'Hello world!'?>
```
</details>

## The Service Container

`JayWolfeLib` stores reusable objects in the global container which makes it easier to inject dependencies into your controllers and callbacks. The container can be accessed using the `JayWolfeLib\container` function.

You can pass your own values / objects into the container using the `set` method.
```php
container()->set('example', fn(Container $c) => new Example($c->get('guzzle')));
```

The instance can be retrieved with the `get` method.
```php
$example = container()->get('example');
```