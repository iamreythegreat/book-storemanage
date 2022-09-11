<?php
session_start();
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/init.php";
require __DIR__ . "/controllers/user.Controller.php";

if ( !isset($_SESSION['active']) ) {

    $templates ="";
        $view = new View(
             __DIR__ . "/views/",
             __DIR__ . "/views/login.html",
            [
                "/css1/style.css",          
                "assets/css1/bootstrap.min.css",   
                "assets/css1/font-awesome.min.css",
                "assets/css1/form/all-type-forms.css",
                "assets/css1/style.css",
                "assets/css1/responsive.css",
            ],
            [
                "assets/js/jquery-1.12.4.min.js",
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
 