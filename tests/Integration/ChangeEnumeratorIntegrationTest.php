<?php

namespace AlexLabs\Strauss\Tests\Integration;

use AlexLabs\Strauss\ChangeEnumerator;
use AlexLabs\Strauss\Composer\ComposerPackage;
use AlexLabs\Strauss\Composer\Extra\StraussConfig;
use AlexLabs\Strauss\Composer\ProjectComposerPackage;
use AlexLabs\Strauss\Copier;
use AlexLabs\Strauss\FileEnumerator;
use AlexLabs\Strauss\Tests\Integration\Util\IntegrationTestCase;

/**
 * Class CopierTest
 * @package AlexLabs\Strauss
 * @coversNothing
 */
class ChangeEnumeratorIntegrationTest extends IntegrationTestCase
{

    /**
     * Given a list of files, find all the global classes and the namespaces.
     */
    public function testOne()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "AlexLabs/strauss",
  "require": {
    "google/apiclient": "*"
  },
  "extra": {
    "strauss": {
      "namespace_prefix": "AlexLabs\\Strauss\\",
      "classmap_prefix": "BrianHenryIE_Strauss_",
      "delete_vendor_files": false
    }
  }
}
EOD;

        file_put_contents($this->testsWorkingDir . 'composer.json', $composerJsonString);

        chdir($this->testsWorkingDir);

        exec('composer install');

        $projectComposerPackage = new ProjectComposerPackage($this->testsWorkingDir);

        $dependencies = array_map(function ($element) {
            $dir = $this->testsWorkingDir . 'vendor'. DIRECTORY_SEPARATOR . $element;
            return ComposerPackage::fromFile($dir);
        }, $projectComposerPackage->getRequiresNames());

        $workingDir = $this->testsWorkingDir;
        $relativeTargetDir = 'vendor-prefixed' . DIRECTORY_SEPARATOR;
        $vendorDir = 'vendor' . DIRECTORY_SEPARATOR;

        $config = $this->createStub(StraussConfig::class);
        $config->method('getVendorDirectory')->willReturn($vendorDir);

        $fileEnumerator = new FileEnumerator($dependencies, $workingDir, $config);

        $fileEnumerator->compileFileList();

        $copier = new Copier($fileEnumerator->getAllFilesAndDependencyList(), $workingDir, $relativeTargetDir, $vendorDir);

        $copier->prepareTarget();

        $copier->copy();

        $config = $this->createStub(StraussConfig::class);

        $config->method('getExcludePackagesFromPrefixing')->willReturn(array());
        $config->method('getExcludeNamespacesFromPrefixing')->willReturn(array());

        $changeEnumerator = new ChangeEnumerator($config);

        $phpFileList = $fileEnumerator->getPhpFilesAndDependencyList();

        $changeEnumerator->findInFiles($workingDir . $relativeTargetDir, $phpFileList);


        $classes = $changeEnumerator->getDiscoveredClasses();

        $namespaces = $changeEnumerator->getDiscoveredNamespaceReplacements();

        $this->assertNotEmpty($classes);
        $this->assertNotEmpty($namespaces);

        $this->assertContains('Google_Task_Composer', $classes);
    }
}
