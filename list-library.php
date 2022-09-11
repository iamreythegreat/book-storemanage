<?php
session_start();
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/init.php";
require __DIR__ . "/controllers/user.Controller.php";
require __DIR__ . "/controllers/library.Controller.php";
require __DIR__ . "/models/lend.Model.php";

$library = new library;
$lend    = new lendModel;

if ( $_SESSION['active'] ) {

    $templates ="";
        $view = new View(
             __DIR__ . "/views/",
             __DIR__ . "/views/library-table.html",
            [

            ],
            [
                "assets/js/jquery-1.12.4.min.js",
            ]
            ,            
                false,                        
            ["books"=>$library->getLibrary(), "lend" => $lend],
            []
            
        );

        $view->render();
        return ;
  }

  header("location: /");

?>
 