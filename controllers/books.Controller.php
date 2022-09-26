<?php

require __DIR__ . "/../models/books.model.php";

class books extends controller {
    
    public $id;
    public $bookinfo;

    function __construct($id = null){        
        if ($id) $this->id = $id;
        $this->bookinfo = $this->getBookInfo();
    }

    /*
       countBooks() return number of books             
    */    
    function countBooks(){
        return count(bookModel::getBooks());
    }

    /*
       getBooks() info by id if null returns all           
    */    
    function getBooks(){            
        $book = new bookModel;    
        return $book->getBooks($this->id);
    }

    function createLend($data){
        
        bookModel::createLend($data);
        return true;

    }

    function cleanBooks(){
        $data = array();
        $books = $this->getBooks();

        foreach($books as $item) {
            $data[] = array("id" => $item['id'], "book" => $item["title"] );
        }

        return $data;
    }

    function fetchIds($datas = array() ){
        $data = array();
        foreach($datas as $item){
            $data[] = $item['id'];
        }
        return $data;
    }
    /*
       getBookInfo() return raw of record
    */    
    function getBookInfo(){
        
        $info = bookModel::getBooks($this->id);
        if ( count($info) ) {            
            return $info[0];
        }

    }

    /*
       getBookLibraryInfo() returns library associated by book           
    */        
    function getBookLibraryInfo(){        
        $info = bookModel::getBookInfo($this->id);
        if ( count($info) ) {            
            return $info;
        }

    }

    /*
      getTitle() returns book title
    */            
    function getTitle(){
        return $this->bookinfo['title'];
    }

    /*
    createBooks() cretate book
    */  
    function createBook(array $data){
        return bookModel::create($data);
    }

    /*
    check_params() returns true if item is in array
    */     
    function check_params($data, $check){
        $valid = false;
        foreach($check as $item){
            foreach($data as $i){
                if ( in_array($i,$check) ) $valid = true;
            }
        }
        return $valid;
    }    

    /*
    get_nonexistence() returns an array of non existing 
    */       
    function get_nonexistence($data, $base) {
        $rdata = array();
        foreach($base as $item){        
            if ( !in_array($item,$data) ) 
            $rdata[] = $item;        
        }
        return $rdata;
    }
    
    /*
    getKeys() returns key of an array
    */    
    function getKeys($post){
        $arr = array();
        foreach($post as $key => $item){
            $arr[] = $key;
        }
        return $arr;
    }

}

?>