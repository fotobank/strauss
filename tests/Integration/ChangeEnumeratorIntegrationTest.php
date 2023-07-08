<?php

namespace AlexSoft\Strauss\Tests\Integration;

use AlexSoft\Strauss\ChangeEnumerator;
use AlexSoft\Strauss\Composer\ComposerPackage;
use AlexSoft\Strauss\Composer\Extra\StraussConfig;
use AlexSoft\Strauss\Composer\ProjectComposerPackage;
use AlexSoft\Strauss\Copier;
use AlexSoft\Strauss\FileEnumerator;
use AlexSoft\Strauss\Tests\Integration\Util\IntegrationTestCase;

/**
 * Class CopierTest
 * @package AlexSoft\Strauss
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
  "name": "alexsoft/strauss",
  "require": {
    "google/apiclient": "*"
  },
  "extra": {
    "strauss": {
      "namespace_prefix": "AlexSoft\\Strauss\\",
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
