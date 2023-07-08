<?php
/**
 * WPGraphQL had the word "namespace" in a comment and it was tripping up the matches.
 *
 * @see https://github.com/fotobank/strauss/issues/66
 */

namespace AlexSoft\Strauss\Tests\Issues;

use AlexSoft\Strauss\Console\Commands\Compose;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package AlexSoft\Strauss\Tests\Issues
 * @coversNothing
 */
class StraussIssue66Test extends \AlexSoft\Strauss\Tests\Integration\Util\IntegrationTestCase
{

    /**
     */
    public function test_wp_graphql_prefix_main_class()
    {

        $composerJsonString = <<<'EOD'
{
  "require": {
    "wp-graphql/wp-graphql": "^1.12"
  },
  "extra": {
    "strauss": {
      "namespace_prefix": "MyProject\\Dependencies\\",
      "classmap_prefix": "Prefix_",
      "constant_prefix": "Prefix_"
    }
  }
}
EOD;

        chdir($this->testsWorkingDir);

        file_put_contents($this->testsWorkingDir . '/composer.json', $composerJsonString);

        exec('composer install');

        $inputInterfaceMock = $this->createMock(InputInterface::class);
        $outputInterfaceMock = $this->createMock(OutputInterface::class);

        $strauss = new Compose();

        $result = $strauss->run($inputInterfaceMock, $outputInterfaceMock);

        $php_string = file_get_contents($this->testsWorkingDir . 'vendor-prefixed/wp-graphql/wp-graphql/src/WPGraphQL.php');

        $this->assertStringContainsString('final class Prefix_WPGraphQL', $php_string);
    }
}
