<?php
require_once 'system.php';
global $admin;
$admin->logout();
header("location:login.php");
