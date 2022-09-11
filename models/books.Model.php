<?php

use DBQuery\{FieldFilter, Query, FilterColumn};

class bookModel {
    
    public function getBooks( $id = null){
        global $db;
        
        $row  = $db->prepare("SELECT * FROM books");                

        if ($id) {
            $row  = $db->prepare("SELECT * FROM books where id=:id");                
            $row->bindValue(":id", $id);
            
        }

        $row->execute();
        $result = $row->fetchAll();
        
        return $result;

    }

    public function getName($id){
        global $db;

        $row  = $db->prepare("SELECT title FROM books WHERE id=:id");   
        $row->bindValue(":id", $id);            
        $row->execute();
        $result = $row->fetchAll();
        
        return $result[0]['title'];
    }

    public function getBookInfo( $id = null){
        global $db;
        
        $row  = $db->prepare("SELECT * FROM books LEFT JOIN lend ON books.id = lend.bookid WHERE lend.bookid = :id");                
        $row->bindValue(":id"  , $id, PDO::PARAM_STR);
        $row->execute();

        $result = $row->fetchAll();
        
        return $result;
    }    

    public function countLend($id){
        
        global $db;
        $row  = $db->prepare("SELECT * FROM lend WHERE bookid = :id and status='IN'  ");                
        $row->bindValue(":id"  , $id, PDO::PARAM_STR);
        $row->execute();

        return count($row->fetchAll());

    }

    public function bookStats(){
        $data = array();
        $books = new books;
        $allbooks = $books->getBooks();

        foreach( $allbooks as $item ) {
            $data[] = array("title" => $item['title'], "count" => bookModel::countLend($item['id']) );             
        } 
        return $data;       
    }

    public function createLend($data){
        global $db;
        $s = "INSERT INTO `lend` (`bookid`, `libraryid`, `release_date`) VALUES (:bookid, :libraryid, NOW())";
        $q = $db->prepare($s);
        $q->bindValue(":bookid"  , $data['bookid'], PDO::PARAM_STR);
        $q->bindValue(":libraryid" , $data['libraryid'], PDO::PARAM_STR);                
        $q->execute();

    }

    public function create($data){
        global $db;
        $newId = null;

        $s = "INSERT INTO `books` (`title`, `author`, `published`) VALUES (:title, :author, :published)";
        $q = $db->prepare($s);
        $q->bindValue(":title"  , $data['title'], PDO::PARAM_STR);
        $q->bindValue(":author" , $data['author'], PDO::PARAM_STR);        
        $q->bindValue(":published", $data['published'], PDO::PARAM_INT);
        $q->execute();
        
        $newId = $db->lastInsertId();

        if ( count( $data['library'] ) > 0) {
            foreach($data['library'] as $item) {
                $s = "INSERT INTO `lend` (`bookid`, `libraryid`) VALUES (:bookid, :libraryid)";
                $q = $db->prepare($s);
                $q->bindValue(":bookid"  , $newId, PDO::PARAM_STR);
                $q->bindValue(":libraryid"  , $item, PDO::PARAM_STR);
                $q->execute();
            }
        }

    }
    
}
