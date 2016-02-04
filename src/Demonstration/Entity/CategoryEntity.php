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

namespace PrestaShop\Demonstration\Entity;

use PrestaShop\Demonstration\Contract\EntityInterface;
use PrestaShop\Demonstration\Services\ImageUploader;
use Context;
use Configuration;
use Image;
use Category;
use stdClass;
use Tools;

class CategoryEntity implements EntityInterface
{
    public static function create(array $values, $assetsPath)
    {
        $language = Context::getContext()->language;
        $shop = Context::getContext()->shop;
        $defaultCategoryId = $shop->getCategory();

        $category = new Category(null, false, $language->id);
        foreach ($values as $property => $value) {
            if (property_exists('Category', $property)) {
                $category->{$property} = $value;
            }
        }

        $category->active = 1;
        $category->link_rewrite = Tools::link_rewrite($category->name);
        $category->id_shop_default = $shop->id;
        $category->id_parent = $defaultCategoryId;
        $category->regenerateEntireNtree();

        if (isset($values['ps_id'])) {

        }

        if ($category->save()) {
            if (isset($values['images'])) {
                self::manageImages($category, $values['images'], $assetsPath.'/img/');
            }

            return  [
                'id' => $category->id,
                'table_name' => 'product',
                'id_name' => 'id_product',
                'fixture_id' => $values['ps_id'],
            ];
        }

        return false;
    }

    /**
     * @param $category Category instance:
     * - an `src` property used to move images from modules to Product images folder
     * - an `alt` property refers to HTML attribute
     * - an `cssClass` property refers to `css` HTML attribute
     * @param $images stdClass[] a collection of Images from configuration
     * @param $imgPath define folder where images should be found
     */
    public static function manageImages(Category $category, $images, $imgPath)
    {
        foreach ($images as $imageObject) {
            self::createAndUploadImage($category, $imageObject, $category->id_shop_default, $imgPath);
        }
    }

    private static function createAndUploadImage($category, array $imageArray, $shopId, $imgPath)
    {
        $image = new Image();
        $image->legend = $imageArray['alt'];

        $image->save();

        ImageUploader::upload(
            $category->id,
            $image->id,
            $imgPath.$imageArray['src'],
            'categories',
            true,
            $shopId
        );

        $category->id_image = $image->id;
        $category->update();
    }
}
