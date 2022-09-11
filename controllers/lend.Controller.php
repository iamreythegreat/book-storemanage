<?php
 
class lendController extends controller {
    
    public $id;
 
    function __construct($id = null){
              

    }

    public function lendReturn($id){

        lendModel::update($id,true);
        
    }        
}

?>