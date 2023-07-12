<?php
/**
 * @see https://github.com/fotobank/strauss/issues/8
 */

namespace AlexLabs\Strauss\Tests\Issues;

use AlexLabs\Strauss\Console\Commands\Compose;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package AlexLabs\Strauss\Tests\Issues
 * @coversNothing
 */
class StraussIssue8Test extends \AlexLabs\Strauss\Tests\Integration\Util\IntegrationTestCase
{

    /**
     * @author BrianHenryIE
     */
    public function test_delete_vendor_files()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "AlexLabs/strauss-issue-8",
  "require": {
    "htmlburger/carbon-fields": "*"
  },
  "extra": {
    "strauss":{
      "delete_vendor_files": true
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

        $this->assertEquals(0, $result);

        $this->assertFileDoesNotExist($this->testsWorkingDir. 'vendor/htmlburger/carbon-fields/core/Carbon_Fields.php');
    }
}
