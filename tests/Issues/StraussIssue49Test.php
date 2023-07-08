<?php
/**
 * @see https://github.com/fotobank/strauss/issues/49
 */

namespace AlexSoft\Strauss\Tests\Issues;

use AlexSoft\Strauss\Composer\Extra\StraussConfig;
use AlexSoft\Strauss\Console\Commands\Compose;
use AlexSoft\Strauss\Prefixer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package AlexSoft\Strauss\Tests\Issues
 * @coversNothing
 */
class StraussIssue49Test extends \AlexSoft\Strauss\Tests\Integration\Util\IntegrationTestCase
{

    /**
     */
    public function test_local_symlinked_repositories_fail()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "alexsoft/strauss-local-symlinked-repositories-fail",
  "minimum-stability": "dev",
  "repositories": {
    "alexsoft/bh-wp-logger": {
        "type": "path",
        "url": "../bh-wp-logger"
    },
    "alexsoft/bh-wp-private-uploads": {
        "type": "git",
        "url": "https://github.com/alexsoft/bh-wp-private-uploads"
    }
  },
  "require": {
    "alexsoft/bh-wp-logger": "dev-master"
  },
  "extra": {
    "strauss": {
      "namespace_prefix": "AlexSoft\\Strauss_Local_Symlinked_Repositories_Fail\\",
      "target_directory": "/strauss/",
      "classmap_prefix": "BH_Strauss_Local_Symlinked_Repositories_Fail_"
    }
  }
}
EOD;

        // 1. Git clone alexsoft/bh-wp-logger into the temp dir.
        chdir($this->testsWorkingDir);

        exec('git clone https://github.com/fotobank/bh-wp-logger.git');

        mkdir($this->testsWorkingDir . 'project');

        // 2. Create the project composer.json in a subdir (one level).
        file_put_contents($this->testsWorkingDir . 'project/composer.json', $composerJsonString);

        chdir($this->testsWorkingDir.'project');

        exec('composer install');

        $inputInterfaceMock = $this->createMock(InputInterface::class);
        $outputInterfaceMock = $this->createMock(OutputInterface::class);

        $strauss = new Compose();

        $result = $strauss->run($inputInterfaceMock, $outputInterfaceMock);

        $this->assertNotEquals(1, $result);
    }
}
