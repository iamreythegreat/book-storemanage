<?php

use DBQuery\{FieldFilter, Query, FilterColumn};

class lendModel {

    public $totalLend;
    public $in_status;
    public $out_status;
    public $bookId;    
    
    function __construct( ){

    }

    function update($id){
        global $db;
        $row  = $db->prepare("UPDATE lend SET status='OUT', return_date=NOW() WHERE id = :id");  
        $row->bindValue(":id", $id);     
        $row->execute();

        return true;
    }

    function countbyLibrary($id, $status=null){
        global $db;
        $row  = $db->prepare("SELECT * FROM lend WHERE libraryid = :libid AND status = :status");  
        $row->bindValue(":libid", $id);     
        $row->bindValue(":status", $status);     
        $row->execute();
        $result = $row->fetchAll();           

        return count($result);
        
    }

    function countbyBooks($id, $status=null){
        global $db;
        $row  = $db->prepare("SELECT * FROM lend WHERE bookid = :bookid AND status = :status");  
        $row->bindValue(":bookid", $id);     
        $row->bindValue(":status", $status);     
        $row->execute();
        $result = $row->fetchAll();           

        return count($result);

    }

    function booksperLib($id,$status=null){

        global $db;
        $data = array();
        if ($status) {
            $row  = $db->prepare("SELECT * FROM lend WHERE libraryid = :libid AND status = :status");  
            $row->bindValue(":status", $status);     
        } else {
            $row  = $db->prepare("SELECT * FROM lend WHERE libraryid = :libid");              
        }        

        $row->bindValue(":libid", $id);     
        $row->execute();
        $result = $row->fetchAll();    

        foreach( $result as $items ){
            $book    = new books($items['bookid']);            
            $library = new library($items['libraryid']);

            $items["bookname"]    = $book->getTitle();
            $items["libraryname"] = $library->getName(1,0);
            $data[] = $items;
            
        }        
        
        return $data;
    }

    function lenders($bookId, $status=null ){
        global $db;
        $data = array();
        if ($status) {
            $row  = $db->prepare("SELECT * FROM lend WHERE bookid = :bookid AND status = :status");  
            $row->bindValue(":status", $status);     
        } else {
            $row  = $db->prepare("SELECT * FROM lend WHERE bookid = :bookid");  
        }

        $row->bindValue(":bookid", $bookId);     
        $row->execute();
        $result = $row->fetchAll();        

        foreach( $result as $items ){
            $book    = new books($items['bookid']);            
            $library = new library($items['libraryid']);

            $items["bookname"]    = $book->getTitle();
            $items["libraryname"] = $library->getName(1,0);
            $data[] = $items;
            
        }

        if ($result) return $data;

    }

    function lendDetails($id){
        global $db;
        $data  = array();

        $row  = $db->prepare("SELECT * FROM lend WHERE id = :id");  
        $row->bindValue(":id", $id);                  
        $row->execute();        

        $result = $row->fetchAll();        

        foreach( $result as $items ){
            $book    = new books($items['bookid']);                                    
            $items["bookname"]    = $book->getTitle();        
            $data[] = $items;
            
        }
        return $data[0];


    }

    function lists(){
        global $db;
        $data  = array();
        $lib   = new libraryMOdel();
        $book  = new bookMOdel();

        $row  = $db->prepare("SELECT * FROM lend ");                
        $row->execute();
        $result = $row->fetchAll();        
        $this->in_status = $row->rowCount();

        foreach($result as $item){
            $data[] = [
                "id"            => $item['id'],
                "bookid"        => $item['bookid'],
                "bookTitle"     => $book->getName($item['bookid']),
                "libraryId"     => $item['libraryid'],
                "libraryName"   => $lib->getName($item['libraryid']),
                "date_return"   => $item['return_date'],
                "status"        => $item['status'],
            ]; 
        }
  
        return $data;

    }

}

?>
