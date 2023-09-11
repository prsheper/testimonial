<?php
/**
* 2007-2023 PrestaShop
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
*  @copyright 2007-2023 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'egiotestimonials` (
    `id_egiotestimonials` int(11) NOT NULL AUTO_INCREMENT,
    `id_shop` INT(11) UNSIGNED NOT NULL,
    `id_shop_group` INT(11) UNSIGNED NOT NULL,
    `title` VARCHAR(60) NOT NULL,
    `message` text NOT NULL,
    `image` text NOT NULL,
    `status` int(11) NOT NULL DEFAULT 0,
    `position` int(11) NULL,
    `soft_delete` int(11) NULL,
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY  (`id_egiotestimonials`, `id_shop`, `id_shop_group`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';


/* $sql[] =  'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'egiotestimonials_group` (
    `id_egiotestimonials` INT(11) UNSIGNED NOT NULL,
    `id_group` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY ( `id_egiotestimonials` , `id_group`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;'; */

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
