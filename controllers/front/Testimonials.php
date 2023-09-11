<?php
/**
 * Lnk Banner Manager
 *
 * @author    L'nkboot
 * @copyright L'nkboot 2021
 * @license   http://www.lnkboot.fr/
 */

require_once(_PS_MODULE_DIR_.'/egiotestimonials/models/TestimonialModel.php');
class EgiotestimonialsTestimonialsModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function initContent()
    {

        // Retrieve all testimonials that have been both approved.
        $testimonials = TestimonialModel::getTestimonials(1);
        $this->context->smarty->assign(array('testimonials' => $testimonials, 'image_path' => $this->module->image_path));
        $this->setTemplate('module:egiotestimonials/views/templates/front/testimonials.tpl');
        parent::initContent();
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->registerStylesheet('theme-testimonial-css', _MODULE_DIR_ . $this->module->name.'/views/css/testimonials.css', ['media' => 'all', 'priority' => 100]);
        $this->registerJavascript('theme-testimonial-js', _MODULE_DIR_ . $this->module->name.'/views/js/testimonials.js', ['priority' => 100]);
    }

    public function postProcess()
    {
        if(Tools::getIsset('addtestimonialsubmit')) {

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
                }

                // If there are no errors, then save the testimonial.
                if(count($this->errors) == 0) {

                    $testimonial = new TestimonialModel();
                    $testimonial->title = Tools::getValue('title');
                    if (isset($file_name)) {
                        $testimonial->image = $file_name;
                    }
                    $testimonial->message = Tools::getValue('message');

                    $testimonial->save();

                    $this->success[] = $this->l('Your testimonial has been sent successfully and is awaiting approval.', [], 'Modules.Egiotestimonials.Front');
                }

            } catch(PrestaShopException $e) {

                PrestaShopLogger::addLog($e->getMessage());
                $this->errors[] = $e->getMessage();
            }

            $this->redirectWithNotifications($this->getCurrentURL());

        }

    }
}
