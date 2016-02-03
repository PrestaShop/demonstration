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

use ImageType;

class ImageUploaderTest extends \PHPUnit_Framework_TestCase
{
    const NOTICE = "[Image uploader]";

    public function getBestPath(array $imageType, array $pathData)
    {
        $method = new \ReflectionMethod('\PrestaShop\Demonstration\Services\ImageUploader', 'getBestPath');
        $method->setAccessible(true);

        return $method->invokeArgs(null, [$imageType, $pathData]);
    }

    public function testGetBestPathFormatting()
    {
        $imageTypes = ImageType::getImagesTypes('products', true);
        $pathData = [
            0 => [
                0 => 800,
                1 => 800,
                2 => 'path/to/prestashop/img/p/1/9/5/195'
            ]
        ];

        foreach($imageTypes as $imageType) {
            $actual = $this->getBestPath($imageType, $pathData);
            $expected = $pathData[0][2].'-'.$imageType['name'].'.jpg';
            $this->assertSame($actual,
                $expected,
                sprintf('%s unexpected image formatting on resizing: got %s, expect %s',
                    self::NOTICE,
                    $actual,
                    $expected
                )
            );
        }
    }
}
