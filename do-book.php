<?php
session_start();
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/init.php";
require __DIR__ . "/controllers/books.Controller.php";

$books = new books;

if ( $_POST ) 
    $id = $books->createBook($_POST); 

header("location: book.php?id=".$id);

?>