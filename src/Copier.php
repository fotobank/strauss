<?php
/**
 * Prepares the destination by deleting any files about to be copied.
 * Copies the files.
 *
 * TODO: Exclude files list.
 *
 * MIT states: "The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software."
 *
 * GPL states: "You must cause the modified files to carry prominent notices stating
 * that you changed the files and the date of any change."
 *
 *
 * @author CoenJacobs
 * @author BrianHenryIE
 * @author Alex Jurii
 *
 * @license MIT
 */

namespace AlexLabs\Strauss;

use AlexLabs\Strauss\Composer\ComposerPackage;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class Copier
{
    /**
     * The only path variable with a leading slash.
     * All directories in project end with a slash.
     *
     * @var string
     */
    protected string $workingDir;

    protected string $absoluteTargetDir;

    /** @var string */
    protected string $vendorDir;

    /** @var array<string,array{dependency:ComposerPackage,sourceAbsoluteFilepath:string,targetRelativeFilepath:string}> */
    protected array $files;

    /** @var Filesystem */
    protected Filesystem $filesystem;

    /**
     * Copier constructor.
     *
     * @param array<string,array{dependency:ComposerPackage,sourceAbsoluteFilepath:string,targetRelativeFilepath:string}> $files
     * @param string $workingDir
     * @param string $relativeTargetDir
     * @param string $vendorDir
     */
    public function __construct(array $files, string $workingDir, string $relativeTargetDir, string $vendorDir)
    {
        $this->files = $files;

        $this->workingDir = $workingDir;

        $this->absoluteTargetDir = $workingDir . $relativeTargetDir;

        $this->vendorDir = $vendorDir;

        $this->filesystem = new Filesystem(new Local($workingDir));
    }

    /**
     * If the target dir does not exist, create it.
     * If it already exists, delete any files we're about to copy.
     *
     * @return void
     */
    public function prepareTarget(): void
    {
        $TargetDir = basename($this->absoluteTargetDir);
        if (! $this->filesystem->has($TargetDir)) {
            $this->filesystem->createDir($TargetDir);
        } else {
            $this->filesystem->getAdapter()->setPathPrefix('');
            foreach (array_keys($this->files) as $targetRelativeFilepath) {
                $targetAbsoluteFilepath = $this->absoluteTargetDir . $targetRelativeFilepath;

                if ($this->filesystem->has($targetAbsoluteFilepath)) {
                    $this->filesystem->delete($targetAbsoluteFilepath);
                }
            }
        }
    }


    /**
     *
     */
    public function copy(): void
    {

        $this->filesystem->getAdapter()->setPathPrefix('');

        foreach ($this->files as $targetRelativeFilepath => $fileArray) {
            $sourceAbsoluteFilepath = $fileArray['sourceAbsoluteFilepath'];

            $targetAbsolutePath = $this->absoluteTargetDir . $targetRelativeFilepath;

            $this->filesystem->copy($sourceAbsoluteFilepath, $targetAbsolutePath);
        }
    }
}
