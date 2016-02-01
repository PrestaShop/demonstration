<?php
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

use PrestaShop\Demonstration\Config\ConfigurationProvider;

class ConfigurationProviderTest extends \PHPUnit_Framework_TestCase
{
    const NOTICE = "[Config parsing]";

    /* @var $provider ConfigurationProvider */
    private $provider;

    protected function setUp()
    {
        $this->provider = new ConfigurationProvider();
    }

    protected function tearDown()
    {
        $this->provider = null;
    }

    /**
     * Should return an array with 'products', 'users' and 'foo' first level keys.
     *
     * According to fixtures:
     * - 'products' key is an array of 7 entries
     * - 'users' key is an array of 4 entries
     * - foo key is an array of 1 entry
     *
     * Validation process is not part of this test.
     * Integrity tests are already done in Symfony Config component.
     */
    public function testProcessFromPath()
    {
        $config = $this->provider->processFromPath(__DIR__.'/../fixtures/fake-module/config/', 'config.yml');
        $this->assertInternalType('array', $config, self::NOTICE." ConfigProvider::processFrom Path MUST return an array");

        $expectedSections = ['products', 'users', 'foo'];
        foreach($expectedSections as $section) {
            $this->assertArrayHasKey($section, $config, sprintf(self::NOTICE." The expected key %s is missing from the parsed configuration", $section));
        }

        $products = $config['products'];
        $this->assertTrue(count($products) == 7, sprintf(self::NOTICE." The number of expected products is wrong: 7 expected, got %s.", count($products)));

        $users = $config['users'];
        $this->assertTrue(count($users) == 4, sprintf(self::NOTICE." The number of expected products is wrong: 4 expected, got %s.", count($users)));

        $foo = $config['foo'];
        $this->assertTrue(count($foo) == 1, sprintf(self::NOTICE." The number of expected products is wrong: 1 expected, got %s.", count($foo)));
    }

    /**
     * We expect to return an exception with an explicit message.
     *
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage The file "malformed-config.yml" does not contain valid YAML.
     */
    public function testProcessFromPathMalformedConfig()
    {
        $this->provider->processFromPath(__DIR__.'/../fixtures/fake-module/malformed-config/', 'malformed-config.yml');
    }
}
