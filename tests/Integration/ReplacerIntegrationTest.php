<?php

namespace AlexLabs\Strauss\Tests\Integration;

use AlexLabs\Strauss\ChangeEnumerator;
use AlexLabs\Strauss\Composer\ComposerPackage;
use AlexLabs\Strauss\Composer\Extra\StraussConfig;
use AlexLabs\Strauss\Composer\ProjectComposerPackage;
use AlexLabs\Strauss\Console\Commands\Compose;
use AlexLabs\Strauss\Copier;
use AlexLabs\Strauss\FileEnumerator;
use AlexLabs\Strauss\Prefixer;
use AlexLabs\Strauss\Tests\Integration\Util\IntegrationTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReplacerIntegrationTest
 * @package AlexLabs\Strauss\Tests\Integration
 * @coversNothing
 */
class ReplacerIntegrationTest extends IntegrationTestCase
{

    public function testReplaceNamespace()
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

        $this->assertStringContainsString('use AlexLabs\Strauss\Google\AccessToken\Revoke;', $updatedFile);
    }


    public function testReplaceClass()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "AlexLabs/strauss",
  "require": {
    "setasign/fpdf": "*"
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
