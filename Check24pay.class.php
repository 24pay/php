<?php
class Check24pay{
  private $mid;
  private $eshopId;

  protected $installUrl = "https://admin.24-pay.eu/pay_gate/install";

  public function __construct($mid, $eshopId){
      $this->mid = $mid;
      $this->eshopId = $eshopId;
  }

  public function makeCall(){
    $data = array(
      'ESHOP_ID'=> $this->eshopId,
      'MID'=> $this->mid,
    );

    return $this->makePostRequest($this->installUrl, $data);
  }

  private function makePostRequest($url, $data) {
    $options = array(
            'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
            )
    );

    // DISABLE CERTIFICATE CHECK
    $options['ssl'] = array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
    );

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result;
  }
}

?>
