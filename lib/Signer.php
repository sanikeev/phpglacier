<?php

    /*
    *   This class implements AWS signing.
    *   For details see http://docs.amazonwebservices.com/general/latest/gr/sigv4-calculate-signature.html
    */
    class Signer{

        /*
        *   Constructs signer by values.
        */
        function __construct($options = array())
        {
            foreach($options as $key => $value)
            {
                $this->$key = $value;
            }
            
        }
        
        /*
        *   Signs a data by the derived key. 
        */
        public function sign($data)
        {
            return bin2hex($this->hmac( $this->getDerivedKey(), $data));
        }
        
        /*
        *   Calculates the derived key.
        */
        private function getDerivedKey()
        {
            $kSecret = $this->secretAccessKey;
            $kDate = $this->hmac("AWS4" . $kSecret, $this->date);
            $kRegion = $this->hmac($kDate, $this->region);
            $kService = $this->hmac($kRegion, $this->service);
            $kSigning = $this->hmac($kService, "aws4_request");
            
            return $kSigning;
        }
        
        /*
        * Represents an HMAC-SHA256 function that returns output in binary format.
        */
        private function hmac($key, $data)
        {
            $result = hash_hmac('sha256', $data, $key, true);
            return $result;
        }
    }
