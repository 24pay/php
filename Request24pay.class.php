<?php

class Request24pay{

  private $mid;
  private $eshopId;
  private $key;

  public function __construct($mid, $eshopId, $key){
      $this->mid      = $mid;
      $this->eshopId  = $eshopId;
      $this->key      = $key;
  }

  // $message = (String) Mid+Amount+CurrencyAlphaCode+MsTxnId+FirstName+FamilyName+Timestamp
  public function sign($message){
        $hash = hash("sha1", $message, true);
      	$iv = $this->mid . strrev($this->mid);

      	$key = pack('H*', $this->key);

      	if ( PHP_VERSION_ID >= 50303 && extension_loaded( 'openssl' ) ) {
      		$crypted = openssl_encrypt( $hash, 'AES-256-CBC', $key, 1, $iv );
      	} else {
      		$crypted = mcrypt_encrypt( MCRYPT_RIJNDAEL_128, $key, $hash, MCRYPT_MODE_CBC, $iv );
      	}

        return strtoupper(bin2hex(substr($crypted, 0, 16)));
  }

}

?>
