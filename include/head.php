<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(__FILE__));
}
require_once(__ROOT__ . '/util/Util.php');

use F3\Util\Util;
?>

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">


	<title>F3RVA</title>

	<link href="/css/bootstrap-5.3.3/bootstrap.min.css" rel="stylesheet">
	<link href="/css/f3.css?v=<?= Util::getVersion() ?>" rel="stylesheet">
	<link href="https://cdn.datatables.net/2.1.3/css/dataTables.bootstrap5.css" rel="stylesheet">
 	<link rel="shortcut icon" href="/assets/cropped-f3-150x150.png" type="image/x-icon">
</head>
