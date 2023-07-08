<?php

namespace AlexSoft\Strauss\Tests\Integration;

use AlexSoft\Strauss\Composer\ComposerPackage;
use AlexSoft\Strauss\Composer\Extra\StraussConfig;
use AlexSoft\Strauss\Composer\ProjectComposerPackage;
use AlexSoft\Strauss\FileEnumerator;
use AlexSoft\Strauss\Tests\Integration\Util\IntegrationTestCase;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class FileEnumeratorIntegrationTest
 * @package AlexSoft\Strauss
 * @coversNothing
 */
class FileEnumeratorIntegrationTest extends IntegrationTestCase
{

    public function testBuildFileList()
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

        // Only one because we haven't run "flat dependency list".
        $dependencies = array_map(function ($element) {
            $dir = $this->testsWorkingDir . 'vendor'. DIRECTORY_SEPARATOR . $element;
            return ComposerPackage::fromFile($dir);
        }, $projectComposerPackage->getRequiresNames());

        $workingDir = $this->testsWorkingDir;
        $vendorDir = 'vendor' . DIRECTORY_SEPARATOR;

        $config = $this->createStub(StraussConfig::class);
        $config->method('getVendorDirectory')->willReturn($vendorDir);

        $fileEnumerator = new FileEnumerator($dependencies, $workingDir, $config);

        $fileEnumerator->compileFileList();

        $list = array_keys($fileEnumerator->getAllFilesAndDependencyList());

        $this->assertContains('google/apiclient/src/aliases.php', $list);
    }


    public function testClassmapAutoloader()
    {
        $this->markTestIncomplete();
    }


    public function testFilesAutoloader()
    {
        $this->markTestIncomplete();
    }
}
