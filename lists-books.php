<?php
session_start();
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/init.php";
require __DIR__ . "/controllers/user.Controller.php";
require __DIR__ . "/controllers/books.Controller.php";
require __DIR__ . "/models/lend.Model.php";

$books = new books;

 
if ( $_SESSION['active'] ) {

    $templates ="";
        $view = new View(
             __DIR__ . "/views/",
             __DIR__ . "/views/books-table.html",
            [

            ],
            [
                "assets/js/jquery-1.12.4.min.js",
            ]
            ,            
                false,                        
            ["books"=>$books->getBooks()],
            []
            
        );

        $view->render();
        return ;
  }

  header("location: /");

?>
 