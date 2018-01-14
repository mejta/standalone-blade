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

    public function __get($name) {
        if($name === 'compiler')
            return $this->compiler;

        return null;
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

    public function render($template, $data) {
        return $this->factory->make($template, $data)->render();
    }
}
