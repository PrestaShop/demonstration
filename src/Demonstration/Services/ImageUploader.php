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

        if(Tools::copy($imagePath, $tempFile)) {
            $targetWidth = $targetHeight = $srcWidth = $srcHeight = $error = 0;

            ImageManager::resize($tempFile, $imagePath, null, null, 'jpg', false, $error, $targetWidth, $targetHeight, 5, $srcWidth, $srcHeight);

            if ($regenerate) {
                switch ($type) {
                    default:
                    case 'products':
                        $imageObj = new Image($imageId);
                        $imageObjPath = $imageObj->getPathForCreation();
                        break;
                }

                $pathData[] = [$targetWidth, $targetHeight, $imageObjPath];
                $imagesTypes = ImageType::getImagesTypes($type, true);

                foreach ($imagesTypes as $imageType) {
                    $bestPath = self::getBestPath($imageType, $pathData);

                    if (ImageManager::resize(
                        $tempFile,
                        $bestPath,
                        $imageType['width'],
                        $imageType['height'],
                        'jpg',
                        false,
                        $error,
                        $targetWidth,
                        $targetHeight,
                        5,
                        $srcWidth,
                        $srcHeight
                    )) {
                        // the last image should not be added in the candidate list if it's bigger than the original image
                        if ($targetWidth <= $srcWidth && $targetHeight <= $srcHeight) {
                            $pathData[] = array($targetWidth, $targetHeight, $bestPath);
                        }
                        if ($type == 'products') {
                            if (is_file(_PS_TMP_IMG_DIR_.'product_mini_'.$entityId.'.jpg')) {
                                unlink(_PS_TMP_IMG_DIR_.'product_mini_'.$entityId.'.jpg');
                            }
                            if (is_file(_PS_TMP_IMG_DIR_.'product_mini_'.$entityId.'_'.$shopId.'.jpg')) {
                                unlink(_PS_TMP_IMG_DIR_.'product_mini_'.$entityId.'_'.$shopId.'.jpg');
                            }
                        }
                    }else {
                        throw new PrestaShopException(sprintf('[ImageUploader] unexpected error when regenerating %s into %s', $tempFile, $bestPath));
                    }
                }
            }
        }else {
            throw new PrestaShopException(sprintf('[ImageUploader] unexpected error when copying %s to %s', $tempFile, $imagePath));
        }
        return true;
    }

    protected static function getBestPath(array $imageType, array $pathData)
    {
        $targetWidth = $imageType['width'];
        $targetHeight = $imageType['height'];
        $targetName = $imageType['name'];
        $path = '';
        $completePath = '-'.stripslashes($targetName).'.jpg';
        foreach ($pathData as $pathInfo) {
            list($width, $height, $path) = $pathInfo;
            if ($width >= $targetWidth && $height >= $targetHeight) {
                return "{$path}{$completePath}";
            }
        }
        return "{$path}{$completePath}";
    }
}
