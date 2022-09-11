<?php
session_start();
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/init.php";
require __DIR__ . "/controllers/user.Controller.php";
require __DIR__ . "/controllers/library.Controller.php";
require __DIR__ . "/models/books.Model.php";
require __DIR__ . "/models/lend.Model.php";


$lib  = new lendModel;
 
if ( $_SESSION['active'] ) {

    $templates ="";
        $view = new View(
             __DIR__ . "/views/",
             __DIR__ . "/views/lend-table.html",
            [

            ],
            [
                "assets/js/jquery-1.12.4.min.js",
            ]
            ,            
                false,                        
            ["lend"=> $lib->lists() ],
            []
            
        );

        $view->render();
        return ;
  }

  header("location: /");

?>
 