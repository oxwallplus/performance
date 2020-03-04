<?php

/*
 * @version 2.0.0
 * @copyright Copyright (C) 2016 ArtMedia. All rights reserved.
 * @license OSCL, see http://www.oxwall.org/store/oscl
 * @website http://artmedia.biz.pl
 * @author Arkadiusz Tobiasz
 * @email kontakt@artmedia.biz.pl
 */

class PERFORMANCE_Cron extends OW_Cron
{
    public function __construct() {
        parent::__construct();
        $config = OW::getConfig();
        $key = PERFORMANCE_BOL_Service::KEY;
        if($config->getValue($key, 'cache') && $config->getValue($key, 'cacheType') == 1) {
            $this->addJob('cacheProcess', 5);
        }
    }
    
    public function run() {
        
    }

    public function cacheProcess() {
        return PERFORMANCE_BOL_DbCacheService::getInstance()->deleteExpiredList();
    }
}