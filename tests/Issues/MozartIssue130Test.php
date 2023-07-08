<?php
/**
 * Carbon Fields, including non-class files
 * @see https://github.com/coenjacobs/mozart/issues/130
 *
 * Basically, Mozart does not support `files` autoloaders. Strauss does!
 *
 * @author BrianHenryIE
 */

namespace AlexSoft\Strauss\Tests\Issues;

use AlexSoft\Strauss\Console\Commands\Compose;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MozartIssue130Test
 * @package AlexSoft\Strauss\Tests\Issues
 * @coversNothing
 */
class MozartIssue130Test extends \AlexSoft\Strauss\Tests\Integration\Util\IntegrationTestCase
{

    /**
     * @author BrianHenryIE
     */
    public function test_config_copied()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "fotobank/mozart-issue-130",
  "require": {
    "htmlburger/carbon-fields": "*"
  },
  "extra": {
    "mozart":{
      "dep_namespace": "MZoo\\MzMboAccess\\",
      "dep_directory": "/strauss/",
      "override_autoload": {
        "htmlburger/carbon-fields": {
          "psr-4": {
            "Carbon_Fields\\": "core/"
          },
          "files": [
            "config.php",
            "templates",
            "assets",
            "build"
          ]
        }
      }
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

        $mozartCompose->run($inputInterfaceMock, $outputInterfaceMock);

        $this->assertFileExists($this->testsWorkingDir .'strauss/htmlburger/carbon-fields/config.php');
    }
}
