<?php
/*
 * @version 1.0
 * @copyright Copyright (C) 2016 ArtMedia. All rights reserved.
 * @website http://artmedia.biz.pl
 * @author Arkadiusz Tobiasz
 * @email kontakt@artmedia.biz.pl
 */

class PERFORMANCE_CLASS_BackendCollector extends BASE_CLASS_EventCollector
{
    public function __construct($name, $params = array()) {
        parent::__construct($name, $params);

        $this->data = array();
    }

    public function add($key, $label = null) {
        if($label === null) {
            $label = $key;
        }
        $this->data[$key] = $label;
    }

    public function setData($data) {
        throw new LogicException("Can't set data in collector event `".$this->getName()."`!");
    }
}