<?php
/*
 * @version 2.0.0
 * @copyright Copyright (C) 2016 ArtMedia. All rights reserved.
 * @license OSCL, see http://www.oxwall.org/store/oscl
 * @website http://artmedia.biz.pl
 * @author Arkadiusz Tobiasz
 * @email kontakt@artmedia.biz.pl
 */

class PERFORMANCE_DTO_DbCacheTag extends OW_Entity
{
    /**
     * @var string
     */
    public $tag;
    /**
     * @var int
     */
    public $cacheId;
    
    public function setTag($value) {
        $this->tag = $value;
    }
    
    public function getTag() {
        return $this->tag;
    }
    
    public function setCacheId($value) {
        $this->cacheId = $value;
    }
    
    public function getCacheId() {
        return $this->cacheId;
    }
}


