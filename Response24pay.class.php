<?php

class Response24pay{

  private $mid;
  private $key;

  public function __construct($mid, $key){
      $this->mid = $mid;
      $this->key = $key;
  }

  public function process(){
    if (filter_input(INPUT_POST, 'params') != false){
      return $this->parparseNotification($params);
    }
    else {
      return false;
    }
  }

  private function parseNotification($params){
    if (get_magic_quotes_gpc())
        $params = stripslashes($params);

    $params = trim(preg_replace("/^\s*<\?xml.*?\?>/i", "", $params));

    $xml = new SimpleXMLElement($params);
    $result = array();

    if ($xml->count()==1){
        $node = $xml[0];
        $attributes = $node->attributes();
        // SIGN
        $result['Sign'] = (string) $attributes["sign"];
        // AMOUNT
        $result['Amount'] = (string) $xml->Transaction->Presentation->Amount;
        // CURRENCY
        $result['Currency'] = (string) $xml->Transaction->Presentation->Currency;
        // PSPTXNID
        $result['PspTxnId'] = $xml->Transaction->Identification->PspTxnId;
        // MSTXNID
        $result['MsTxnId'] = (string) $xml->Transaction->Identification->MsTxnId;
        // TIMESTAMP
        $result['Timestamp'] =  (string) $xml->Transaction->Processing->Timestamp;
        // RESULT
        $result['Result'] = (string) $xml->Transaction->Processing->Result;

        $message = $mid.$result['Amount'].$result['Currency'].$result['PspTxnId'].$result['MsTxnId'].$result['Timestamp'].$result['Result'];

        $signCandidate = $this->computeSign($message, $this->mid, $this->key);
        if ($signCandidate == $result['Sign']){
            $result['Valid'] = true;
        }
        else{
            $result['ValidSign'] = $signCandidate;
            $result['Valid'] = false;
        }
    }
    else{
        $result['Valid'] = false;
    }

    return $result;
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
