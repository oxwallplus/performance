<?php
/*
 * @version 2.0.0
 * @copyright Copyright (C) 2016 ArtMedia. All rights reserved.
 * @license OSCL, see http://www.oxwallplus.com/oscl
 * @website http://artmedia.biz.pl
 * @author Arkadiusz Tobiasz
 * @email kontakt@artmedia.biz.pl
 */

class PERFORMANCE_FORM_Settings extends Form
{    
    private $key;
    private $language;
    
    public function __construct() {
        $this->key = PERFORMANCE_BOL_Service::KEY;
        parent::__construct('settings');
        
        $this->language = OW::getLanguage();
        
        $cache = new ELEMENT_Checkbox('cache');
        $cache->setSwitch();
        $cache->setLabel($this->language->text($this->key, 'enable_cache'), array('class' => 'col-sm-4 col-form-label'));
        $this->addElement($cache);
        
        $cacheType = new ELEMENT_Select('cacheType');
        $cacheType->setOptions(PERFORMANCE_BOL_Service::getInstance()->getBackends());
        $cacheType->setRequired();
        $cacheType->setLabel($this->language->text($this->key, 'cache_type'), array('class' => 'col-sm-4 col-form-label'));
        $this->addElement($cacheType);
        
        $compressJs = new ELEMENT_Checkbox('compressJs');
        $compressJs->setSwitch();
        $compressJs->setLabel($this->language->text($this->key, 'compress_js_files'), array('class' => 'col-sm-4 col-form-label'));
        $this->addElement($compressJs);
        
        $compressCss = new ELEMENT_Checkbox('compressCss');
        $compressCss->setSwitch();
        $compressCss->setLabel($this->language->text($this->key, 'compress_css_files'), array('class' => 'col-sm-4 col-form-label'));
        $this->addElement($compressCss);
        
        $saveCompressedFiles = new ELEMENT_Checkbox('saveCompressedFiles');
        $saveCompressedFiles->setSwitch();
        $saveCompressedFiles->setLabel($this->language->text($this->key, 'storage_compressed_files'), array('class' => 'col-sm-4 col-form-label'));
        $this->addElement($saveCompressedFiles);
        
        $gzip = new ELEMENT_Checkbox('gzip');
        $gzip->setSwitch();
        $gzip->setLabel($this->language->text($this->key, 'enable_gzip_compress'), array('class' => 'col-sm-4 col-form-label'));
        $this->addElement($gzip);
        
        $submit = new ELEMENT_Button('submit');
        $submit->setValue($this->language->text('base', 'edit_button'));
        $this->addElement($submit);
    }
    
    public function processForm($data) {        
        unset($data['form_name']);
        unset($data['csrf_token']);
        
        $config = OW::getConfig();
        
        foreach($data as $name => $value) {
            if(is_int($value)) {
                $value = (int)$value;
            }
            $config->saveConfig($this->key, $name, $value);
        }
        return array('status' => 'success', 'message' => $this->language->text('admin', 'main_settings_updated'));
        
    }
}

