<?php

namespace Mejta;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

class StandaloneBlade {

    private $cache;
    private $views;

    private $filesystem;
    private $compiler;
    private $resolver;
    private $finder;
    private $dispatcher;
    private $factory;

    public static $checks = [];

    private $shares = [];

    public function __construct(array $views, string $cache) {

        if(!file_exists($cache))
            throw new StandaloneBladeException("Cache directory '$cache' doesn't exists");
        
        foreach($views as $view) {
            if(!file_exists($view))
                throw new StandaloneBladeException("View directory '$view' doesn't exists");
        }

        $this->cache = $cache;
        $this->views = $views;
        
        $this->boot();
    }

    private function boot() {
        $this->filesystem = new Filesystem;

        $this->compiler = new BladeCompiler($this->filesystem, $this->cache);
    
        $this->resolver = new EngineResolver;
    
        $this->resolver->register('blade', function () {
            return new CompilerEngine($this->compiler, $this->filesystem);
        });
    
        $this->resolver->register('php', function () {
            return new PhpEngine;
        });
    
        $this->finder = new FileViewFinder($this->filesystem, $this->views);
    
        $this->dispatcher = new Dispatcher(new Container);
    
        $this->factory = new Factory($this->resolver, $this->finder, $this->dispatcher);
    }

    public function directive($directive, $callback) {
        $this->compiler->directive($directive, $callback);
    }

    public function if($if, $check) {
        self::$checks[$if] = $check;

        $this->compiler->directive($if, function($expression) use ($if, $check) {
            return "<?php if(\\" . self::class . "::check('$if', $expression)): ?>";
        });
        
        $this->compiler->directive("else$if", function($expression) use ($if, $check) {
            return "<?php elseif(\\" . self::class . "::check('$if', $expression)): ?>";
        });
        
        $this->compiler->directive("end$if", function() {
            return "<?php endif; ?>";
        });
    }

    public static function check($if, $value) {
        return self::$checks[$if]($value);
    }
    
    public function share($key, $value) {
        $this->shares[$key] = $value;
    }

    public function render(string $template, array $data = []) {
        return $this
            ->factory
            ->make($template, array_merge_recursive($this->shares, $data))
            ->render();
    }
}
