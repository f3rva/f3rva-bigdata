<?php
namespace F3;

if (!defined('__ROOT__')) {
  define('__ROOT__', dirname(__FILE__));
}
require_once(__ROOT__ . '/settings.php');
require_once(__ROOT__ . '/util/Util.php');

use F3\Settings;
use F3\Util\Util;

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  if ($username === Settings::ADMIN_USERNAME && $password === Settings::ADMIN_PASSWORD) {
    $_SESSION[Util::SESSION_TOKEN] = true;
    header('Location: index.php');
    exit;
  }
  else {
    $error = 'Invalid username or password';
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<? include __ROOT__ . '/include/head.php'; ?>

<body>
  <? include __ROOT__ . '/include/analytics.php'; ?>
  <? include __ROOT__ . '/include/nav.php'; ?>

  <script src="/js/jquery-3.7.1/jquery-3.7.1.min.js"></script>
  <script src="/js/bootstrap-5.3.3/bootstrap.bundle.min.js"></script>

  <div class="container">
    <div class="row">
      <div class="col-md-6 offset-md-3">
      <?
        if (isset($error)) {
      ?>
        <div class="alert alert-danger my-5" role="alert" id="loginAlert">
          <?= $error ?>
        </div>
      <?
        }
      ?>
        <div class="card my-5">
          <form method="post" action="login.php" class="card-body cardbody-color p-lg-5">
            <div class="mb-3">
              <input type="text" name="username" class="form-control" id="username" placeholder="Username" />
            </div>
            <div class="mb-3">
              <input type="password" name="password" class="form-control" id="password" placeholder="Password" />
            </div>
            <div class="text-center">
              <button type="submit" class="btn btn-color px-5 mt-4 w-100">Login</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
