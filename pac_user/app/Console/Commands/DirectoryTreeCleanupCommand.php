<?php

namespace App\Console\Commands;

use File;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class DirectoryTreeCleanupCommand extends Command
{
    protected $signature = 'clean:directoryTrees';

    protected $description = 'Clean up directory trees.';

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $filesystem;
    
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }
    
    public function handle()
    {
        $this->comment('Cleaning directory trees...');

        $directories = collect(config('laravel-directory-cleanup.directories'));

        collect($directories)->each(function ($config, $directory) {
            if (File::isDirectory($directory)) {
                $this->deleteEmptySubdirectories($directory);
            }
        });

        $this->comment('All done!');
    }

    protected function deleteEmptySubdirectories(string $directory)
    {
        for($depth = 4; $depth >= 0; $depth--){
            $deletedSubdirectories = collect($this->directories($directory, $depth))
                ->filter(function ($directory) {
                    return ! $this->filesystem->allFiles($directory, true);
                })
                ->each(function ($directory) {
                    $this->filesystem->deleteDirectory($directory);
                });
            $this->info("Deleted {$deletedSubdirectories->count()} directory(ies) from {$directory}.");
        }
    }
    
    /**
     * Get all of the directories within a given directory.
     *
     * @param  string  $directory
     * @return array
     */
    private function directories($directory, $depth)
    {
        $directories = [];

        foreach (Finder::create()->in($directory)->directories()->depth($depth)->sortByName() as $dir) {
            $directories[] = $dir->getPathname();
        }

        return $directories;
    }
}
