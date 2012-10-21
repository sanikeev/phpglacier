<?php

    require_once '../lib/Signer.php';
    
    class SignerTest extends PHPUnit_Framework_TestCase
    {
        public function setUp()
        {
            $this->signer = new Signer(array(
                'secretAccessKey' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
                'date' => '20120525',
                'region' => 'us-east-1',
                'service' => 'glacier'
            ));
        }
        
        public function testSign()
        {
            $signature = $this->signer->sign(
                "AWS4-HMAC-SHA256\n" .
                "20120525T002453Z\n" .
                "20120525/us-east-1/glacier/aws4_request\n" .
                "5f1da1a2d0feb614dd03d71e87928b8e449ac87614479332aced3a701f916743");
            
            $this->assertEquals($signature, '3ce5b2f2fffac9262b4da9256f8d086b4aaf42eba5f111c21681a65a127b7c2a');
        }
    }
