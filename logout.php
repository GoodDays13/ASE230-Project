<?php
require_once 'authentication.php';
logout();
header('Location: index.php');
exit;
