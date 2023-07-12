<?php
/**
 * Namespaces in constants not replaced
 * @see https://github.com/coenjacobs/mozart/issues/13
 *
 */

namespace AlexLabs\Strauss\Tests\Issues;

use AlexLabs\Strauss\Console\Commands\Compose;
use AlexLabs\Strauss\Tests\Integration\Util\IntegrationTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MozartIssue13Test
 * @package AlexLabs\Strauss\Tests\Issues
 * @coversNothing
 */
class MozartIssue13Test extends IntegrationTestCase
{

    /**
     *
     * "paypal/rest-api-sdk-php"
     *
     */
    public function testPaypalStringReplacement()
    {

//        $this->markTestSkipped('This test was passing until I excluded the PSR namespace');

        $composerJsonString = <<<'EOD'
{
	"name": "AlexLabs/mozart-issue-13",
	"require": {
		"paypal/rest-api-sdk-php": "*"
	},
	"extra": {
		"strauss": {
			"namespace_prefix": "AlexLabs\\Strauss\\",
			"classmap_prefix": "AlexLabs_Strauss_",
			"exclude_from_prefix": {
			    "file_patterns": [
			    ]
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

        $result = $mozartCompose->run($inputInterfaceMock, $outputInterfaceMock);

        $phpString = file_get_contents($this->testsWorkingDir .'vendor-prefixed/paypal/rest-api-sdk-php/lib/PayPal/Log/PayPalLogger.php');

        // Confirm solution is correct.
        $this->assertStringContainsString('constant("\\\\AlexLabs\\\\Strauss\\\\Psr\\\\Log\\\\LogLevel::$loggingLevel")', $phpString);
    }
}
