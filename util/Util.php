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
    
    const SESSION_TOKEN = 'loggedIn';

    public static function getVersion(): string {
        return '1_1_0';
    }

    public static function isLoggedIn(): bool {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION[Util::SESSION_TOKEN]) && $_SESSION[Util::SESSION_TOKEN] == true;
    }
}
