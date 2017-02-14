<?php
namespace Staf\Generator;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:build {definition=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Builds the static site';

    /**
     * The static site builder instance
     *
     * @var Builder
     */
    protected $builder;

    /**
     * Set the instance of the builder upon instantiation.
     *
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     *
     */
    public function handle()
    {
        $definitionName = $this->argument('definition');

        $definition = array_get(config('generator.definitions'), $definitionName);

        if ($definition === null) {
            $this->error('Invalid site definition provided.');

            return;
        }

        $this->builder->build($definition);

        $this->info("Static site '$definitionName' built!");
    }
}
