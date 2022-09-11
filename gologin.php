<?php 
session_start();
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/init.php";
require __DIR__ . "/controllers/user.Controller.php";
    
$user = new User($_POST['username']);
echo $user->login($_POST['password']);

?>