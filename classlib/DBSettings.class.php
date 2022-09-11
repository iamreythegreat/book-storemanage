<?php

class DBSettings {

    public $dbstring;
    public $user;
    public $password;

    public function __construct(
        string $user     = APP\DATABASE::USER,
        string $password = APP\DATABASE::PASSWORD,
        string $driver   = APP\DATABASE::DRIVER,
        string $host     = APP\DATABASE::HOST,
        string $dbname   = APP\DATABASE::DBNAME,
        string $port     = APP\DATABASE::PORT,
        string $charset  = "utf8"
    ) {        

        $this->dbstring = "{$driver}:host={$host};dbname={$dbname};port={$port};charset={$charset}";
        $this->user = $user;
        $this->password = $password;
    }

    public function getUser(){
        return $this->user;
    }

    public function getPass(){
        return $this->password;
    }

    public function getConnectionString() {
        return $this->dbstring;
    }
}

