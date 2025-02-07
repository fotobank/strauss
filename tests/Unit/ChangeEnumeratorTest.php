<?php

namespace AlexSoft\Strauss\Tests\Unit;

use AlexSoft\Strauss\ChangeEnumerator;
use AlexSoft\Strauss\Composer\ComposerPackage;
use AlexSoft\Strauss\Composer\Extra\StraussConfig;
use AlexSoft\Strauss\Prefixer;
use Composer\Composer;
use PHPUnit\Framework\TestCase;

class ChangeEnumeratorTest extends TestCase
{

    // PREG_BACKTRACK_LIMIT_ERROR

    // Single implied global namespace.
    // Single named namespace.
    // Single explicit global namespace.
    // Multiple namespaces.



    public function testSingleNamespace()
    {

        $validPhp = <<<'EOD'
<?php
namespace MyNamespace;

class MyClass {
}
EOD;

        $config = $this->createMock(StraussConfig::class);
        $config->method('getNamespacePrefix')->willReturn('Prefix');
        $sut = new ChangeEnumerator($config);

        $sut->find($validPhp);

        $this->assertArrayHasKey('MyNamespace', $sut->getDiscoveredNamespaceReplacements(), 'Found: ' . implode(',', $sut->getDiscoveredNamespaceReplacements()));
        $this->assertContains('Prefix\MyNamespace', $sut->getDiscoveredNamespaceReplacements());

        $this->assertNotContains('MyClass', $sut->getDiscoveredClasses());
    }

    public function testGlobalNamespace()
    {

        $validPhp = <<<'EOD'
<?php
namespace {
    class MyClass {
    }
}
EOD;

        $config = $this->createMock(StraussConfig::class);
        $sut = new ChangeEnumerator($config);

        $sut->find($validPhp);

        $this->assertContains('MyClass', $sut->getDiscoveredClasses());
    }

    /**
     *
     */
    public function testMultipleNamespace()
    {

        $validPhp = <<<'EOD'
<?php
namespace MyNamespace {
}
namespace {
    class MyClass {
    }
}
EOD;

        $config = $this->createMock(StraussConfig::class);
        $sut = new ChangeEnumerator($config);

        $sut->find($validPhp);

        $this->assertContains('\MyNamespace', $sut->getDiscoveredNamespaceReplacements());

        $this->assertContains('MyClass', $sut->getDiscoveredClasses());
    }


    /**
     *
     */
    public function testMultipleNamespaceGlobalFirst()
    {

        $validPhp = <<<'EOD'
<?php

namespace {
    class MyClass {
    }
}
namespace MyNamespace {
    class MyOtherClass {
    }
}
EOD;

        $config = $this->createMock(StraussConfig::class);
        $sut = new ChangeEnumerator($config);

        $sut->find($validPhp);

        $this->assertContains('\MyNamespace', $sut->getDiscoveredNamespaceReplacements());

        $this->assertContains('MyClass', $sut->getDiscoveredClasses());
        $this->assertNotContains('MyOtherClass', $sut->getDiscoveredClasses());
    }


    /**
     *
     */
    public function testMultipleClasses()
    {

        $validPhp = <<<'EOD'
<?php
class MyClass {
}
class MyOtherClass {

}
EOD;

        $config = $this->createMock(StraussConfig::class);
        $sut = new ChangeEnumerator($config);

        $sut->find($validPhp);

        $this->assertContains('MyClass', $sut->getDiscoveredClasses());
        $this->assertContains('MyOtherClass', $sut->getDiscoveredClasses());
    }

    /**
     *
     * @author BrianHenryIE
     */
    public function test_it_does_not_treat_comments_as_classes()
    {
        $contents = "
    	// A class as good as any.
    	class Whatever {
    	
    	}
    	";

        $config = $this->createMock(StraussConfig::class);
        $changeEnumerator = new ChangeEnumerator($config);
        $changeEnumerator->find($contents);

        $this->assertNotContains('as', $changeEnumerator->getDiscoveredClasses());
        $this->assertContains('Whatever', $changeEnumerator->getDiscoveredClasses());
    }

    /**
     *
     * @author BrianHenryIE
     */
    public function test_it_does_not_treat_multiline_comments_as_classes()
    {
        $contents = "
    	 /**
    	  * A class as good as any; class as.
    	  */
    	class Whatever {
    	}
    	";

        $config = $this->createMock(StraussConfig::class);
        $changeEnumerator = new ChangeEnumerator($config);
        $changeEnumerator->find($contents);

        $this->assertNotContains('as', $changeEnumerator->getDiscoveredClasses());
        $this->assertContains('Whatever', $changeEnumerator->getDiscoveredClasses());
    }

    /**
     * This worked without adding the expected regex:
     *
     * // \s*\\/?\\*{2,}[^\n]* |                        # Skip multiline comment bodies
     *
     * @author BrianHenryIE
     */
    public function test_it_does_not_treat_multiline_comments_opening_line_as_classes()
    {
        $contents = "
    	 /** A class as good as any; class as.
    	  *
    	  */
    	class Whatever {
    	}
    	";

        $config = $this->createMock(StraussConfig::class);
        $changeEnumerator = new ChangeEnumerator($config);
        $changeEnumerator->find($contents);

        $this->assertNotContains('as', $changeEnumerator->getDiscoveredClasses());
        $this->assertContains('Whatever', $changeEnumerator->getDiscoveredClasses());
    }


    /**
     *
     * @author BrianHenryIE
     */
    public function test_it_does_not_treat_multiline_comments_on_one_line_as_classes()
    {
        $contents = "
    	 /** A class as good as any; class as. */ class Whatever_Trevor {
    	}
    	";

        $config = $this->createMock(StraussConfig::class);
        $changeEnumerator = new ChangeEnumerator($config);
        $changeEnumerator->find($contents);

        $this->assertNotContains('as', $changeEnumerator->getDiscoveredClasses());
        $this->assertContains('Whatever_Trevor', $changeEnumerator->getDiscoveredClasses());
    }

    /**
     * If someone were to put a semicolon in the comment it would mess with the previous fix.
     *
     * @author BrianHenryIE
     *
     * @test
     */
    public function test_it_does_not_treat_comments_with_semicolons_as_classes()
    {
        $contents = "
    	// A class as good as any; class as versatile as any.
    	class Whatever_Ever {
    	
    	}
    	";

        $config = $this->createMock(StraussConfig::class);
        $changeEnumerator = new ChangeEnumerator($config);
        $changeEnumerator->find($contents);

        $this->assertNotContains('as', $changeEnumerator->getDiscoveredClasses());
        $this->assertContains('Whatever_Ever', $changeEnumerator->getDiscoveredClasses());
    }

    /**
     * @author BrianHenryIE
     */
    public function test_it_parses_classes_after_semicolon()
    {

        $contents = "
	    myvar = 123; class Pear { };
	    ";

        $config = $this->createMock(StraussConfig::class);
        $changeEnumerator = new ChangeEnumerator($config);
        $changeEnumerator->find($contents);

        $this->assertContains('Pear', $changeEnumerator->getDiscoveredClasses());
    }


    /**
     * @author BrianHenryIE
     */
    public function test_it_parses_classes_followed_by_comment()
    {

        $contents = <<<'EOD'
	class WP_Dependency_Installer {
		/**
		 *
		 */
EOD;

        $config = $this->createMock(StraussConfig::class);
        $changeEnumerator = new ChangeEnumerator($config);
        $changeEnumerator->find($contents);

        $this->assertContains('WP_Dependency_Installer', $changeEnumerator->getDiscoveredClasses());
    }


    /**
     * It's possible to have multiple namespaces inside one file.
     *
     * To have two classes in one file, one in a namespace and the other not, the global namespace needs to be explicit.
     *
     * @author BrianHenryIE
     *
     * @test
     */
    public function it_does_not_replace_inside_named_namespace_but_does_inside_explicit_global_namespace_a(): void
    {

        $contents = "
		namespace My_Project {
			class A_Class { }
		}
		namespace {
			class B_Class { }
		}
		";

        $config = $this->createMock(StraussConfig::class);
        $changeEnumerator = new ChangeEnumerator($config);
        $changeEnumerator->find($contents);

        $this->assertNotContains('A_Class', $changeEnumerator->getDiscoveredClasses());
        $this->assertContains('B_Class', $changeEnumerator->getDiscoveredClasses());
    }

    public function testExcludePackagesFromPrefix()
    {

        $config = $this->createMock(StraussConfig::class);
        $config->method('getExcludePackagesFromPrefixing')->willReturn(
            array('alexsoft/pdfhelpers')
        );

        $dir = '';
        $composerPackage = $this->createMock(ComposerPackage::class);
        $composerPackage->method('getPackageName')->willReturn('alexsoft/pdfhelpers');
        $filesArray = array(
            'irrelevantPath' => array(
                'dependency' => $composerPackage
            ),
        );

        $changeEnumerator = new ChangeEnumerator($config);
        $changeEnumerator->findInFiles($dir, $filesArray);

        $this->assertEmpty($changeEnumerator->getDiscoveredNamespaceReplacements());
    }


    public function testExcludeFilePatternsFromPrefix()
    {
        $config = $this->createMock(StraussConfig::class);
        $config->method('getExcludeFilePatternsFromPrefixing')->willReturn(
            array('/to/')
        );

        $dir = '';
        $composerPackage = $this->createMock(ComposerPackage::class);
        $composerPackage->method('getPackageName')->willReturn('alexsoft/pdfhelpers');
        $filesArray = array(
            'path/to/file' => array(
                'dependency' => $composerPackage
            ),
        );

        $changeEnumerator = new ChangeEnumerator($config);
        $changeEnumerator->findInFiles($dir, $filesArray);

        $this->assertEmpty($changeEnumerator->getDiscoveredNamespaceReplacements());
    }

    /**
     * Test custom replacements
     */
    public function testNamespaceReplacementPatterns()
    {

        $contents = "
		namespace AlexSoft\PdfHelpers {
			class A_Class { }
		}
		";

        $config = $this->createMock(StraussConfig::class);
        $config->method('getNamespacePrefix')->willReturn('AlexSoft\Prefix');
        $config->method('getNamespaceReplacementPatterns')->willReturn(
            array('/AlexSoft\\\\(PdfHelpers)/'=>'AlexSoft\\Prefix\\\\$1')
        );

        $changeEnumerator = new ChangeEnumerator($config);
        $changeEnumerator->find($contents);

        $this->assertArrayHasKey('AlexSoft\PdfHelpers', $changeEnumerator->getDiscoveredNamespaceReplacements());
        $this->assertContains('AlexSoft\Prefix\PdfHelpers', $changeEnumerator->getDiscoveredNamespaceReplacements());
        $this->assertNotContains('AlexSoft\Prefix\AlexSoft\PdfHelpers', $changeEnumerator->getDiscoveredNamespaceReplacements());
    }

    /**
     * @see https://github.com/fotobank/strauss/issues/19
     */
    public function testPhraseClassObjectIsNotMistaken()
    {

        $contents = <<<'EOD'
<?php

class TCPDF_STATIC
{

    /**
     * Creates a copy of a class object
     * @param $object (object) class object to be cloned
     * @return cloned object
     * @since 4.5.029 (2009-03-19)
     * @public static
     */
    public static function objclone($object)
    {
        if (($object instanceof Imagick) and (version_compare(phpversion('imagick'), '3.0.1') !== 1)) {
            // on the versions after 3.0.1 the clone() method was deprecated in favour of clone keyword
            return @$object->clone();
        }
        return @clone($object);
    }
}
EOD;

        $config = $this->createMock(StraussConfig::class);
        $changeEnumerator = new ChangeEnumerator($config);
        $changeEnumerator->find($contents);

        $this->assertNotContains('object', $changeEnumerator->getDiscoveredClasses());
    }

    public function testDefineConstant()
    {

        $contents = <<<'EOD'
/*******************************************************************************
 * FPDF                                                                         *
 *                                                                              *
 * Version: 1.83                                                                *
 * Date:    2021-04-18                                                          *
 * Author:  Olivier PLATHEY                                                     *
 *******************************************************************************
 */

define('FPDF_VERSION', '1.83');

define('ANOTHER_CONSTANT', '1.83');

class FPDF
{
EOD;

        $config = $this->createMock(StraussConfig::class);
        $changeEnumerator = new ChangeEnumerator($config);
        $changeEnumerator->find($contents);

        $constants = $changeEnumerator->getDiscoveredConstants();

        $this->assertContains('FPDF_VERSION', $constants);
        $this->assertContains('ANOTHER_CONSTANT', $constants);
    }

    public function test_commented_namespace_is_invalid(): void
    {

        $contents = <<<'EOD'
<?php

// Global. - namespace WPGraphQL;

use WPGraphQL\Utils\Preview;

/**
 * Class WPGraphQL
 *
 * This is the one true WPGraphQL class
 *
 * @package WPGraphQL
 */
final class WPGraphQL {

}
EOD;

        $config = $this->createMock(StraussConfig::class);
        $changeEnumerator = new ChangeEnumerator($config);
        $changeEnumerator->find($contents);

        self::assertArrayNotHasKey('WPGraphQL', $changeEnumerator->getDiscoveredNamespaceReplacements());
        self::assertContains('WPGraphQL', $changeEnumerator->getDiscoveredClasses());
    }
}
