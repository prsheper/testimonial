<?php

use PaypalAddons\classes\InstallmentBanner\BannerManager;
use PrestaShopBundle\Entity\Lang;

require_once _PS_MODULE_DIR_ . '/egiotestimonials/models/TestimonialModel.php';

class AdminTestimonialsController extends ModuleAdminController
{
    protected $id_egiotestimonials;
    protected $position_identifier = 'id_egiotestimonials';

    public function __construct()
    {

        $this->module = Module::getInstanceByName('egiotestimonials');

        $this->bootstrap = true;
        $this->table = 'egiotestimonials';
        $this->className = TestimonialModel::class;
        $this->identifier = 'id_egiotestimonials';
        $this->_orderBy = 'position';
        $this->_defaultOrderBy = 'position';

        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->bulk_actions = [
            'delete' => [
                'text' => Context::getContext()->getTranslator()->trans('Delete selected', [], 'Admin.Notifications.Info'),
                'confirm' => Context::getContext()->getTranslator()->trans('Delete selected items?', [], 'Admin.Notifications.Info'),
                'icon' => 'icon-trash',
            ],
        ];

        $this->fields_list = [
            'id_egiotestimonials' => [
                'title' => $this->module->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'title' => [
                'title' => $this->module->l('Title'),
            ],
            'status' => [
                'title' => $this->module->l('Status'),
                'align' => 'center',
                'type' => 'select',
                'list' => array(
                    0 => $this->module->l('Waiting'),
                    1 => $this->module->l('Approved'),
                    2 => $this->module->l('Refused'),
                ),
                'filter_key' => 'a!status',
                'filter_type' => 'int',
                'order_key' => 'a!name',
                'callback' => 'showApprovedTitleMethod'
            ],
            'position' => [
                'title' => $this->module->l('Position'),
                'filter_key' => 'a!position',
                'align' => 'center',
                'class' => 'fixed-width-sm',
                'position' => 'position',
            ],
        ];


        parent::__construct();
    }

    public function showApprovedTitleMethod($id_egiotestimonials, $row)
    {
        switch($row['status']) {
            case 0:
                return '<span class="badge badge-warning">'.$this->module->l('Waiting').'</span>';
                break;
            case 1:
                return '<span class="badge badge-success">'.$this->module->l('Approved').'</span>';
                break;
            case 2:
                return '<span class="badge badge-danger">'.$this->module->l('Refused').'</span>';
                break;


        }
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);


    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_title = $this->module->l('Testimonials');
        if ($this->display != 'view') {
            $this->page_header_toolbar_btn['link_front'] = [
                'href' => $this->context->link->getModuleLink($this->module->name, 'testimonials'),
                'desc' => $this->module->l('Testimonials front page'),
                'icon' => 'process-icon-preview',
            ];
        }

        parent::initPageHeaderToolbar();
    }



    public function renderForm()
    {

        if (!($obj = $this->loadObject(true))) {
            return;
        }

        // Load current testimonial image
        $image = $obj->image ? '<div class="col-lg-6"><img src="' . $this->module->image_path.$obj->image . '" class="img-thumbnail" width="400"></div>' : false;

        $this->fields_form = [
            'legend' => [
                'title' => $this->module->l('Testimonials'),
                'icon' => 'icon-list',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->module->l('Title'),
                    'name' => 'title',
                    'required' => true,
                    'maxlength' => 60,
                ],
                [
                    'type' => 'file',
                    'label' => $this->module->l('Image'),
                    'name' => 'image',
                    'image' => $image,
                    'display_image' => true,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->module->l('Message', [], 'Admin.Shipping.Feature'),
                    'name' => 'message',
                    'required' => true,
                    'maxlength' => 300,
                    'rows' => 7,
                ],

                [
                    'type' => 'select',
                    'label' => $this->module->l('Status'),
                    'name' => 'status',
                    'options' => [
                        'query' => [
                            [
                                'id' => 1,
                                'name' => $this->module->l('Approved'),
                            ],
                            [
                                'id' => 2,
                                'name' => $this->module->l('Refused'),
                            ]
                        ],
                        'id' => 'id',
                        'name' => 'name',
                        'default' => [
                            'label' => $this->module->l('Waiting'),
                            'value' => 0,
                        ],
                    ],
                ],
            ],
        ];

        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = [
                'type' => 'shop',
                'label' => $this->module->l('Shop association'),
                'name' => 'checkBoxShopAsso',
            ];
        }

        $this->fields_form['submit'] = [
            'title' => $this->trans('Save', [], 'Admin.Actions'),
        ];

        return parent::renderForm();
    }

    public function postProcess()
    {

        parent::postProcess();

        if(Tools::getIsset('submitAddegiotestimonials')) {

            if (!($testimonial = $this->loadObject(true))) {
                return;
            }

            try {
                // Upload File
                if (isset($_FILES['image'])
                && isset($_FILES['image']['tmp_name'])
                && !empty($_FILES['image']['tmp_name'])) {

                    $ext = substr($_FILES['image']['name'], strrpos($_FILES['image']['name'], '.') + 1);

                    // Check the authorized size and types of the image
                    if ($error = ImageManager::validateUpload($_FILES['image'], Configuration::get('MYEGIOTESTIMONIALS_IMAGE_ALLOWED_SIZE'), explode(',', Configuration::get('MYEGIOTESTIMONIALS_IMAGE_ALLOWED_TYPES')), explode(',', Configuration::get('MYEGIOTESTIMONIALS_IMAGE_ALLOWED_MIME_TYPES')))) {
                        $this->errors[] = $error;
                    } else {

                        $file_name = md5($_FILES['image']['name']) . '.' . $ext;

                        if (!move_uploaded_file($_FILES['image']['tmp_name'], $this->module->folder_file_upload. $file_name)) {
                            $this->errors[] = $this->trans('An error occurred while attempting to upload the file.', [], 'Admin.Notifications.Error');
                        }
                    }

                    @unlink($this->module->folder_file_upload.$testimonial->image);
                }

            } catch(PrestaShopException $e) {

                PrestaShopLogger::addLog($e->getMessage());
                $this->errors[] = $e->getMessage();
            }

            // If there are no errors, then save the testimonial.
            if(count($this->errors) == 0) {
                $testimonial->title = Tools::getValue('title');
                if (isset($file_name)) {
                    $testimonial->image = $file_name;
                }
                $testimonial->message = Tools::getValue('message');
                $testimonial->status = Tools::getValue('status');

                $testimonial->update();
                return true;
            }

            return false;

        }
    }

    // Update Items Position
    public function ajaxProcessUpdatePositions()
    {

        $way = (int) (Tools::getValue('way'));
        $id = (int) (Tools::getValue('id'));

        if (Tools::getIsset('egiotestimonials')) {

            $positions = Tools::getValue('egiotestimonials');

            foreach ($positions as $position => $value) {
                $pos = explode('_', $value);

                if (isset($pos[2]) && (int) $pos[2] === $id) {
                    if ($testimonial = new TestimonialModel((int) $pos[2])) {
                        if (isset($position) && $testimonial->updatePosition($way, $position)) {
                            echo 'ok position ' . (int) $position . ' for testimonial ' . (int) $pos[1] . '\r\n';
                        } else {
                            echo '{"hasError" : true, "errors" : "Can not update testimonial ' . (int) $id . ' to position ' . (int) $position . ' "}';
                        }
                    } else {
                        echo '{"hasError" : true, "errors" : "This banner (' . (int) $id . ') can t be loaded"}';
                    }

                    break;
                }
            }
        }
    }

}
