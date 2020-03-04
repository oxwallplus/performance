<?php
/*
 * @version 2.0.0
 * @copyright Copyright (C) 2016 ArtMedia. All rights reserved.
 * @license OSCL, see http://www.oxwallplus.com/oscl
 * @website http://artmedia.biz.pl
 * @author Arkadiusz Tobiasz
 * @email kontakt@artmedia.biz.pl
 */

class PERFORMANCE_CLASS_EventHandler
{
    private $key;
    private $config;
    private $eventManager;
    private $service;
    
    private static $classInstance;

    public static function getInstance() {
        if(self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    public function __construct() {
        $this->key = PERFORMANCE_BOL_Service::KEY;
        $this->service = PERFORMANCE_BOL_Service::getInstance();
        $this->config = OW::getConfig();;
        $this->eventManager = OW::getEventManager();
    }
    
    public function genericInit() {
        if($this->config->getValue($this->key, 'compressJs') || $this->config->getValue($this->key, 'compressCss')) {
            $this->eventManager->bind("core.after_master_page_render", [$this, "compressHandler"]);
            if($this->config->getValue($this->key, 'compressedCssFiles')) {
                $this->eventManager->bind(OW_DeveloperTools::EVENT_UPDATE_CACHE_ENTITIES, [$this, "refreshFiles"]);
            }
        }
        if($this->service->avaibleGzip()) {
            $this->eventManager->bind("core.before_master_page_render", [$this, "startGzip"]);
            $this->eventManager->bind("core.exit", [$this, "flushGzip"]);
        }
    }

    public function init() {
        if(OW_DEV_MODE) {
            return;
        }
        $this->genericInit();
    }
    
    public function compressHandler() {
        if(OW_DEV_MODE || OW::getRequest()->isAjax()) {
            return;
        }

        $this->service->processPage();
    }
    
    public function refreshFiles() {
        $this->service->removeFiles();
    }
    
    public function startGzip() {
        ob_start("ob_gzhandler");
    }
    
    public function flushGzip() {
        ob_end_flush();
    }

}
