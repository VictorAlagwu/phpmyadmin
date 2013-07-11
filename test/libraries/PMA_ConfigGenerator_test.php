<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * tests for methods under Config file generator
 *
 * @package PhpMyAdmin-test
 */

/*
 * Include to test
 */
require_once 'setup/lib/ConfigGenerator.class.php';
require_once 'libraries/config/ConfigFile.class.php';
require_once 'libraries/core.lib.php';
require_once 'libraries/Util.class.php';

/**
 * tests for methods under Config file generator
 *
 * @package PhpMyAdmin-test
 */
class PMA_ConfigGenerator_Test extends PHPUnit_Framework_TestCase
{

    /**
     * Test for ConfigGenerator::getConfigFile
     * 
     * @return void
     */
    public function testGetConfigFile()
    {   
        $GLOBALS['cfg']['AvailableCharsets'] = array();
        $GLOBALS['PMA_Config'] = new PMA_Config();
        unset($_SESSION['eol']);
        
        $attr_instance = new ReflectionProperty("ConfigFile", "_instance");
        $attr_instance->setAccessible(true);
        $attr_instance->setValue(null, null);

        $GLOBALS['server'] = 0;
        $cf = ConfigFile::getInstance();
        $_SESSION['ConfigFile0']['Servers'] = array(
            array(1, 2, 3)
        );

        $cf->setPersistKeys(array("1/", 2));
        
        $result = ConfigGenerator::getConfigFile();
        
        $this->assertContains(
            "<?php\n" .
            "/*\n" .
            " * Generated configuration file\n" .
            " * Generated by: phpMyAdmin 4.1-dev setup script\n" .
            " * Date: " . date(DATE_RFC1123) ."\n" .
            " */\n\n",
            $result
        );

        $this->assertContains(
            "/* Servers configuration */\n" .
            '$i = 0;' . "\n\n" .
            "/* Server: localhost [0] */\n" .
            '$i++;' . "\n" .
            '$cfg[\'Servers\'][$i][\'0\'] = 1;' . "\n" .
            '$cfg[\'Servers\'][$i][\'1\'] = 2;' . "\n" .
            '$cfg[\'Servers\'][$i][\'2\'] = 3;' . "\n\n" .
            "/* End of servers configuration */\n\n",
            $result
        );

        $this->assertContains(
            '?>',
            $result
        );
    }

    /**
     * Test for ConfigGenerator::_getVarExport
     * 
     * @return void
     */
    public function testGetVarExport()
    {
        $reflection = new \ReflectionClass('ConfigGenerator');
        $method = $reflection->getMethod('_getVarExport');
        $method->setAccessible(true);
        
        $this->assertEquals(
            '$cfg[\'var_name\'] = 1;' ."\n",
            $method->invoke(null, 'var_name', 1, "\n")
        );

        $this->assertEquals(
            '$cfg[\'var_name\'] = array (' .
            "\n);\n",
            $method->invoke(null, 'var_name', array(), "\n")
        );

        $this->assertEquals(
            '$cfg[\'var_name\'] = array(1, 2, 3);' . "\n",
            $method->invoke(
                null,
                'var_name',
                array(1, 2, 3),
                "\n"
            )
        );
        
        $this->assertEquals(
            '$cfg[\'var_name\'][\'1a\'] = \'foo\';' . "\n" . 
            '$cfg[\'var_name\'][\'b\'] = \'bar\';' . "\n",
            $method->invoke(
                null,
                'var_name',
                array(
                    '1a' => 'foo',
                    'b' => 'bar'
                ),
                "\n"
            )
        );
    }

    /**
     * Test for ConfigGenerator::_isZeroBasedArray
     * 
     * @return void
     */
    public function testIsZeroBasedArray()
    {   
        $reflection = new \ReflectionClass('ConfigGenerator');
        $method = $reflection->getMethod('_isZeroBasedArray');
        $method->setAccessible(true);

        $this->assertFalse(
            $method->invoke(
                null,
                array(
                    'a' => 1,
                    'b' => 2
                )
            )
        );

        $this->assertFalse(
            $method->invoke(
                null,
                array(
                    0 => 1,
                    1 => 2,
                    3 => 3,
                )
            )
        );

        $this->assertTrue(
            $method->invoke(
                null,
                array()
            )
        );

        $this->assertTrue(
            $method->invoke(
                null,
                array(1, 2, 3)
            )
        );
    }

    /**
     * Test for ConfigGenerator::_exportZeroBasedArray
     * 
     * @return void
     */
    public function testExportZeroBasedArray()
    {
        $reflection = new \ReflectionClass('ConfigGenerator');
        $method = $reflection->getMethod('_exportZeroBasedArray');
        $method->setAccessible(true);

        $arr = array(1, 2, 3, 4);

        $result = $method->invoke(null, $arr, "\n");

        $this->assertEquals(
            'array(1, 2, 3, 4)',
            $result
        );

        $arr = array(1, 2, 3, 4, 7, 'foo');

        $result = $method->invoke(null, $arr, "\n");
        
        $this->assertEquals(
            'array(' . "\n" .
            '    1,' . "\n" .
            '    2,' . "\n" .
            '    3,' . "\n" .
            '    4,' . "\n" .
            '    7,' . "\n" .
            '    \'foo\')',
            $result
        );
    }
}
?>
