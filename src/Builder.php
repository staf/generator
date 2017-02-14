<?php
namespace Staf\Generator;

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
     * The absolute path of the directory containing the source files.
     *
     * @var string
     */
    protected $sourceDir;

    /**
     * The absolute path of the target directory to put the compiled files in.
     *
     * @var string
     */
    protected $targetDir;

    /**
     * The absolute path to the cache directory used by the Blade compiler.
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * The filesystem helper
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * The view factory used to render the blade templates.
     *
     * @var Factory
     */
    protected $factory;

    /**
     * Create the filesystem instance, verify the various directory paths and setup the view factory.
     * TODO: This should probably change somewhat to be able to set these by using the Laravel service container.
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
     * Instantiate the view factory.
     *
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
     * Build the static site based on a definition array.
     * TODO: Is it worth trying to make some definition class to build this array?
     *
     * @param array $site
     * @param bool  $clean
     */
    public function build($site, $clean = false)
    {
        $this->filesystem->cleanDirectory($this->targetDir);

        if ($clean) {
            $this->filesystem->cleanDirectory($this->cacheDir);
        }

        foreach ($site as $path => $item) {
            $this->processItem($path, $item);
        }
    }

    /**
     * Handle an item in the definition array.
     *
     * @param string       $path
     * @param array|string $item
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

            if (is_array($file)) {
                // If there is a specific source path defined for the file we use that
                $this->filesystem->copy(
                    $file['source'],
                    $this->destination($path, $file['name'])
                );

            } else {
                // Else we assume the file is located in the current path
                $this->filesystem->copy(
                    $this->sourceFile($path, $file),
                    $this->destination($path, $file)
                );
            }
        }

    }

    /**
     * Make sure a path exists and try to create it if it doesn't
     *
     * @param  string $path
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
     * Build a path to a file in the source directory
     *
     * @param  string $path
     * @param  string $file
     * @return string
     */
    protected function sourceFile($path, $file)
    {
        return $this->sourceDir . '/' . $path . '/' . $file;
    }

    /**
     * Build the path for a file to put in the build/destination directory.
     *
     * @param  string $path
     * @param  string $fileName
     * @return string
     */
    protected function destination($path, $fileName)
    {
        return $this->verifyPath($this->targetDir . '/' . trim($path, '/')) . '/' . $fileName;
    }
}
