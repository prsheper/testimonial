<?php
/**
* 2007-2020 PrestaShop
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
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class TestimonialModel extends ObjectModel
{
    /** @var integer Testimonial id*/
    public $id;

    /** @var string Testimonial title */
    public $title;

    /** @var string Testimonial message */
    public $message;

    /** @var string Testimonial image */
    public $image;

    /** @var integer Testimonial status */
    public $status;

    /** @var string Banner Position*/
    public $position;

    /** @var integer Soft Delete*/
    public $soft_delete;


    /** @var integer ID Shop*/
    public $id_shop;

    /** @var integer ID Shop Group*/
    public $id_shop_group;

    /** @var date Banner date_add*/
    public $date_add;

    /** @var date Banner date_upd*/
    public $date_upd;



    public static $definition = array(
        'table' => 'egiotestimonials',
        'primary' => 'id_egiotestimonials',
        //'multishop' => true,
        'fields' => array(
            'title' =>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'message' =>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'image' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'status' => array('type' => self::TYPE_BOOL),
            'position' => array('type' => self::TYPE_INT),
            'soft_delete' => array('type' => self::TYPE_INT),
            'date_add' =>	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' =>	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        )
    );

    public function __construct($id_testimonial = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id_testimonial, $id_lang, $id_shop);
    }


    public static function getTestimonials($status = 0)
    {
        return Db::getInstance()->executeS('SELECT *, (select count(*) from '. _DB_PREFIX_ . self::$definition['table'].' as testimonialsTotal) FROM '. _DB_PREFIX_ . self::$definition['table'].' WHERE status='.$status.' ORDER BY position asc');
    }

    public function updatePosition($way, $position)
    {

        if (!$testimonials = Db::getInstance()->executeS(
            '
            SELECT `'.self::$definition['primary'].'`, `position`
            FROM `' . _DB_PREFIX_ . self::$definition['table'].'`
            ORDER BY `position` ASC'
        )) {
            return false;
        }

        foreach ($testimonials as $testimonial) {
            if ((int)$testimonial[self::$definition['primary']] == (int)$this->id) {
                $moved_testimonials = $testimonial;
            }
        }

        if (!isset($moved_testimonials) || !isset($position)) {
            return false;
        }
        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        return (Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ .self::$definition['table'].'`
            SET `position`= `position` ' . ($way ? '- 1' : '+ 1') . '
            WHERE `position`
            ' . ($way
            ? '> ' . (int)$moved_testimonials['position'] . ' AND `position` <= ' . (int)$position
            : '< ' . (int)$moved_testimonials['position'] . ' AND `position` >= ' . (int)$position . '
            '))
            && Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . self::$definition['table'].'`
            SET `position` = ' . (int)$position . '
            WHERE `'.self::$definition['primary'].'` = ' . (int)$moved_testimonials[self::$definition['primary']]));
    }

    public static function getHigherPosition()
    {
        $sql = 'SELECT MAX(`position`)
		FROM `' . _DB_PREFIX_ . self::$definition['table'].'`';

        $position = DB::getInstance()->getValue($sql);

        return (is_numeric($position)) ? $position : -1;
    }

    /**
     * Adds current Banner as a new Object to the database.
     *
     * @param bool $autoDate Automatically set `date_upd` and `date_add` column
     * @param bool $nullValues Whether we want to use NULL values instead of empty quotes values
     *
     * @return bool Whether the AttributeGroup has been successfully added
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function add($autoDate = true, $nullValues = false)
    {

        if ($this->position <= 0) {
            $this->position = self::getHigherPosition() + 1;
        }

        if (!parent::add($autoDate, $nullValues)) {
            return false;
        }

        return true;
    }


    /**
     * Deletes current Banner from database.
     *
     * @return bool True if delete was successful
     *
     * @throws PrestaShopException
     */
    public function delete()
    {
        // Delete Group Assosciation
        return parent::delete() && $this->cleanPositions();
    }

    public static function cleanPositions()
    {
        $return = true;

        $sql = 'SELECT `'.self::$definition['primary'].'`
				FROM `' . _DB_PREFIX_ . self::$definition['table'].'`
				ORDER BY `position` ASC';

        $result = Db::getInstance()->executeS($sql);

        $i = 0;

        foreach ($result as $value) {
            $return = Db::getInstance()->execute('
				UPDATE `' . _DB_PREFIX_ . self::$definition['table'].'`
				SET `position` = ' . (int)$i++ . '
				WHERE `'.self::$definition['primary'].'` = ' . (int)$value[self::$definition['primary']]);
        }

        return $return;
    }

}
