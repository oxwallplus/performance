<?php
/*
 * @version 2.0.0
 * @copyright Copyright (C) 2016 ArtMedia. All rights reserved.
 * @license OSCL, see http://www.oxwallplus.com/oscl
 * @website http://artmedia.biz.pl
 * @author Arkadiusz Tobiasz
 * @email kontakt@artmedia.biz.pl
 */

class PERFORMANCE_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    private $key;
    private $language;
    
    public function __construct() {
        parent::__construct();

        $this->key = PERFORMANCE_BOL_Service::KEY;
        $this->language = OW::getLanguage();
        
        $this->setPageTitle($this->language->text($this->key, 'admin_title').' | '.$this->getPageTitle());
        $this->setPageHeading($this->language->text($this->key, 'admin_title'));
        $this->setPageHeadingIconClass('ow_ic_star');

        OW::getNavigation()->activateMenuItem('admin_settings', 'admin', 'sidebar_menu_performance');
        $this->assign('key', $this->key);
    }

    public function index() {        
        $form = new PERFORMANCE_FORM_Settings();

        if(OW::getRequest()->isPost()) {
            $ajax = OW::getRequest()->isAjax();
            $status = 'error';
        
            if($form->isValid(OW::getRequest()->getPost())) {
                $data = $form->getValues();

                $result = $form->processForm($data);
                $message = $result['message'];
                $status = $result['status'];
            } else {
                $message = $this->language->text('admin', 'settings_submit_error_message');
                foreach($form->getErrors() as $error) {
                    if($error) {
                        $message .= '<br />'.(is_array($error) ? implode('<br />', $error) : $error);
                    }
                }
            }
            
            if($ajax) {
                exit(json_encode(array('status' => $status, 'message' => $message)));
            } else {
                if($status == 'error') {
                    OW::getFeedback()->error($message);
                } else {
                    OW::getFeedback()->info($message);
                }
            }
        }
        
        $settings = OW::getConfig()->getValues($this->key);
        $form->bind($settings);
        $form->setAjax(true);
        $form->setAction(OW::getRouter()->urlForRoute($this->key.'.admin'));
        $form->setAjaxResetOnSuccess(false);
        $form->bindJsFunction(Form::BIND_SUCCESS, "function(data){ if(data.status == 'success') { OW.info(data.message); } else { OW.error(data.message); } }");
        
        $this->addForm($form);
    }

    
}