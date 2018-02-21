# Standalone Laravel Blade template engine

This project provide Blade as a standalone library that works with 5.6 Laravel Blade.
See blade documentation: [https://laravel.com/docs/5.5/blade](https://laravel.com/docs/5.5/blade)

## Compatible with

- PHP >= 7.1
- Laravel Blade 5.6.x

## Instalation

```bash
composer require mejta/standalone-blade
```

## Usage

```php
use Mejta\StandaloneBlade;

$viewDirs = [
    __DIR__ . '/views',
];

$cacheDir = __DIR__ . '/cache';

$engine = new StandaloneBlade($viewDirs, $cacheDir);

// Define custom directives
$engine->directive('datetime', function($expression) {
    return "<?php echo ($expression)->format('m/d/Y H:i'); ?>";
});

// Define custom if statements
$engine->if('env', function($environment) {
    return app()->environment($environment);
});

// Share variables with all templates
$engine->share('key', 'value');

// Render template
echo $engine->render('page-template', [
    'title' => 'Page title',
    'content' => 'Some example page content',
]);

```
