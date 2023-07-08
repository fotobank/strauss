<?php
/**
 *
 * @see https://github.com/fotobank/strauss/issues/37
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
class StraussIssue37Test extends \AlexSoft\Strauss\Tests\Integration\Util\IntegrationTestCase
{

    /**
     */
    public function test_can_handle_psr_namespace_with_path_array()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "alexsoft/strauss-psr-4-path-array",
  "minimum-stability": "dev",
  "require": {
    "automattic/woocommerce": "*"
  },
  "extra": {
    "strauss": {
      "namespace_prefix": "AlexSoft\\Strauss\\",
      "classmap_prefix": "BH_Strauss_"
    }
  }
}

EOD;

        file_put_contents($this->testsWorkingDir . 'composer.json', $composerJsonString);

        chdir($this->testsWorkingDir);

        exec('composer install');

        $inputInterfaceMock = $this->createMock(InputInterface::class);
        $outputInterfaceMock = $this->createMock(OutputInterface::class);

        $strauss = new Compose();

        $result = $strauss->run($inputInterfaceMock, $outputInterfaceMock);

        $this->assertNotEquals(1, $result);
    }
}
