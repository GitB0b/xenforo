<?php

/**
 * 
 */
abstract class Pfps_Helper_Abstract {

    static private $instances = array();

    public static function getInstance() {
    	
        $class = get_called_class();

        if (empty(self::$instances[$class])) {
        	
            self::$instances[$class] = new $class;
            
        }
        return self::$instances[$class];
    }
}