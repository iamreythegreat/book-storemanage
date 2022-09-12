<?php 
session_start();
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/init.php";
require __DIR__ . "/controllers/user.Controller.php";   
if ( $_POST['username'] && $_POST['username'] ) { 
        $user = new User($_POST['username']);
        
        if ( $user->login($_POST['password']) == 1 ) header("location: /dashboard.php");

}
header("location: /")

?>