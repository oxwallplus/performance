<?php
/*
 * @version 2.0.0
 * @copyright Copyright (C) 2016 ArtMedia. All rights reserved.
 * @license OSCL, see http://www.oxwall.org/store/oscl
 * @website http://artmedia.biz.pl
 * @author Arkadiusz Tobiasz
 * @email kontakt@artmedia.biz.pl
 */

class PERFORMANCE_DAO_DbCacheTag extends OW_BaseDao
{
    private static $classInstance;

    public static function getInstance() {
        if(self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    protected function __construct() {
        parent::__construct();
    }
    
    public function getDtoClassName() {
        return 'PERFORMANCE_DTO_DbCacheTag';
    }

    public function getTableName() {
        return OW_DB_PREFIX . 'base_cache_tag';
    }
    
    public function deleteByCacheId($cacheId) {
        $example = new OW_Example();
        $example->andFieldEqual('cacheId', $cacheId);

        return $this->deleteByExample($example);
    }
    
    public function findByTag($tag, $cacheId = null) {
        $example = new OW_Example();
        $example->andFieldEqual('tag', $tag);
        if($cacheId) {
            $example->andFieldEqual('cacheId', $cacheId);
        }

        return $this->findListByExample($example);
    }
}