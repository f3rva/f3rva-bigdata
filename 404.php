<?php
namespace F3;

if (!defined('__ROOT__')) {
  define('__ROOT__', dirname(__FILE__));
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
      <div class="col-md-6 offset-md-3 mt-5">
        <h1 class="py-5">Man Left Behind</h1>
        <p>You are lost.  Never fear, the PAX will come back for you.  If you would rather not wait, go <a href="/">home</a>.</p>
      </div>
    </div>
  </div>
</body>
</html>
