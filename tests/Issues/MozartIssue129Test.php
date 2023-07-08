<?php
/**
 * Namespaces with escaped backslashes in strings are not replaced.
 *
 * @see https://github.com/coenjacobs/mozart/issues/129
 *
 * Also affects mpdf: Tag.php:170
 *
 * $className = 'Mpdf\Tag\\';
 *
 * @author BrianHenryIE
 */

namespace AlexSoft\Strauss\Tests\Issues;

use AlexSoft\Strauss\ChangeEnumerator;
use AlexSoft\Strauss\Composer\Extra\StraussConfig;
use AlexSoft\Strauss\Prefixer;
use PHPUnit\Framework\TestCase;

/**
 * Class MozartIssue129Test
 * @package AlexSoft\Strauss\Tests\Issues
 * @coversNothing
 */
class MozartIssue129Test extends TestCase
{

    /**
     * @author BrianHenryIE
     *
     * @dataProvider pairTestDataProvider
     */
    public function test_test($phpString, $expected)
    {

        $config = $this->createMock(StraussConfig::class);

        $original = 'Example\Sdk\Endpoints';
        $replacement = 'Strauss\Example\Sdk\Endpoints';

        $replacer = new Prefixer($config, __DIR__);

        $result = $replacer->replaceNamespace($phpString, $original, $replacement);

        $this->assertEquals($expected, $result);
    }

    public function pairTestDataProvider()
    {

        $fromTo = [];

        $contents = <<<'EOD'
$baseNamespace = "\Example\Sdk\Endpoints";
EOD;
        $expected = <<<'EOD'
$baseNamespace = "\Strauss\Example\Sdk\Endpoints";
EOD;
        $fromTo[] = [ $contents, $expected];

        $contents = <<<'EOD'
$baseNamespace = "Example\\Sdk\\Endpoints";
EOD;
        $expected = <<<'EOD'
$baseNamespace = "Strauss\\Example\\Sdk\\Endpoints";
EOD;
        $fromTo[] = [ $contents, $expected];

        $contents = <<<'EOD'
$baseNamespace = "Example\Sdk\Endpoints";
EOD;
        $expected = <<<'EOD'
$baseNamespace = "Strauss\Example\Sdk\Endpoints";
EOD;
        $fromTo[] = [ $contents, $expected];

        $contents = <<<'EOD'
$baseNamespace = '\\Example\\Sdk\\Endpoints';
EOD;
        $expected = <<<'EOD'
$baseNamespace = '\\Strauss\\Example\\Sdk\\Endpoints';
EOD;
        $fromTo[] = [ $contents, $expected];

        $contents = <<<'EOD'
$baseNamespace = '\Example\Sdk\Endpoints';
EOD;
        $expected = <<<'EOD'
$baseNamespace = '\Strauss\Example\Sdk\Endpoints';
EOD;
        $fromTo[] = [ $contents, $expected];

        $contents = <<<'EOD'
$baseNamespace = 'Example\\Sdk\\Endpoints';
EOD;
        $expected = <<<'EOD'
$baseNamespace = 'Strauss\\Example\\Sdk\\Endpoints';
EOD;
        $fromTo[] = [ $contents, $expected];

        $contents = <<<'EOD'
$baseNamespace = 'Example\Sdk\Endpoints';
EOD;
        $expected = <<<'EOD'
$baseNamespace = 'Strauss\Example\Sdk\Endpoints';
EOD;
        $fromTo[] = [ $contents, $expected];

        return $fromTo;
    }
}
