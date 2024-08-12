<?php
namespace F3\Util;

if (!defined('__ROOT__')) {
    define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}

/**
 * Utility class for miscellaneous functions.
 *
 * @author bbischoff
 */
class Util {
    
    public static function getVersion() {
        return '1_1_0';
    }
}
