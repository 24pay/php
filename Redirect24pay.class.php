<?php

class Redirect24pay{
  private $mid;
  private $key;

  public function __construct($mid, $key){
      $this->mid = $mid;
      $this->key = $key;
  }

  public function process(){
    if (filter_input(INPUT_GET, 'Sign')){
      $message = filter_input(INPUT_GET, 'MsTxnId').filter_input(INPUT_GET, 'Amount').filter_input(INPUT_GET, 'CurrCode').filter_input(INPUT_GET, 'Result');
      $signCandidat = $this->computeSign($message, $this->mid, $this->key);
      if  ($signCandidat == filter_input(INPUT_GET, 'Sign'))
        return true;
      else
        return false;
    }
    return true;
  }

  private function computeSign($message, $mid, $key){
    $hash = hash("sha1", $message, true);
    $iv = $mid . strrev($mid);
    $key = pack('H*', $key);

    if ( PHP_VERSION_ID >= 50303 && extension_loaded( 'openssl' ) ) {
            $crypted = openssl_encrypt( $hash, 'AES-256-CBC', $key, 1, $iv );
    } else {
            $crypted = mcrypt_encrypt( MCRYPT_RIJNDAEL_128, $key, $hash, MCRYPT_MODE_CBC, $iv );
    }
    $sign = strtoupper(bin2hex(substr($crypted, 0, 16)));

    return $sign;
  }
}

?>
