<?php
namespace PrestaShop\Demonstration\Test\Loader;
/**
 * 2007-2015 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2015 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use PrestaShop\Demonstration\Loader\YamlLoader;
use Symfony\Component\Config\FileLocator;

class YamLoaderTest extends \PHPUnit_Framework_TestCase
{
    const NOTICE = "[Config loading]";

    /* @var $loader YamlLoader */
    private $loader;

    /* @var $locator FileLocator */
    private $locator;

    /* @var $configPath string */
    private $configPath;

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function loadFile($path)
    {
        $reflection = new \ReflectionClass(get_class($this->loader));
        $method = $reflection->getMethod('loadFile');
        $method->setAccessible(true);

        return $method->invokeArgs($this->loader, [$path]);
    }

    protected function setUp()
    {
        $method = new \ReflectionMethod('\PrestaShop\Demonstration\Loader\YamlLoader', 'loadFile');
        $method->setAccessible(true);

        $this->configPath = __DIR__.'/../fixtures/fake-module/';
        $this->locator    = new FileLocator($this->configPath);
        $this->loader     = new YamlLoader($this->locator);
    }

    protected function tearDown()
    {
        $this->configPath = null;
        $this->locator    = null;
        $this->loader     = null;
    }

    /**
     * YamlLoader should only accepts yaml extension and won't try to parse others files.
     */
    public function testSupport()
    {
        $this->assertFalse($this->loader->supports($this->configPath.'non-supported-config/config.php'));
        $this->assertTrue($this->loader->supports($this->configPath.'malformed-config/config.yml'));
    }

    /**
     * Should return a configuration array or throw an exception.
     * - RuntimeException if Config component is not installed (wont be tested)
     * - InvalidArgumentException if file does not exits in local folder
     * - InvalidArgumentException if file have an unexpected file extension
     * - InvalidArgumentException if the YAML is invalid
     */

    public function testLoadFile()
    {
        $validConfig = $this->loadFile($this->configPath.'config/config.yml');
        $this->assertInternalType('array', $validConfig);
        $this->assertNotEmpty($validConfig, self::NOTICE . "this configuration can't be empty");
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage The file "non_config.yml" does not exists.
     */
    public function testLoadFileWithNonFile()
    {
        $this->loadFile($this->configPath.'config/non_config.yml');
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage The file "config.php" is not a YAML file.
     */
    public function testLoadFileWithUnsupportedFile()
    {
        $this->loadFile($this->configPath.'non-supported-config/config.php');
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage The file "malformed-config.yml" does not contain valid YAML.
     */
    public function testLoadFileWithMalformedFile()
    {
        $this->loadFile($this->configPath.'malformed-config/malformed-config.yml');
    }
}
