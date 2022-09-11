<?php
session_start();
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/init.php";
require __DIR__ . "/controllers/User.Controller.php";

 if ( !isset($_SESSION['active']) ) {
      header("location: login.php");
      return ;
 } else {
      header("location: dashboard.php");
 }    
