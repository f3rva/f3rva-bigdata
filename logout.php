<?php

session_start();
unset($_SESSION['loggedin']);
$_SESSION = array();
session_destroy();
header('Location: index.php');
