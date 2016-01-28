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
namespace PrestaShop\Demonstration;

use PrestaShop\Demonstration\Config\ConfigurationProvider;

final class DemoInstaller
{
    private $database;

    public function __construct()
    {
        $this->database = \Db::getInstance();
    }

    public function processConfiguration()
    {
        return (new ConfigurationProvider())->processFromPath();
    }

    public function install()
    {
        $config = $this->processConfiguration();
        dump($config);die;

    }

    public function uninstall()
    {
        $trashEntities = $this->database->executeS('SELECT * FROM `'._DB_PREFIX_.'demonstration`');

        foreach ($trashEntities as $entity) {
            $this->database->delete($entity['table_name'], $entity['id_name'].' IN ('.$entity['ids'].')');
        }
    }
}
