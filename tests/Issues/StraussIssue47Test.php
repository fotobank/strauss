<?php
/**
 * When the namespace being replaced is a substring of the prefix, the order of replacements
 * is important, otherwise the replacement is performed twice.
 *
 * @see \AlexSoft\Strauss\Prefixer::replaceInString()
 * @see asort()
 *
 * @see https://core.trac.wordpress.org/ticket/42670
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
class StraussIssue47Test extends \AlexSoft\Strauss\Tests\Integration\Util\IntegrationTestCase
{

    /*
     * The proper failing test.
     */
    public function test_double_namespace()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "alexsoft/double-namespace-47",
  "minimum-stability": "dev",
  "repositories": {
    "dragon-public/framework": {
        "type": "git",
        "url": "https://gitlab.com/dragon-public/framework/"
    }
  },  
  "require": {
	"dragon-public/framework": "*"
  },
  "extra": {
    "strauss": {
      "namespace_prefix": "Dragon\\Dependencies\\",
      "target_directory": "/strauss/",
      "classmap_prefix": "Dragon_Dependencies_"
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

        // 0 for no errors.
        $this->assertNotEquals(1, $result);

        $php_string = file_get_contents($this->testsWorkingDir . 'strauss/dragon-public/framework/src/Form/TextArea.php');

        $this->assertStringNotContainsString('namespace Dragon\Dependencies\Dragon\Dependencies\Dragon\Form;', $php_string);
        $this->assertStringContainsString('namespace Dragon\Dependencies\Dragon\Form;', $php_string);
    }

    /*
     * Exclude all other packages, so step debugging has less noise.
     */
    public function test_double_namespace_dont_copy_dependencies()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "alexsoft/double-namespace-47",
  "minimum-stability": "dev",
  "repositories": {
    "dragon-public/framework": {
        "type": "git",
        "url": "https://gitlab.com/dragon-public/framework/"
    }
  },  
  "require": {
	"dragon-public/framework": "*"
  },
  "extra": {
    "strauss": {
      "namespace_prefix": "Dragon\\Dependencies\\",
      "target_directory": "/strauss/",
      "classmap_prefix": "Dragon_Dependencies_",
      "exclude_from_copy": {
        "packages": [
			"guzzlehttp/guzzle",
			"ramsey/uuid",
			"illuminate/database",
			"illuminate/filesystem",
			"illuminate/translation",
			"illuminate/validation",
			"illuminate/pagination",
			"symfony/var-dumper",
			"doctrine/dbal"
        ]
      },
      "exclude_from_prefix": {
        "namespaces": [
			"voku\\",
			"Symfony\\",
			"Ramsey\\",
			"Illuminate\\",
			"GuzzleHttp\\",
			"Egulias\\",
			"Doctrine\\",
			"Carbon",
			"Brick\\"
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

        $strauss = new Compose();

        $result = $strauss->run($inputInterfaceMock, $outputInterfaceMock);

        // 0 for no errors.
        $this->assertNotEquals(1, $result);

        $php_string = file_get_contents($this->testsWorkingDir . 'strauss/dragon-public/framework/src/Form/TextArea.php');

        $this->assertStringNotContainsString('namespace Dragon\Dependencies\Dragon\Dependencies\Dragon\Form;', $php_string);
        $this->assertStringContainsString('namespace Dragon\Dependencies\Dragon\Form;', $php_string);
    }

    /**
     * Test only one file. This did not fail.
     */
    public function test_double_namespace_only_file_copied()
    {

        $composerJsonString = <<<'EOD'
{
  "name": "alexsoft/double-namespace-47",
  "minimum-stability": "dev",
  "repositories": {
    "dragon-public/framework": {
        "type": "git",
        "url": "https://gitlab.com/dragon-public/framework/"
    }
  },  
  "require": {
	"dragon-public/framework": "*"
  },
  "extra": {
    "strauss": {
      "namespace_prefix": "Dragon\\Dependencies\\",
      "target_directory": "/strauss/",
      "classmap_prefix": "Dragon_Dependencies_",
      "exclude_from_copy": {
        "file_patterns": [
            "/^((?!Form\\/TextArea.php$).)*$/"
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

        $strauss = new Compose();

        $result = $strauss->run($inputInterfaceMock, $outputInterfaceMock);

        // 0 for no errors.
        $this->assertNotEquals(1, $result);

        $php_string = file_get_contents($this->testsWorkingDir . 'strauss/dragon-public/framework/src/Form/TextArea.php');

        $this->assertStringNotContainsString('namespace Dragon\Dependencies\Dragon\Dependencies\Dragon\Form;', $php_string);
        $this->assertStringContainsString('namespace Dragon\Dependencies\Dragon\Form;', $php_string);
    }
}
