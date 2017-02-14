<?php
namespace Staf;

use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

class Builder
{
    protected $sourceDir;

    protected $targetDir;

    protected $cacheDir;

    protected $filesystem;

    protected $factory;

    public function __construct(array $config)
    {
        $this->sourceDir  = array_get($config, 'source_path');
        $this->targetDir  = array_get($config, 'target_path');
        $this->cacheDir   = array_get($config, 'cache_path');
        $this->filesystem = new Filesystem();
        $this->factory    = $this->getFactory();
    }

    protected function getFactory()
    {
        $finder   = new FileViewFinder($this->filesystem, [$this->sourceDir]);
        $resolver = new EngineResolver();

        $resolver->register('blade', function () {
            return new CompilerEngine(new BladeCompiler($this->filesystem, $this->cacheDir));
        });

        return new Factory($resolver, $finder, new Dispatcher());
    }

    public function build(array $definition)
    {
        $this->filesystem->cleanDirectory($this->targetDir);
        $this->filesystem->cleanDirectory($this->cacheDir);

        foreach ($definition as $path => $item) {
            $content = '';
            $file    = array_get($item, 'name', 'index.html');

            if (isset($item['entry'])) {
                $view    = $this->factory->make($item['entry']);
                $content = $view->render();
            }

            $destination = $this->dest($path);

            if (!$this->filesystem->exists($destination)) {
                $this->filesystem->makeDirectory($destination);
            }

            $this->filesystem->put($destination . '/' . $file, $content);
        }
    }

    protected function dest($path)
    {
        return $this->targetDir . '/' . trim($path, '/');
    }

    protected function path($path)
    {
        return $this->sourceDir . '/' . trim($path, '/');
    }
}