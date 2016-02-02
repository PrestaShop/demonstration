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
namespace PrestaShop\Demonstration\Services;

use Image;
use ImageManager;
use ImageType;
use PrestaShopException;
use Tools;

class ImageUploader
{
    /**
     * Upload an image located in $url and save it in a path
     * according to $entity->id_{entity}.
     *
     * @param int $entityId id of product or category (set in entity)
     * @param int $imageId (default null) id of the image.
     * @param string $imagePath path or url to use
     * @param string $type 'products' or 'categories'
     * @param bool $regenerate
     * @param int $shopId the shopId
     * @return bool|PrestaShopException true or exception in case of failure
     * @throws PrestaShopException
     */
    public static function upload($entityId, $imageId = null, $imagePath = '', $type = 'products', $regenerate = true, $shopId)
    {
        $tempFile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');

        switch ($type) {
            default:
            case 'products':
                $image_obj = new Image($imageId);
                $path = $image_obj->getPathForCreation();
                break;
        }

        if(Tools::copy($imagePath, $tempFile)) {
            $tgt_width = $tgt_height = 0;
            $src_width = $src_height = 0;
            $error = 0;
            ImageManager::resize($tempFile, $imagePath, null, null, 'jpg', false, $error, $tgt_width, $tgt_height, 5, $src_width, $src_height);
            $images_types = ImageType::getImagesTypes($type, true);

            if ($regenerate) {
                $previous_path = null;
                $path_infos = array();
                $path_infos[] = array($tgt_width, $tgt_height, $path.'.jpg');

                foreach ($images_types as $image_type) {
                    $path = self::getBestPath($image_type['width'], $image_type['height'], $path_infos);

                    if (ImageManager::resize(
                        $tempFile,
                        $path.'-'.stripslashes($image_type['name']).'.jpg',
                        $image_type['width'],
                        $image_type['height'],
                        'jpg',
                        false,
                        $error,
                        $tgt_width,
                        $tgt_height,
                        5,
                        $src_width,
                        $src_height
                    )) {
                        // the last image should not be added in the candidate list if it's bigger than the original image
                        if ($tgt_width <= $src_width && $tgt_height <= $src_height) {
                            $path_infos[] = array($tgt_width, $tgt_height, $path.'-'.stripslashes($image_type['name']).'.jpg');
                        }
                        if ($type == 'products') {
                            if (is_file(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$entityId.'.jpg')) {
                                unlink(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$entityId.'.jpg');
                            }
                            if (is_file(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$entityId.'_'.(int)$shopId.'.jpg')) {
                                unlink(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$entityId.'_'.(int)$shopId.'.jpg');
                            }
                        }
                    }
                }
            }
        }else {
            throw new PrestaShopException(sprintf('[ImageUploader] unexpected error when copying %s to %s', $tempFile, $imagePath));
        }

        return true;
    }

    protected static function getBestPath($tgt_width, $tgt_height, $path_infos)
    {
        $pathInfos = array_reverse($path_infos);
        $path = '';
        foreach ($pathInfos as $path_info) {
            list($width, $height, $path) = $path_info;
            if ($width >= $tgt_width && $height >= $tgt_height) {
                return $path;
            }
        }
        return $path;
    }
}
