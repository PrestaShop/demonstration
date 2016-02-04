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

namespace PrestaShop\Demonstration\Helper;

use Db;
use PrestaShopException;
use stdClass;

trait FixtureTrait
{
    public static function getFixture($fixtureId)
    {
        $queryResult = Db::getInstance()->executeS(sprintf('SELECT * FROM `'._DB_PREFIX_.'demonstration` WHERE `fixture_id` = %s', $fixtureId));
        if (is_array($queryResult) && !empty($queryResult)) {
            return new Fixture($queryResult[0]);
        }

        throw new PrestaShopException(sprintf('[FixtureTrait] : there is no fixture in database for the id %s', $fixtureId));
    }
}

class Fixture extends stdClass
{
    public function __construct(array $values)
    {
        $this->tableName = $values['table_name'];
        $this->idName = $values['id_name'];
        $this->idValue = $values['id'];
        $this->fixtureId = isset($values['id_fixture']) === true ? $values['id_fixture'] : null;
    }

    public $tableName;
    public $idName;
    public $idValue;
    public $fixtureId;
}
