<?php
/**
 * @see https://github.com/fotobank/strauss/issues/14
 */

namespace AlexSoft\Strauss\Tests\Issues;

use AlexSoft\Strauss\Console\Commands\Compose;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package AlexSoft\Strauss\Tests\Issues
 * @coversNothing
 */
class StraussIssue14Test extends \AlexSoft\Strauss\Tests\Integration\Util\IntegrationTestCase
{

    /**
     * Looks like the exclude_from_prefix regex for psr is not specific enough.
     *
     * @author BrianHenryIE
     */
    public function test_guzzle_http_is_prefixed()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "alexsoft/strauss-issue-14",
  "require":{
    "guzzlehttp/psr7": "*"
  },
  "extra": {
    "strauss": {
      "namespace_prefix": "AlexSoft\\Strauss\\"
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

        $php_string = file_get_contents($this->testsWorkingDir .'vendor-prefixed/guzzlehttp/psr7/src/AppendStream.php');

        // was namespace GuzzleHttp\Psr7;

        // Confirm solution is correct.
        $this->assertStringContainsString('namespace AlexSoft\Strauss\GuzzleHttp\Psr7;', $php_string);
    }

    public function testFilesAutoloaderIsGenerated()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "alexsoft/strauss-issue-14",
  "require":{
    "guzzlehttp/psr7": "*"
  },
  "require-dev":{
    "alexsoft/strauss": "*"
  },
  "extra": {
    "strauss": {
      "namespace_prefix": "AlexSoft\\Strauss\\"
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

        $this->assertFileExists($this->testsWorkingDir .'vendor-prefixed/autoload-files.php');
    }
}
