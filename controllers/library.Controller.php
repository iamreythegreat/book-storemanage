<?php
require __DIR__ . "/../models/library.model.php";

class library extends controller {
    
    public $id;
    public $libraryname;
    public $limit;

    function __construct($id = null){
        
        if ($id) { 
                
            $this->id = $id;                    
            $library = libraryModel::getLibrary($id,0);            
            $this->libraryname =  $library[0]['name'];
            
        }        

    }

    function getName(){
        return $this->libraryname;
    }

    /*
       countBooks return number of books             
    */    
    function countLibrary(){

        return count(LibraryModel::getLibrary());

    }

    /*
       getBooks info by id if null returns all           
    */    
    function getLibrary(){
        
        $book = new LibraryModel;                
        return $book->getLibrary($this->id,0);

    }


    /*
       
    */        
    function getLibraryBooksInfo(){     
        
        $info = LibraryModel::getbooks($this->id);
        if ( count($info) ) {            
            return $info;
        }

    }

    function getLend(){
        return LibraryModel::countlend();
    }

    function libraryStats(){

        $data = array();        
        $libs = $this->getlibrary(0,4);                              

        foreach($libs as $item){                         
             $count = libraryModel::countlend($item['id']);             
             $data[] = array("library"=>$item['name'],"count" => $count);             
        }
        
        return $data;

    }
    

}

?>