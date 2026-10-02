<?php
session_start();
session_destroy();
header('Location: ' . (defined('APP_URL') ? APP_URL : '') . 'http://localhost/gestion_archivage/login.php');
exit;
