<?php
session_start();
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/init.php";
require __DIR__ . "/controllers/user.Controller.php";
require __DIR__ . "/controllers/books.Controller.php";
require __DIR__ . "/controllers/Library.Controller.php";
require __DIR__ . "/models/lend.Model.php";

$id = $_GET['id'];

 
if ($id) {
    $library = new library();    
    $books = new books($id);
    $info  = $books->getBookInfo();

    

    if ( !$info ) {
        header("location: lists-books.php");
    }

} else {
    header("location: lists-books.php");
}

if ( $_SESSION['active'] ) {

    $templates ="";
        $view = new View(
             __DIR__ . "/views/",
             __DIR__ . "/views/book-lend.html",
            [

            ],
            [
                
            ]
            ,            
                false,                        
            [  "lib" => $library->getLibrary(), "info" => $info , "lendsIN" => lendModel::lenders($id,'IN'), "lendsOUT" => lendModel::lenders($id,'OUT') ],
            []
            
        );

        $view->render();
        return ;
  }

  header("location: /");

?>
 