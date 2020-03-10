<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/config.php');
setcookie("hash", '', time() - 60*60*24*365, '/', $config['domain'], 1, 1);
header('Location: /');