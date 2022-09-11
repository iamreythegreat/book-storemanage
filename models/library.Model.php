<?php

use DBQuery\{FieldFilter, Query, FilterColumn};

class libraryModel {

    public $libraryname;
    public $limit;

    function __construct( ){

    }

    public function setLimit($limit){
         $this->limit = $limit;
    }

    public function getName($id){
        global $db;

        $row  = $db->prepare("SELECT name FROM library WHERE id=:id");   
        $row->bindValue(":id", $id);            
        $row->execute();
        $result = $row->fetchAll();
        
        return $result[0]['name'];
    }

    public function getLibrary( $id = null, $limit){
        global $db;
        $limit = 4;

        $row  = $db->prepare("SELECT * FROM library");                
    
        if ($id) {            
            $row  = $db->prepare("SELECT * FROM library where id=:id ");                
            $row->bindValue(":id", $id);            
        }

        // if ( $limit >0 ) {

        //     $db->limit_start  = 1;                    
        //     $db->limit_length = 4;               
        //     $row  = $db->query("SELECT * FROM library");                                                
        //     $row->bindValue(":limit", $limit);   

        // }

        $row->execute();
        $result = $row->fetchAll();
        
        return $result;

    }

    function getbooks($id = mull){

        global $db;
        $bookids = array();

        $row  = $db->prepare("SELECT bookid FROM library LEFT JOIN lend ON library.id = lend.libraryid WHERE lend.libraryid = :id");                
        $row->bindValue(":id"  , $id, PDO::PARAM_STR);
        $row->execute();

        $result = $row->fetchAll();        

        foreach($result as $item ){
            $bookids[] = bookModel::getBookInfo($item['bookid']);
        }
        return $bookids;

    }

    function countlend($id = null){

        global $db; 
        
        $q  = $db->prepare("SELECT * FROM lend");   
        if ($id) {

            $q  = $db->prepare("SELECT * FROM lend where libraryid=:id AND status='IN' ");                
            $q->bindValue(":id", $id);
            
        }        
            
        $q->execute();
        $rows = $q->fetchAll();  
        
        return count($rows);

    }
    
}
