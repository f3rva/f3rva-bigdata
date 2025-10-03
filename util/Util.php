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

    public static function retrieveAccessControlAllowOriginHeader(): mixed {
        if (!isset($_SERVER['HTTP_ORIGIN'])) {
            return '';
        }
        
        $originHeader = $_SERVER['HTTP_ORIGIN'];
        error_log(message: "Origin header: " . $originHeader);

        $allowedOrigins = array([
            'http://localhost:3000',
            'https://dev.f3rva.org',
            'https://www.dev.f3rva.org',
            'https://f3rva.org'
        ]);

        $accessControlAllowOrigin = in_array(
            needle: $originHeader, 
            haystack: $allowedOrigins) 
            ? $originHeader : '';
        
        return $originHeader;
    }
}
