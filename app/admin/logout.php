<?php
require_once '../../db_config.php';
require_once 'auth.php';

$auth->logout();
header('Location: login.php');
exit();
