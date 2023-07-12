<?php
/**
 * When users migrate from Mozart, the settings are only preserved when the extra "mozart" key
 * is still used. Let's change it so they're understood not matter what.
 *
 * @see https://github.com/fotobank/strauss/issues/11
 */

namespace AlexLabs\Strauss\Tests\Issues;

use AlexLabs\Strauss\Composer\Extra\StraussConfig;
use AlexLabs\Strauss\Console\Commands\Compose;
use Composer\Factory;
use Composer\IO\NullIO;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package AlexLabs\Strauss\Tests\Issues
 * @coversNothing
 */
class StraussIssue11Test extends \AlexLabs\Strauss\Tests\Integration\Util\IntegrationTestCase
{

    /**
     * @author BrianHenryIE
     */
    public function test_migrate_mozart_config()
    {

        $composerExtraStraussJson = <<<'EOD'
{
	"name": "AlexLabs/strauss-issue-8",
	"extra": {
		"mozart": {
			"dep_namespace": "MZoo\\MBO_Sandbox\\Dependencies\\",
			"dep_directory": "/src/Mozart/",
			"packages": [
				"htmlburger/carbon-fields",
				"ericmann/wp-session-manager",
				"ericmann/sessionz"
			],
			"delete_vendor_files": false,
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

        $tmpfname = tempnam(sys_get_temp_dir(), 'strauss-test-');
        file_put_contents($tmpfname, $composerExtraStraussJson);

        $composer = Factory::create(new NullIO(), $tmpfname);

        $straussConfig = new StraussConfig($composer);

        $this->assertEquals('src/Mozart/', $straussConfig->getTargetDirectory());

        $this->assertEquals("MZoo\\MBO_Sandbox\\Dependencies", $straussConfig->getNamespacePrefix());
    }



    /**
     * @author BrianHenryIE
     */
    public function test_carbon_fields()
    {

        $composerJsonString = <<<'EOD'
{
	"name": "AlexLabs/strauss-issue-8",
	"require":{
	    "htmlburger/carbon-fields": "*"
	},
	"extra": {
		"mozart": {
			"dep_namespace": "MZoo\\MBO_Sandbox\\Dependencies\\",
			"dep_directory": "/src/Mozart/",
			"packages": [
				"htmlburger/carbon-fields"
			],
			"delete_vendor_files": false,
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

        $result = $mozartCompose->run($inputInterfaceMock, $outputInterfaceMock);

        $phpString = file_get_contents($this->testsWorkingDir .'src/Mozart/htmlburger/carbon-fields/core/Carbon_Fields.php');

        // This was not being prefixed.
        $this->assertStringNotContainsString('$ioc->register( new \Carbon_Fields\Provider\Container_Condition_Provider() );', $phpString);

        $this->assertStringContainsString('$ioc->register( new \MZoo\MBO_Sandbox\Dependencies\Carbon_Fields\Provider\Container_Condition_Provider() );', $phpString);
    }


    /**
     * @author BrianHenryIE
     */
    public function test_static_namespace()
    {

        $composerJsonString = <<<'EOD'
{
	"name": "AlexLabs/strauss-issue-8",
	"require":{
	    "htmlburger/carbon-fields": "*"
	},
	"extra": {
		"mozart": {
			"dep_namespace": "MZoo\\MBO_Sandbox\\Dependencies\\",
			"dep_directory": "/src/Mozart/",
			"packages": [
				"htmlburger/carbon-fields"
			],
			"delete_vendor_files": false,
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

        $result = $mozartCompose->run($inputInterfaceMock, $outputInterfaceMock);

        $phpString = file_get_contents($this->testsWorkingDir .'src/Mozart/htmlburger/carbon-fields/core/Container.php');

        // This was not being prefixed.
        $this->assertStringNotContainsString('@method static \Carbon_Fields\Container\Comment_Meta_Container', $phpString);

        $this->assertStringContainsString('@method static \MZoo\MBO_Sandbox\Dependencies\Carbon_Fields\Container\Comment_Meta_Container', $phpString);
    }
}
