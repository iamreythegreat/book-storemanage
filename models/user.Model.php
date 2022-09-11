<?php

use DBQuery\{FieldFilter, Query, FilterColumn};

class UserModel {

    public function getPassword($user){
        global $db;

        $row  = $db->prepare("SELECT * FROM users WHERE user = :user");
        $row->bindValue(":user", $user);
        $row->execute();
        $result = $row->fetch();
        
        return $result['pass'];

    }

    public static function GeneratePassword() {
        $length = 8;
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ?!@#$%^&*()-=_+[]{};:",./';
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }
}
