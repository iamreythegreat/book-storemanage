<?php
session_start();
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/init.php";
require __DIR__ . "/controllers/user.Controller.php";
require __DIR__ . "/controllers/books.Controller.php";

if ( $_SESSION['active'] ) {

    $templates ="";
        $view = new View(
             __DIR__ . "/views/",
             __DIR__ . "/views/create-book.html",
            [

            ],
            [
                
            ]
            ,            
                false,                        
            [],
            []
            
        );

        $view->render();
        return ;
  }

  header("location: /");

?>
 