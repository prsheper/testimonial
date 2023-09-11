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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Egiotestimonials extends Module
{
    /** @var bool */
    protected $config_form = false;
    /** @var string */
    public $image_path;
    /** @var string */
    public $folder_file_upload;
    /** @var int */
    private $allowed_image_size = 1000000;
    /** @var array */
    private $allowed_image_types = array('png', 'jpeg', 'gif', 'jpg');
    /** @var array */
    private $allowed_image_mime_types = ['image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png'];

    public function __construct()
    {
        $this->name = 'egiotestimonials';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Ilyas Bouamama';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Egio Testimonials');
        $this->description = $this->l('Assist visitors in adding testimonials to the website.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        $this->controllers = array('AdminTestimonials');
        // Settings paths
        if (!$this->_path) {
            $this->_path = __PS_BASE_URI__ . 'modules/' . $this->name . '/';
        }
        $this->image_path = $this->_path . 'uploads/';
        $this->folder_file_upload = _PS_MODULE_DIR_ .$this->name. '/uploads/';
    }

    // For PrestaShop 1.6 compilation
    public function installTab($parent, $class_name, $name)
    {
        $tab = new Tab();
        $tab->module = $this->name;
        //$tab->icon = 'track_changes';
        $tab->active = 1;
        $tab->class_name = $class_name;
        $id_shop = Context::getContext()->shop->id;

        foreach (Language::getLanguages(true, $id_shop) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }

        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            $tab->icon = 'track_changes';
            $tab->id_parent = (int)Tab::getIdFromClassName($parent);
            return $tab->save();
        } else {
            $tab->id_parent = 0;
            return $tab->add();
        }
    }

    // For Prestashop 1.6 compile
    public function uninstallTab($class_name)
    {
        // Retrieve Tab ID
        $id_tab = (int)Tab::getIdFromClassName($class_name);

        // Load tab
        $tab = new Tab((int)$id_tab);

        // Delete it
        return $tab->delete();
    }


    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        Configuration::updateValue('MYEGIOTESTIMONIALS_IMAGE_ALLOWED_SIZE', $this->allowed_image_size);
        Configuration::updateValue('MYEGIOTESTIMONIALS_IMAGE_ALLOWED_TYPES', implode(',', $this->allowed_image_types));
        Configuration::updateValue('MYEGIOTESTIMONIALS_IMAGE_ALLOWED_MIME_TYPES', implode(',', $this->allowed_image_mime_types));

        // Run SQL
        require_once('sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->installTab('AdminParentCustomer', 'AdminTestimonials', $this->l('Testimonials Manager'));

    }


    public function uninstall()
    {
        Configuration::deleteByName('MYEGIOTESTIMONIALS_IMAGE_ALLOWED_SIZE');
        Configuration::deleteByName('MYEGIOTESTIMONIALS_IMAGE_ALLOWED_TYPES');
        Configuration::deleteByName('MYEGIOTESTIMONIALS_IMAGE_ALLOWED_MIME_TYPES');

        // Run SQL
        require_once('sql/uninstall.php');

        return parent::uninstall() && $this->uninstallTab('AdminTestimonials');
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitEgiotestimonialsModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEgiotestimonialsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Enter allowed image size by octet.'),
                        'name' => 'MYEGIOTESTIMONIALS_IMAGE_ALLOWED_SIZE',
                        'label' => $this->l('Allowed image size')
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Enter allowed image mime types.'),
                        'name' => 'MYEGIOTESTIMONIALS_IMAGE_ALLOWED_TYPES',
                        'label' => $this->l('Allowed image types'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Enter allowed image mime types.'),
                        'name' => 'MYEGIOTESTIMONIALS_IMAGE_ALLOWED_MIME_TYPES',
                        'label' => $this->l('Allowed image mime types'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {

        return array(
            'MYEGIOTESTIMONIALS_IMAGE_ALLOWED_SIZE' => Configuration::get('MYEGIOTESTIMONIALS_IMAGE_ALLOWED_SIZE', $this->allowed_image_size),
            'MYEGIOTESTIMONIALS_IMAGE_ALLOWED_TYPES' => Configuration::get('MYEGIOTESTIMONIALS_IMAGE_ALLOWED_TYPES', implode(',', $this->allowed_image_types)),
            'MYEGIOTESTIMONIALS_IMAGE_ALLOWED_MIME_TYPES' => Configuration::get('MYEGIOTESTIMONIALS_IMAGE_ALLOWED_MIME_TYPES', implode(',', $this->allowed_image_mime_types)),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }
}
