<?php
namespace F3;

if (!defined('__ROOT__')) {
  define('__ROOT__', dirname(__FILE__));
}
require_once(__ROOT__ . '/util/Util.php');

use F3\Util\Util;

if (!Util::isLoggedIn()) {
    header('Location: /login.php');
    exit;
}
