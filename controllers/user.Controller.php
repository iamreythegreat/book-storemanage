<?php

require __DIR__ . "/../models/User.model.php";
class user extends Controller {

        public $login;
        public $pass;

        function __Construct($login){
            $this->login = $login;
        }

        function hasAccess(){
            return UserModel::GeneratePassword();;
        }

        function login($password){

            $this->pass = $password;
            return $this->checkAccess();

        }

        function logout(){

            if (isset($_SESSION['active'])) unset($_SESSION['active']);

        }

        function checkAccess(){

            $valid = false;
            $pullPassword = UserModel::getPassword($this->login);            
          
            if ( $this->encrypt($this->pass) == $pullPassword ) { 

                $_SESSION['user']       = $this->login;                
                $_SESSION['active']     = true;
                return true;
            }          
            return $valid;

        }

        function check(){

            if ( isset($_SESSION['active'])) {
                return true;
            }

        }

        function encrypt ($string) {
            
            $encryption_key = APP\SECURITY::KEY;            
            $ciphering = "AES-128-CTR";
            $iv_length = openssl_cipher_iv_length($ciphering);
            $options = 0;
            $encryption_iv = '1234567891011121';

            return openssl_encrypt($string, $ciphering,
            $encryption_key, $options, $encryption_iv);

             
        }        

        function decrypt ($string) {

            $decryption_key = APP\SECURITY::KEY;            
            $ciphering = "AES-128-CTR";
            $iv_length = openssl_cipher_iv_length($ciphering);
            $options = 0;
            $decryption_iv = '1234567891011121';

            return $decryption=openssl_decrypt ($string, $ciphering, 
                $decryption_key, $options, $decryption_iv);
        }        
        
}

?>