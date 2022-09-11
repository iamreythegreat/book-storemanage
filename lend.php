<?php
session_start();
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/init.php";
require __DIR__ . "/controllers/user.Controller.php";
require __DIR__ . "/controllers/books.Controller.php";
require __DIR__ . "/controllers/Library.Controller.php";
require __DIR__ . "/controllers/lend.Controller.php";
require __DIR__ . "/models/lend.Model.php";


$books = new books;
$library = new library;

$id = $_GET['id'];
$lendDetails = lendModel::lendDetails($id);

if ($id) {

    $library = new library($lendDetails['libraryid']);
    $books   = new books($id);    


    if ( $_GET['go']==true) {

        $return = new lendController();
        $return->lendReturn($_GET['id']);
        header("location: library.php?id=".$lendDetails["libraryid"]);
        die;        
    }

    $page    = __DIR__ . "/views/lend.return.html";
 
} else {
    $page    = __DIR__ . "/views/books-lend-table.html";
}    

if ( $_SESSION['active'] ) {

    $templates ="";
        $view = new View(
             __DIR__ . "/views/",
             $page,
            [

            ],
            [
                
            ]
            ,            
                false,                        
            [  "books"=>$books->getBooks(), "libraryname" => $library->getName(), "lendDetails"=> $lendDetails ],
            []
            
        );

        $view->render();
        return ;
  }

  header("location: /");

?>
 