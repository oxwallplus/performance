<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.oxwall.org/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Oxwall software.
 * The Initial Developer of the Original Code is Oxwall Foundation (http://www.oxwall.org/foundation).
 * All portions of the code written by Oxwall Foundation are Copyright (c) 2011. All Rights Reserved.

 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2011 Oxwall Foundation. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Oxwall community software
 * Attribution URL: http://www.oxwall.org/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */

/**
 * Database cache service
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class PERFORMANCE_BOL_DbCacheService implements OW_CacheService
{
    /**
     * 
     * @var BOL_DbCacheDao
     */
    private $dbCacheDao;
    
    /**
     * 
     * @var BOL_DbCacheTagDao
     */
    private $dbCacheTagDao;

    /**
     * Class instance
     *
     * @var PERFORMANCE_BOL_DbCacheService
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return PERFORMANCE_BOL_DbCacheService
     */
    public static function getInstance() {
        if(!isset(self::$classInstance)) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private function __construct() {
        $this->dbCacheDao = PERFORMANCE_DAO_DbCache::getInstance();
        $this->dbCacheTagDao = PERFORMANCE_DAO_DbCacheTag::getInstance();
    }

    public function get($key) {
        $dto = $this->dbCacheDao->findByName($key);

        return $dto === null ? false : ($dto->getExpireStamp() > time()  ? $dto->value : false);
    }

    public function set($key, $var, $lifeTime = 0) {
        $dto = $this->dbCacheDao->findByName($key);

        if($dto === null) {
            $dto = new PERFORMANCE_DTO_DbCache();
            $dto->name = $key;
        }

        $dto->expireStamp = empty($lifeTime) ? PHP_INT_MAX : time() + $lifeTime;
        $dto->value = $var;

        return $this->dbCacheDao->save($dto);
    }
    
    public function remove($key) {
        $cache = $this->dbCacheDao->findByName($key);
        $this->dbCacheTagDao->deleteByCacheId($cache->getId());
        $this->dbCacheDao->deleteByName($key);
    }

    public function deleteExpiredList() {
        $this->dbCacheDao->deleteExpiredList();
    }
    
    public function setTag($cacheId, $tag) {
        if(!$cacheTag = $this->dbCacheTagDao->findByTag($tag, $cacheId)) {
            $cacheTag = new PERFORMANCE_DTO_DbCacheTag;
            $cacheTag->setTag($tag);
            $cacheTag->setCacheId($cacheId);

            $cacheTag = $this->dbCacheTagDao->save($cacheTag);
        }
        return $cacheTag;
    }
    
    public function clean($tags, $mode) {
        foreach($tags as $tag) {
            $objects = $this->dbCacheTagDao->findByTag($tag);
            foreach($objects as $object) {
                $this->dbCacheDao->deleteById($object->getCacheId());
                $this->dbCacheTagDao->deleteById($object->getId());
            }
        }
    }
}