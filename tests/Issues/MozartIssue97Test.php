<?php
/**
 * The Packagist named crewlabs/unsplash has the composer name unsplash/unsplash.
 */

namespace AlexLabs\Strauss\Tests\Issues;

use AlexLabs\Strauss\Console\Commands\Compose;
use AlexLabs\Strauss\Tests\Integration\Util\IntegrationTestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MozartIssue97Test
 * @package AlexLabs\Strauss\Tests\Issues
 * @coversNothing
 */
class MozartIssue97Test extends IntegrationTestCase
{

    /**
     * Issue 97. Package named "crewlabs/unsplash" is downloaded to `vendor/crewlabs/unsplash` but their composer.json
     * has the package name as "unsplash/unsplash".
     *
     * "The "/Users/AlexLabs/Sites/mozart-97/vendor/unsplash/unsplash/src" directory does not exist."
     */
    public function testCrewlabsUnsplashSucceeds()
    {

        $composerJsonString = <<<'EOD'
{
	"name": "AlexLabs/mozart-issue-97",
	"require": {
		"crewlabs/unsplash": "3.1.0"
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

        $inputInterfaceMock = $this->createMock(InputInterface::class);
        $outputInterfaceMock = $this->createMock(OutputInterface::class);

        $mozartCompose = new Compose();

        $result = $mozartCompose->run($inputInterfaceMock, $outputInterfaceMock);

        $this->assertEquals(0, $result);
    }
}
