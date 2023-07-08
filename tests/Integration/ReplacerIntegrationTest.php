<?php

namespace AlexSoft\Strauss\Tests\Integration;

use AlexSoft\Strauss\ChangeEnumerator;
use AlexSoft\Strauss\Composer\ComposerPackage;
use AlexSoft\Strauss\Composer\Extra\StraussConfig;
use AlexSoft\Strauss\Composer\ProjectComposerPackage;
use AlexSoft\Strauss\Console\Commands\Compose;
use AlexSoft\Strauss\Copier;
use AlexSoft\Strauss\FileEnumerator;
use AlexSoft\Strauss\Prefixer;
use AlexSoft\Strauss\Tests\Integration\Util\IntegrationTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReplacerIntegrationTest
 * @package AlexSoft\Strauss\Tests\Integration
 * @coversNothing
 */
class ReplacerIntegrationTest extends IntegrationTestCase
{

    public function testReplaceNamespace()
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
      "classmap_prefix": "BrianHenryIE_Strauss_"
    }
  }
}
EOD;

        file_put_contents($this->testsWorkingDir . 'composer.json', $composerJsonString);

        chdir($this->testsWorkingDir);

        exec('composer install');

        $projectComposerPackage = new ProjectComposerPackage($this->testsWorkingDir);
        $config = $projectComposerPackage->getStraussConfig();

        $dependencies = array_map(function ($element) {
            $dir = $this->testsWorkingDir . 'vendor'. DIRECTORY_SEPARATOR . $element;
            return ComposerPackage::fromFile($dir);
        }, $projectComposerPackage->getRequiresNames());

        $workingDir = $this->testsWorkingDir;
        $relativeTargetDir = 'vendor-prefixed' . DIRECTORY_SEPARATOR;
        $absoluteTargetDir = $workingDir . $relativeTargetDir;
        $vendorDir = 'vendor' . DIRECTORY_SEPARATOR;

//        $config = $this->createStub(StraussConfig::class);
//        $config->method('getTargetDirectory')->willReturn('vendor-prefixed' . DIRECTORY_SEPARATOR);

        $fileEnumerator = new FileEnumerator($dependencies, $workingDir, $config);
        $fileEnumerator->compileFileList();
        $fileList = $fileEnumerator->getAllFilesAndDependencyList();
        $phpFileList = $fileEnumerator->getPhpFilesAndDependencyList();

        $copier = new Copier($fileList, $workingDir, $relativeTargetDir, $vendorDir);
        $copier->prepareTarget();
        $copier->copy();

        $changeEnumerator = new ChangeEnumerator($config);
        $changeEnumerator->findInFiles($absoluteTargetDir, $phpFileList);

        $namespaces = $changeEnumerator->getDiscoveredNamespaceReplacements();
        $classes = $changeEnumerator->getDiscoveredClasses();
        $constants = array();

        $replacer = new Prefixer($config, $workingDir);

        $replacer->replaceInFiles($namespaces, $classes, $constants, $phpFileList);

        $updatedFile = file_get_contents($absoluteTargetDir . 'google/apiclient/src/Client.php');

        $this->assertStringContainsString('use AlexSoft\Strauss\Google\AccessToken\Revoke;', $updatedFile);
    }


    public function testReplaceClass()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "alexsoft/strauss",
  "require": {
    "setasign/fpdf": "*"
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

        $inputInterfaceMock = $this->createMock(InputInterface::class);
        $outputInterfaceMock = $this->createMock(OutputInterface::class);

        $mozartCompose = new Compose();

        $result = $mozartCompose->run($inputInterfaceMock, $outputInterfaceMock);


//        $projectComposerPackage = new ProjectComposerPackage($this->testsWorkingDir);
//        $config = $projectComposerPackage->getStraussConfig();
//
//        $dependencies = array_map(function ($element) {
//            $dir = $this->testsWorkingDir . 'vendor'. DIRECTORY_SEPARATOR . $element;
//            return ComposerPackage::fromFile($dir);
//        }, $projectComposerPackage->getRequiresNames());
//
//        $workingDir = $this->testsWorkingDir;
//        $relativeTargetDir = 'vendor-prefixed' . DIRECTORY_SEPARATOR;
//        $absoluteTargetDir = $workingDir . $relativeTargetDir;
//
//        $fileEnumerator = new FileEnumerator($dependencies, $workingDir);
//        $fileEnumerator->compileFileList();
//        $fileList = $fileEnumerator->getAllFilesAndDependencyList();
//        $phpFileList = $fileEnumerator->getPhpFilesAndDependencyList();
//
//        $copier = new Copier($fileList, $workingDir, $relativeTargetDir);
//        $copier->prepareTarget();
//        $copier->copy();
//
//        $changeEnumerator = new ChangeEnumerator();
//        $changeEnumerator->findInFiles($absoluteTargetDir, $phpFileList);
//        $namespaces = $changeEnumerator->getDiscoveredNamespaces();
//        $classes = $changeEnumerator->getDiscoveredClasses();
//
//        $replacer = new Replacer($config, $workingDir);
//
//        $replacer->replaceInFiles($namespaces, $classes, $phpFileList);

        $updatedFile = file_get_contents($this->testsWorkingDir .'vendor-prefixed/' . 'setasign/fpdf/fpdf.php');

        $this->assertStringContainsString('class BrianHenryIE_Strauss_FPDF', $updatedFile);
    }
}
