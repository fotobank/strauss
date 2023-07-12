<?php
/**
 * nesbot/carbon empty searchNamespace
 * @see https://github.com/coenjacobs/mozart/issues/109
 *
 * Comments were being prefixed.
 */

namespace AlexLabs\Strauss\Tests\Issues;

use AlexLabs\Strauss\Console\Commands\Compose;
use AlexLabs\Strauss\Tests\Integration\Util\IntegrationTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MozartIssue109Test
 * @package AlexLabs\Strauss\Tests\Issues
 * @coversNothing
 */
class MozartIssue109Test extends IntegrationTestCase
{

    public function testTheOutputDoesNotPrefixComments()
    {

        $composerJsonString = <<<'EOD'
{
  "minimum-stability": "dev",
  "require": {
    "nesbot/carbon":"1.39.0"
  },
  "config": {
    "process-timeout": 0,
    "sort-packages": true,
    "allow-plugins": {
        "kylekatarnls/update-helper": true
    }
  },
  "extra": {
    "mozart": {
      "dep_namespace": "Mozart\\",
      "dep_directory": "/vendor-prefixed/",
      "delete_vendor_files": false,
      "exclude_packages": [
        "kylekatarnls/update-helper",
        "symfony/polyfill-intl-idn",
        "symfony/translation",
        "symfony/polyfill-mbstring",
        "symfony/translation-contracts",
        "composer-plugin-api"
      ]
    }
  }
}
EOD;

        file_put_contents($this->testsWorkingDir . 'composer.json', $composerJsonString);

        chdir($this->testsWorkingDir);

        exec('composer install');

        assert(file_exists($this->testsWorkingDir .'vendor/nesbot/carbon/src/Carbon/Carbon.php'));

        $inputInterfaceMock = $this->createMock(InputInterface::class);
        $outputInterfaceMock = $this->createMock(OutputInterface::class);

        $mozartCompose = new Compose();

        $result = $mozartCompose->run($inputInterfaceMock, $outputInterfaceMock);

        $phpString = file_get_contents($this->testsWorkingDir .'vendor-prefixed/nesbot/carbon/src/Carbon/Carbon.php');

        $this->assertStringNotContainsString('*Mozart\\ This file is part of the Carbon package.Mozart\\', $phpString);
    }
}
