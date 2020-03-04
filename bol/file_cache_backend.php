<?php
/*
 * @version 1.0
 * @copyright Copyright (C) 2016 ArtMedia. All rights reserved.
 * @license OSCL, see http://www.oxwall.org/store/oscl
 * @website http://artmedia.biz.pl
 * @author Arkadiusz Tobiasz
 * @email kontakt@artmedia.biz.pl
 */


class PERFORMANCE_BOL_FileCacheBackend implements OW_ICacheBackend
{
    const CACHE_TIME = 86400;
    
    private $path;
    
    private static $classInstance;
    
    public static function getInstance() {
        if(self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    protected function __construct() {
        $this->path = OW::getPluginManager()->getPlugin(PERFORMANCE_BOL_Service::KEY)->getPluginFilesDir() . 'cache' . DS;
        if(!file_exists($this->path)) {
            mkdir($this->path);
            chmod($this->path, 0777);
        }
    }
    
    public function save($data, $key, array $tags = array(), $expTime) {
        if(!$expTime) {
            $expTime = self::CACHE_TIME;
        }
        
        $filename = $this->path . $key;
        if(file_exists($filename)) {
            unlink($filename);
        }
        
        $expTime += time();
        file_put_contents($filename, $expTime.'|data|'.$data, LOCK_EX);
        
        foreach($tags as $tag) {
            file_put_contents($this->path . $tag, $key."\n", FILE_APPEND | LOCK_EX);
        }
    }
    
    public function load($key) {
        $filename = $this->path . $key;
        if(file_exists($filename)) {
            $file = explode('|data|', file_get_contents($filename));
            $expireTime = $file[0];
            $content = $file[1];
            if($expireTime > time()) {
                return $content;
            } else {
                unlink($filename);
            }
        } else {
            return false;
        }
    }
    
    public function test($key) {
        $this->load($key);
    }

    public function remove($key) {
        $filename = $this->path . $key;
        if(file_exists($filename)) {
            unlink($filename);
        }
    }
    
    public function clean(array $tags, $mode = null) {
        foreach($tags as $tag) {
            if(file_exists($this->path . $tag)) {
                $handle = fopen($this->path . $tag, "r");
                if($handle){
                    while (($file = fgets($handle)) !== false) {
                        $file = trim($file);
                        if(file_exists($this->path . $file)) {
                            unlink($this->path . $file);
                        }
                    }
                    fclose($handle);
                }
            }
        }
    }
}
