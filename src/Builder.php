<?php
namespace Staf;

use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

/**
 * Class Builder
 *
 * @package Staf
 */
class Builder
{
    /**
     * @var string
     */
    protected $sourceDir;

    /**
     * @var string
     */
    protected $targetDir;

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * Builder constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->filesystem = new Filesystem();
        $this->sourceDir  = $this->verifyPath(array_get($config, 'source_path'));
        $this->targetDir  = $this->verifyPath(array_get($config, 'target_path'));
        $this->cacheDir   = $this->verifyPath(array_get($config, 'cache_path'));
        $this->factory    = $this->getFactory();
    }

    /**
     * @return Factory
     */
    protected function getFactory()
    {
        $finder   = new FileViewFinder($this->filesystem, [$this->sourceDir]);
        $resolver = new EngineResolver();

        $resolver->register('blade', function () {
            return new CompilerEngine(new BladeCompiler($this->filesystem, $this->cacheDir));
        });

        return new Factory($resolver, $finder, new Dispatcher());
    }

    /**
     * @param array $views
     * @param bool  $clean
     */
    public function build(array $views, $clean = false)
    {
        $this->filesystem->cleanDirectory($this->targetDir);

        if ($clean) {
            $this->filesystem->cleanDirectory($this->cacheDir);
        }

        foreach ($views as $path => $item) {
            $this->processItem($path, $item);
        }
    }

    /**
     * @param $path
     * @param $item
     */
    protected function processItem($path, $item)
    {
        // Allow shorter syntax by writing the view name directly instead of an array.
        if (is_string($item)) {
            $item = ['entry' => $item];
        }

        // Handle the view to render, if we should render
        $entry = array_get($item, 'entry', str_replace('/', '.', $path));
        if ($entry !== false) {

            // Get the data to use when rendering the view
            $data     = array_get($item, 'data', []);
            $fileName = array_get($item, 'name', 'index.html');

            // Render the view
            $content = $this->factory->make($entry, $data)->render();

            // Write the rendered file to disk.
            $this->filesystem->put($this->destination($path, $fileName), $content);
        }

        // Process eventual children
        foreach (array_get($item, 'children', []) as $childPath => $childItem) {
            $this->processItem($path . '/' . $childPath, $childItem);
        }

        // Copy files
        foreach (array_get($item, 'files', []) as $file) {
            $this->filesystem->copy(
                $this->sourceFile($path, $file),
                $this->destination($path, $file)
            );
        }

    }

    /**
     * @param $path
     * @return string
     * @throws \Exception
     */
    protected function verifyPath($path)
    {
        if (!$this->filesystem->exists($path)) {
            if (!$this->filesystem->makeDirectory($path)) {
                throw new \Exception("Path '$path' does not exist and cannot be created.'");
            }
        }

        return realpath($path);
    }

    /**
     * @param $path
     * @param $file
     * @return string
     */
    protected function sourceFile($path, $file)
    {
        return $this->sourceDir . '/' . $path . '/' . $file;
    }

    /**
     * @param $path
     * @param $fileName
     * @return string
     */
    protected function destination($path, $fileName)
    {
        return $this->verifyPath($this->targetDir . '/' . trim($path, '/')) . '/' . $fileName;
    }
}