<?php
session_start();
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/init.php";
require __DIR__ . "/controllers/user.Controller.php";
require __DIR__ . "/controllers/books.Controller.php";
require __DIR__ . "/controllers/library.Controller.php";

if ( !isset($_SESSION['active']) ) {
    header("location: /");
}

        $countBooks = books::countBooks();        
        $lib   = new library();
        $clib  = count($lib->getlibrary());
        $clend = $lib->getLend();
                         
        $view = new View(
             __DIR__ . "/views/",
             __DIR__ . "/views/dashboard.html",
            [
                
            ], 
            [

            ],            
            true,
            [ "count_books" => $countBooks, "count_library" => $clib, "count_lend" => $clend, "librarystats" => $lib->libraryStats(), "bookstats" => bookModel::bookstats() ],                    
            [],
            []
            
        );

        $view->render();


 


