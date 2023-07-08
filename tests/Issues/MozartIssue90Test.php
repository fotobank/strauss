<?php
/**
 * @see https://github.com/coenjacobs/mozart/issues/90
 */

namespace AlexSoft\Strauss\Tests\Issues;

use AlexSoft\Strauss\Console\Commands\Compose;
use AlexSoft\Strauss\Tests\Integration\Util\IntegrationTestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MozartIssue90Test
 * @package AlexSoft\Strauss\Tests\Issues
 * @coversNothing
 */
class MozartIssue90Test extends IntegrationTestCase
{

    /**
     * Issue 90. Needs "iio/libmergepdf".
     *
     * Error: "File already exists at path: classmap_directory/tecnickcom/tcpdf/tcpdf.php".
     */
    public function testLibpdfmergeSucceeds()
    {

        $composerJsonString = <<<'EOD'
{
	"name": "alexsoft/mozart-issue-90",
	"require": {
		"iio/libmergepdf": "4.0.4"
	},
	"extra": {
		"strauss": {
			"namespace_prefix": "AlexSoft\\Strauss\\",
			"classmap_prefix": "AlexSoft_Strauss_"
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

        // This test would only fail on Windows?
        $this->assertDirectoryDoesNotExist($this->testsWorkingDir .'strauss/iio/libmergepdf/vendor/iio/libmergepdf/tcpdi');

        $this->assertFileExists($this->testsWorkingDir .'vendor-prefixed/iio/libmergepdf/tcpdi/tcpdi.php');
    }
}
