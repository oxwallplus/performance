<?php
/*
 * @version 1.0
 * @copyright Copyright (C) 2016 ArtMedia. All rights reserved.
 * @license OSCL, see http://www.oxwall.org/store/oscl
 * @website http://artmedia.biz.pl
 * @author Arkadiusz Tobiasz
 * @email kontakt@artmedia.biz.pl
 */


class PERFORMANCE_BOL_DbCacheBackend implements OW_ICacheBackend
{
    const CACHE_TIME = 86400;

    private $cache;

    private static $classInstance;
    
    public static function getInstance() {
        if(self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    protected function __construct() {
        $this->cache = PERFORMANCE_BOL_DbCacheService::getInstance();
    }
    
    public function save($data, $key, array $tags = array(), $expTime) {
        if(!$expTime) {
            $expTime = self::CACHE_TIME;
        }
        
        $cache = $this->cache->set($key, $data, $expTime);
        
        if(count($tags)) {
            foreach($tags as $tag) {
                $this->cache->setTag($cache->getId(), $tag);
            }
        }
    }
    
    public function load($key) {
        return $this->cache->get($key);
    }
    
    public function test($key) {
        return $this->cache->get($key);
    }

    public function remove($key) {
        return $this->cache->remove($key);
    }
    
    public function clean(array $tags, $mode = null) {
        return $this->cache->clean($tags, $mode);
    }
}
