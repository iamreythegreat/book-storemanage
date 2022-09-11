<?php

session_start();
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/init.php";
require __DIR__ . "/controllers/user.Controller.php";

$user = new User( $_SESSION['user'] );
$user->logout();

header("location: /");

?>