<?php

    require_once 'Signer.php';
    
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
        protected $query = array('body' => '');

        public function __construct() {
            $this->datetime = date("Ymd") . "T" . date("His", time() - 14400) . "Z";
            $this->query['body'] = json_encode(array(
                "Type" => "archive-retrieval",
                "ArchiveId" => 'AAO033XxszSV-cxnSKFrYfm6bbfLE_tbh4qUl4wcTibz5QDjJP40BZhaYT6d8dHhlDd63zcqmlUKWb1vH4zys7vcH24VlGA956Y5URn8IQJm9dMGDTD9whQ5wSjR66Ff-r3ATn5WrA',
                    //"SNSTopic" => "arn:aws:glacier:us-east-1:115024483680:vaults/TestBeckupStorage",  
                    ));
            //$this->query = array('body' => array());
        }

        public function upload() {

            $jsonData = array(
                'Type' => 'inventory-retrieval'
            );

            //$this->query['body'] = $jsonData; // по ходу вся соль сдесь

            $derived_key = $this->get_signed_key();
            $accountID = '115024483680';
            $vaultName = 'TestBeckupStorage';



            $fp = fopen(dirname(__FILE__) . '/errorlog.txt', 'w');

            // $jsonString = json_encode($jsonData);

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => 'http://glacier.us-east-1.amazonaws.com/' . $this->_config['accountID'] . '/vaults/' . $this->_config['vault'] . '/jobs',
                CURLOPT_POST => 1,
                CURLOPT_HTTPHEADER => array(
                    'host: glacier.us-east-1.amazonaws.com',
                    'x-amz-date: ' . $this->datetime,
                    'x-amz-glacier-version: 2012-06-01',
                    'Authorization: AWS4-HMAC-SHA256 Credential=' . $this->_config['keyID'] . '/' . date("Ymd") . '/us-east-1/glacier/aws4_request,SignedHeaders=host;x-amz-date;x-amz-glacier-version,Signature=' . $derived_key . ' ',
                ),
                CURLOPT_POSTFIELDS => $this->query['body'],
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_VERBOSE => 1,
                CURLOPT_STDERR => $fp,
                CURLOPT_HEADER => 1,
            ));
            $data = curl_exec($ch);
            // $info = curl_getinfo($ch);

            curl_close($ch);
            return $data;
        }

        public function get_signed_key($datetime = 0) {
            $signer = new Signer(array(
                'secretAccessKey' => $this->_config['secretKey'],
                'date' => date("Ymd"),
                'region' => 'us-east-1',
                'service' => 'glacier'
            ));
            
            return $signer->sign($this->string_to_sign());
        }

        public function string_to_sign() {
            $parts = array();
            $parts[] = 'AWS4-HMAC-SHA256';
            $parts[] = $this->datetime;
            $parts[] = date("Ymd") . "/us-east-1/glacier/aws4_request";
            $parts[] = bin2hex($this->hash($this->canonical_request()));
            //$parts[] = '5f1da1a2d0feb614dd03d71e87928b8e449ac87614479332aced3a701f916743';
            $this->string_to_sign = implode("\n", $parts);
            return $this->string_to_sign;
        }

        public function credentials($datetime = NULL) {
            
        }

        public function canonical_request() {
            $parts = array();
            $parts['method'] = 'POST';
            $parts['uri'] = $this->canonicalUri();
            $parts['empty'] = '';
            $parts['headers'] = array(
                'host:glacier.us-east-1.amazonaws.com',
                'x-amz-date:' . $this->datetime,
                'x-amz-glacier-version:2012-06-01',
            );
            $parts['empt'] = $parts['empty'];
            $parts['signed_headers'] = implode(';', array(
                'host',
                'x-amz-date',
                'x-amz-glacier-version'
                    ));
            $parts['request_body'] = bin2hex($this->hash($this->canonical_querystring())); // тут пусто тк тело запроса пустое
            $parts['headers'] = implode("\n", $parts['headers']);
            $canonical_request = implode("\n", $parts);

            return $canonical_request;
        }

        public function canonicalUri() {
            //return 'http://glacier.' . $this->_config['region'] . '.amazonaws.com/' . $this->_config['accountID'] . '/vaults/' . $this->_config['vault'] . '/jobs';
            return '/' . $this->_config['accountID'] . '/vaults/' . $this->_config['vault'] . '/jobs';
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

        public function canonical_querystring() {
            if (!isset($this->canonical_querystring)) {
                $this->canonical_querystring = $this->to_signable_string($this->query['body']);
            }

            return $this->canonical_querystring;
        }

        public function to_signable_string($array) {
            $t = array();

    //        foreach ($array as $k => $v) {
    //            $t[] = $this->encode_signature($k) . '=' . $this->encode_signature($v);
    //        }
            //return implode('&', $t);
            return $array;
        }

        public function encode_signature($string) {
            $string = rawurlencode($string);
            return str_replace('%7E', '~', $string);
        }

        protected function hash($string) {
            return hash('sha256', $string, true);
        }

    }

    $uploader = new ArchiveUploader();
    //print_r($uploader->canonical_request());
    print_r($uploader->upload());

