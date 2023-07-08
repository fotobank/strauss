<?php
/**
 * @see https://github.com/fotobank/strauss/issues/44
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
class StraussIssue44Test extends \AlexSoft\Strauss\Tests\Integration\Util\IntegrationTestCase
{

    /**
     * Unprefixed static function call in ternary operation.
     *
     * @author BrianHenryIE
     */
    public function testStaticIsNotPrefixed()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "alexsoft/strauss-issue-44",
  "require": {
    "guzzlehttp/guzzle": "7.4.5"
  },
  "extra": {
    "strauss": {
      "namespace_prefix": "Strauss\\Issue44\\",
      "classmap_prefix": "Strauss_Issue44_"
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

        $php_string = file_get_contents($this->testsWorkingDir . 'vendor-prefixed/guzzlehttp/guzzle/src/BodySummarizer.php');

        $this->assertStringNotContainsString('? \GuzzleHttp\Psr7\Message::bodySummary($message)', $php_string);
        
        $this->assertStringContainsString('? \Strauss\Issue44\GuzzleHttp\Psr7\Message::bodySummary($message)', $php_string);
    }
}
