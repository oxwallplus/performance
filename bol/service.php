<?php
/*
 * @version 2.0.0
 * @copyright Copyright (C) 2016 ArtMedia. All rights reserved.
 * @license OSCL, see http://www.oxwallplus.com/oscl
 * @website http://artmedia.biz.pl
 * @author Arkadiusz Tobiasz
 * @email kontakt@artmedia.biz.pl
 */

use MatthiasMullie\Minify;

class PERFORMANCE_BOL_Service
{
    const KEY = 'performance';
    
    const EVENT_BACKENDS_COLLECTOR = 'base.cache_backend_init';
        
    private static $classInstance;
    
    public static function getInstance() {
        if(self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() {
    }
    
    public function setCache() {
        if(!OW_DEV_MODE) {
            if(OW::getConfig()->getValue(self::KEY, 'cache', false)) {
                $backend = $this->getBackend();
                OW::getCacheManager()->setCacheBackend($backend);
                OW::getCacheManager()->setLifetime(3600);
                OW::getDbo()->setUseCashe(true);
            }
        }
    }
    
    public function getBackends() {
        $language = OW::getLanguage();
        $defaultBackends = array(
            'PERFORMANCE_BOL_FileCacheBackend' => $language->text(self::KEY, 'file_cache'),
            'PERFORMANCE_BOL_DbCacheBackend' => $language->text(self::KEY, 'db_cache')
        );
        
        $eventManager = OW::getEventManager();
        $event = new PERFORMANCE_CLASS_BackendCollector(self::EVENT_BACKENDS_COLLECTOR);
        $eventManager->trigger($event);
        $defaultBackends = array_merge($defaultBackends, $event->getData());
        return $defaultBackends;
    }
    
    public function getBackend() {
        $backend = OW::getConfig()->getValue(self::KEY, 'cacheType', false);
        if(class_exists($backend)) {
            return $backend::getInstance();
        }
        switch ($type) {
            case 2:
                return PERFORMANCE_BOL_FileCacheBackend::getInstance();
            break;
            default:
                return PERFORMANCE_BOL_DbCacheBackend::getInstance();
        }
    }
    
    public function processPage() {
        $document = OW::getDocument();
        $config = OW::getConfig();
        $admin = OW::getDocument()->getMasterPage() instanceof ADMIN_CLASS_MasterPage;
        $cssCompress = $config->getValue(self::KEY, 'compressCss');
        $jsCompress = $config->getValue(self::KEY, 'compressJs');
        $saveCompressedFiles = $config->getValue(self::KEY, 'saveCompressedFiles');        
        
        if($cssCompress) {
            $cssPath = OW::getPluginManager()->getPlugin(self::KEY)->getStaticDir() . 'css' . DS;
            $cssUrl = OW::getPluginManager()->getPlugin(self::KEY)->getStaticCssUrl();
            $cssFiles = $this->getCssFiles($document->getStyleSheets());
            $compressedCssFiles = $config->getValue(self::KEY, 'compressedCssFiles');
            if($compressedCssFiles) {
                $compressedCssFiles = unserialize($compressedCssFiles);
                if(!is_array($compressedCssFiles)) {
                    $compressedCssFiles = array();
                }
            } else {
                $compressedCssFiles = array('admin' => '', 'front' => '');
            }
            $rebuild = true;

            if($admin) {
                $check = isset($compressedCssFiles['admin']) ? $compressedCssFiles['admin'] : null;
                $cssFilename = 'admin.min.css';
            } else {
                $check = isset($compressedCssFiles['front']) ? $compressedCssFiles['front'] : null;
                $cssFilename = 'styles.min.css';
            }
            $document->setStyleSheets(array("added" => array(), "items" => array()));
            if($saveCompressedFiles) {
                if($check == md5(serialize($cssFiles))) {
                    $cssFile = $cssPath . $cssFilename;
                    if(file_exists($cssFile)) {
                        $document->addStyleSheet($cssUrl . $cssFilename);
                        $rebuild = false;
                    }
                }
            }
                        
            if($rebuild) {
                $static = OW::getPluginManager()->getPlugin(self::KEY)->getPublicStaticDir() . 'css' . DS;
                $minifier = new Minify\CSS();
                foreach($cssFiles as $file) {
                    $minifier->add(OW_DIR_ROOT . $file);
                }
                if($admin) {
                    $compressedCssFiles['admin'] = md5(serialize($cssFiles));
                } else {
                    $compressedCssFiles['front'] = md5(serialize($cssFiles));
                }
                $config->saveConfig(self::KEY, 'compressedCssFiles', serialize($compressedCssFiles));
                $minified = $minifier->minify();
                file_put_contents($cssPath . $cssFilename, $minified);
                file_put_contents($static . $cssFilename, $minified);
                $document->addStyleSheet($cssUrl . $cssFilename);
            }
            
            $styleDeclarations = $document->getStyleDeclarations();
            if(isset($styleDeclarations['items'])) {
                $minifier = new Minify\CSS();
                ksort($styleDeclarations['items']);
                $document->setStyleDeclarations(array("hash" => array(), "items" => array()));
                foreach($styleDeclarations["items"] as $item) {
                    foreach($item as $order => $styles) {
                        foreach($styles as $type => $style) {
                            $minifier->add($style);
                        }
                    }
                }
                $document->addStyleDeclaration($minifier->minify());
            }
        }
        
        if($jsCompress) {
            $jsPath = OW::getPluginManager()->getPlugin(self::KEY)->getStaticDir() . 'js' . DS;
            $jsUrl = OW::getPluginManager()->getPlugin(self::KEY)->getStaticJsUrl();
            $jsFiles = $this->getJsFiles($document->getJavaScripts());
            $compressedJsFiles = $config->getValue(self::KEY, 'compressedJsFiles');
            if($compressedJsFiles) {
                $compressedJsFiles = unserialize($compressedJsFiles);
            } else {
                $compressedJsFiles = array('admin' => '', 'front' => '');
            }
            $document->setJavaScripts(array("added" => array(), "items" => array()));
            $rebuild = true;
            
            if($admin) {
                $check = isset($compressedJsFiles['admin']) ? $compressedJsFiles['admin'] : null;
                $jsFilename = 'admin.min.js';
            } else {
                $check = isset($compressedJsFiles['front']) ? $compressedJsFiles['front'] : null;
                $jsFilename = 'scripts.min.js';
            }
            
            if($saveCompressedFiles) {
                if($check == md5(serialize($jsFiles))) {
                    $jsFile = $jsPath . $jsFilename;
                    if(file_exists($jsFile)) {
                        $document->addScript($jsUrl . $jsFilename);
                        $rebuild = false;
                    }
                }
            }
            
            if($rebuild) {
                $minifier = new Minify\JS();
                $document->setJavaScripts(array("added" => array(), "items" => array()));
                $static = OW::getPluginManager()->getPlugin(self::KEY)->getPublicStaticDir() . 'js' . DS;
                
                foreach($jsFiles as $file) {
                    $minifier->add(OW_DIR_ROOT . $file);
                }
                
                if($admin) {
                    $compressedJsFiles['admin'] = md5(serialize($jsFiles));
                } else {
                    $compressedJsFiles['front'] = md5(serialize($jsFiles));
                }
                $config->saveConfig(self::KEY, 'compressedJsFiles', serialize($compressedJsFiles));
                $minified = $minifier->minify();
                file_put_contents($jsPath . $jsFilename, $minified);
                file_put_contents($static . $jsFilename, $minified);
                $document->addScript($jsUrl . $jsFilename);
            }
            
            $jsDeclarations = $document->getScriptDeclarations();
            if(isset($jsDeclarations['items'])) {
                $minifier = new Minify\JS();
                ksort($jsDeclarations['items']);
                $document->setScriptDeclarations(array("hash" => array(), "items" => array()));
                foreach($jsDeclarations["items"] as $item) {
                    foreach($item as $order => $scripts) {
                        foreach($scripts as $type => $script) {
                            $minifier->add($script);
                        }
                    }
                }
                $document->addScriptDeclaration($minifier->minify());
            }
            
            $onloadScripts = $document->getOnloadScripts();
            if(isset($onloadScripts['items'])) {
                $minifier = new Minify\JS();
                ksort($onloadScripts['items']);
                $document->setOnloadScripts(array("hash" => array(), "items" => array()));
                foreach($onloadScripts["items"] as $item) {
                    foreach($item as $script) {
                        $minifier->add($script);
                    }
                }
                $document->addOnloadScript($minifier->minify());
            }
            
            $preincludeScripts = $document->getScriptsDeclarationsBeforeIncludes();
            if(isset($preincludeScripts['items'])) {
                $minifier = new Minify\JS();
                ksort($preincludeScripts['items']);
                $document->setScriptsDeclarationsBeforeIncludes(array("hash" => array(), "items" => array()));
                foreach($preincludeScripts["items"] as $item) {
                    foreach($item as $script) {
                        $minifier->add($script);
                    }
                }
                $document->addScriptDeclarationBeforeIncludes($minifier->minify());
            }
        } 
    }
    
    protected function getCssFiles($cssData) {
        $files = array();
        ksort($cssData["items"]);
        
        foreach($cssData["items"] as $item) {
            foreach($item as $order => $styles) {
                foreach($styles as $type => $style) {
                    if(strstr($style, "?")) {
                        $style = substr($style, 0, strpos($style, "?"));
                    }
                    if(UTIL_File::getExtension($style) == "css" && strstr($style, OW_URL_HOME)) {
                        $files[] = str_replace(OW_URL_HOME, '', $style);
                    }
                }
            }
        }
        
        return $files;
    }
    
    protected function getJsFiles($jsData) {
        $files = array();
        ksort($jsData["items"]);
        foreach($jsData["items"] as $item) {
            foreach($item as $order => $javascripts) {
                foreach($javascripts as $type => $javascript) {
                    if(strstr($javascript, "?")) {
                        $javascript = substr($javascript, 0, strpos($javascript, "?"));
                    }
                    if(UTIL_File::getExtension($javascript) == "js" && strstr($javascript, OW_URL_HOME)) {
                        $files[] = str_replace(OW_URL_HOME, '', $javascript);
                    }
                }
            } 
        }
        
        return $files;
    }
    
    public function removeFiles() {
        $cssPath = OW::getPluginManager()->getPlugin(self::KEY)->getStaticDir() . 'css' . DS;
        $jsPath = OW::getPluginManager()->getPlugin(self::KEY)->getStaticDir() . 'js' . DS;

        $files = array(
            $jsPath . 'admin.min.js',
            $jsPath . 'scripts.min.js',
            $cssPath . 'admin.min.css',
            $cssPath . 'styles.min.css',
        );
        
        foreach($files as $file) {
            if(file_exists($file)) {
                unlink($file);
            }
        }
    }
    
    public function avaibleGzip() {
        $headers = $_SERVER;
        $config = OW::getConfig();
        if(empty($headers["HTTP_ACCEPT_ENCODING"]) || mb_strpos($headers["HTTP_ACCEPT_ENCODING"], "gzip") === false ) {
            return false;
        }
        
        if(!$config->getValue(self::KEY, 'gzip')) {
            return false;
        }

        if((ini_get("zlib.output_compression") == "On" || ini_get("zlib.output_compression_level") > 0 ) || ini_get("output_handler") == "ob_gzhandler") {
            return false;
        }

        if(function_exists("apache_get_modules")) {
            $modules = apache_get_modules();
            foreach($modules as $module) {
                if(mb_strstr($module, "deflate")) {
                    return false;
                }
            }
        }

        return true;
    }
}