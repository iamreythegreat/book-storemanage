<?php
session_start();
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/init.php";
require __DIR__ . "/controllers/user.Controller.php";
require __DIR__ . "/controllers/books.Controller.php";
require __DIR__ . "/controllers/Library.Controller.php";
require __DIR__ . "/models/lend.Model.php";

$id = $_GET['id'];
$library = new library($id);
$lend = new lendModel;

if ($id) {
    $library = new library($id);
    $books = new books($id);

} else {
    header("location: lists-books.php");
}

if ( $_SESSION['active'] ) {

    $templates ="";
        $view = new View(
             __DIR__ . "/views/",
             __DIR__ . "/views/library-info.html",
            [

            ],
            [
                
            ]
            ,            
                false,                        
            [ "library" => $library, "lendsIN" => $lend->booksperLib($id,'IN'), "lendsOUT" => $lend->booksperLib($id,'OUT') ],
            []
            
        );

        $view->render();
        return ;
  }

  header("location: /");

?>
 