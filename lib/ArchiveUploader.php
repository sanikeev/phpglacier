<?php

class ArchiveUploader {

    protected $_config = array(
        'secretKey' => 'tz4oex8nUAiCR747HkZ1T67E1Nlj1Is1cgHQU2ba',
        'keyID' => 'AKIAIIS2MUUD7VC7BANA',
        'accountID' => '115024483680',
        'host' => 'glacier.us-east-1.amazonaws.com',
        'region' => 'us-east-1',
        'service' => 'glacier',
        'request' => 'aws4_request',
        'vault' => 'TestBeckupStorage',
        'datetime'
    );

    public function __construct() {
        $this->datetime = date("Ymd") . "T" . date("His", time() - 14400) . "Z";
    }

    public function upload() {

        $credential = '';
        $keyID = 'AKIAIIS2MUUD7VC7BANA';
        $secretKey = '';


//        $derived_key = $this->get_signed_key(0);
//        $accountID = '115024483680';
//        $vaultName = 'TestBeckupStorage';
//        $jsonData = array(
//            'Type' => 'inventory-retrieval'
//        );
//
//        $jsonString = json_encode($jsonData);
//
//        $ch = curl_init();
//        curl_setopt_array($ch, array(
//            CURLOPT_URL => 'http://glacier.us-east-1.amazonaws.com/' . $accountID . '/vaults/' . $vaultName . '/jobs',
//            CURLOPT_POST => 1,
//            CURLOPT_HTTPHEADER => array(
//                'host: glacier.us-east-1.amazonaws.com',
//                'date:' . $current_time,
//                'authorization: AWS4-HMAC-SHA256 Credential=' . $keyID . '/' . date("Ymd") . '/us-east-1/glacier/aws4_request,SignedHeaders=host;x-amz-date;x-amz-glacier-version,Signature=' . $derived_key . ' ',
//                'x-amz-glacier-version: 2012-06-01',
//            ),
//            CURLOPT_POSTFIELDS => $jsonString,
//        ));
//        $data = curl_exec($ch);
//        // $info = curl_getinfo($ch);
//        curl_close($ch);
//        return $data;
    }

    public function get_signed_key($datetime = 0) {
        //$secretKey = 'tz4oex8nUAiCR747HkZ1T67E1Nlj1Is1cgHQU2ba';
        $skey = 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY';

        $k_date = $this->hmac('AWS4' . $skey, "20120525"/* substr($datetime, 0, 8) */);
        $k_region = $this->hmac($k_date, "us-east-1");
        $k_service = $this->hmac($k_region, "glacier");
        $k_credentials = $this->hmac($k_service, 'aws4_request');
        $signature = $this->hmac($k_credentials, $this->string_to_sign("20120525"));

        return $signature;
    }

    public function string_to_sign() {
        $parts = array();
        $parts[] = 'AWS4-HMAC-SHA256';
        $parts[] = $this->datetime;
        $parts[] = "20120525/us-east-1/glacier/aws4_request";
        $parts[] = $this->hex16($this->hash($this->canonical_request()));

        $this->string_to_sign = implode("\n", $parts);

        return $this->string_to_sign;
    }

    public function credentials($datetime = NULL) {
        
    }

    public function canonical_request() {
        $parts = array();
        $parts['method'] = 'POST';
        $parts['uri'] = $this->canonicalUri();
        $parts['headers'] = array(
            'host: glacier.us-east-1.amazonaws.com',
            'date:' . $this->datetime,
            'x-amz-glacier-version: 2012-06-01',
        );
        $parts['signed_headers'] = implode(';', array(
            'host',
            'x-amz-date',
            'x-amz-glacier-version'
        ));
        $parts['request_body'] = $this->hex16($this->hash("")); // тут пусто тк тело запроса пустое
        $parts['headers'] = implode("\n", $parts['headers']);
        $canonical_request = implode("\n", $parts);

        return $canonical_request;
    }

    public function canonicalUri() {
        return 'http://glacier.' . $this->_config['region'] . '.amazonaws.com/' . $this->_config['accountID'] . '/vaults/' . $this->_config['vault'] . '/jobs';
    }

    public function signed_headers() {
        $arrHeaders = array(
            'host',
            'x-amz-date',
            'x-amz-glacier-version'
        );
        
        $signed_headers = implode(";", $arrHeaders);
        return 'SignedHeaders=' . $signed_headers;
    }

    protected function hmac($key, $string) {

        return hash_hmac('sha256', $string, $key, TRUE);
    }

    protected function hex16($value) {
        $result = unpack('H*', $value);
        return reset($result);
    }

    protected function hash($string) {
        return hash('sha256', $string, true);
    }

    private function strToHex($string) {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }

}

$uploader = new ArchiveUploader();
//print_r($uploader->upload());
print_r($uploader->canonical_request());
?>
