<?php
/**
 *
 * @see https://github.com/fotobank/strauss/issues/37
 */

namespace AlexLabs\Strauss\Tests\Issues;

use AlexLabs\Strauss\Composer\Extra\StraussConfig;
use AlexLabs\Strauss\Console\Commands\Compose;
use AlexLabs\Strauss\Prefixer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package AlexLabs\Strauss\Tests\Issues
 * @coversNothing
 */
class StraussIssue37Test extends \AlexLabs\Strauss\Tests\Integration\Util\IntegrationTestCase
{

    /**
     */
    public function test_can_handle_psr_namespace_with_path_array()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "AlexLabs/strauss-psr-4-path-array",
  "minimum-stability": "dev",
  "require": {
    "automattic/woocommerce": "*"
  },
  "extra": {
    "strauss": {
      "namespace_prefix": "AlexLabs\\Strauss\\",
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
