<?php
/**
 * Deletes source files and empty directories.
 *
 * MIT states: "The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software."
 *
 * GPL states: "You must cause the modified files to carry prominent notices stating
 * that you changed the files and the date of any change."
 *
 *
 * @author BrianHenryIE
 * @author Alex Jurii
 *
 * @license MIT
 */

namespace AlexLabs\Strauss;

use AlexLabs\Strauss\Composer\Extra\StraussConfig;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use RecursiveDirectoryIterator;
use Symfony\Component\Finder\Finder;

class Cleanup
{

    /** @var Filesystem */
    protected Filesystem $filesystem;

    protected bool $isDeleteVendorFiles;
    protected bool $isDeleteVendorPackages;

    protected string $vendorDirectory = 'vendor'. DIRECTORY_SEPARATOR;
    
    public function __construct(StraussConfig $config, string $workingDir)
    {
        $this->vendorDirectory = $config->getVendorDirectory();

        $this->isDeleteVendorFiles = $config->isDeleteVendorFiles() && $config->getTargetDirectory() !== $config->getVendorDirectory();
        $this->isDeleteVendorPackages = $config->isDeleteVendorPackages() && $config->getTargetDirectory() !== $config->getVendorDirectory();

        $this->filesystem = new Filesystem(new Local($workingDir));
    }

    /**
     * Maybe delete the source files that were copied (depending on config),
     * then delete empty directories.
     *
     * @param array $sourceFiles
     * @throws FileNotFoundException
     */
    public function cleanup(array $sourceFiles)
    {
        if (!$this->isDeleteVendorPackages && !$this->isDeleteVendorFiles) {
            return;
        }

        if ($this->isDeleteVendorPackages) {
            $package_dirs = array_unique(array_map(static function (string $relativeFilePath): string {
                list( $vendor, $package ) = explode(DIRECTORY_SEPARATOR, $relativeFilePath);
                return "{$vendor}/{$package}";
            }, $sourceFiles));

            foreach ($package_dirs as $package_dir) {
                $relativeDirectoryPath = $this->vendorDirectory . $package_dir;

                $this->filesystem->deleteDir($relativeDirectoryPath);
            }
        } elseif ($this->isDeleteVendorFiles) {
            foreach ($sourceFiles as $sourceFile) {
                $relativeFilepath = $this->vendorDirectory . $sourceFile;

                $this->filesystem->delete($relativeFilepath);
            }
        }

        // Get the root folders of the moved files.
        $rootSourceDirectories = [];
        foreach ($sourceFiles as $sourceFile) {
            $arr = explode(DIRECTORY_SEPARATOR, $sourceFile, 2);
            $dir = $arr[0];
            $rootSourceDirectories[ $dir ] = $dir;
        }
        $rootSourceDirectories = array_keys($rootSourceDirectories);


        $finder = new Finder();

        foreach ($rootSourceDirectories as $rootSourceDirectory) {
            if (!is_dir($rootSourceDirectory) || is_link($rootSourceDirectory)) {
                continue;
            }

            $finder->directories()->path($rootSourceDirectory);

            foreach ($finder as $directory) {
                $metadata = $this->filesystem->getMetadata($directory);

                if ($this->dirIsEmpty($directory)) {
                    $this->filesystem->deleteDir($directory);
                }
            }
        }
    }

    // TODO: Use Symphony or Flysystem functions.
    protected function dirIsEmpty(string $dir): bool
    {
        $di = new RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
        return iterator_count($di) === 0;
    }
}
