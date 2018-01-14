# Standalone Laravel Blade template engine

This project provide Blade as a standalone library that works with 5.x Laravel Blade.
See documentation on [https://laravel.com/docs/5.5/blade](https://laravel.com/docs/5.5/blade)

## Usage

```php
use Mejta\StandaloneBlade;

$viewDirs = [
    __DIR__ . '/views',
];

$cacheDir = __DIR__ . '/cache';

$engine = new StandaloneBlade($viewDirs, $cacheDir);

$engine->compiler->directive('datetime', function($expression) {
    return "<?php echo ($expression)->format('m/d/Y H:i'); ?>";
});

$engine->compiler->if('env', function($environment) {
    return app()->environment($environment);
});

echo $engine->render('page-template', [
    'title' => 'Page title',
    'content' => 'Some example page content',
]);

```
