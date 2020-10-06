<?php

    set_exception_handler('handleUnhandledError');

    function handleUnhandledError($exception) {
        die("<b>ERROR: </b>" . $exception->getMessage() . " on <b>line " . $exception->getLine() . "</b> in file <b>" . $exception->getFile() . "</b>");
    }

    function generateRandomToken($cstrong = true) {
        $token = openssl_random_pseudo_bytes(16, $cstrong);
        $token = bin2hex($token);
        return $token;
    }

    function generateActivationCode($cstrong = true) {
        $aCode = openssl_random_pseudo_bytes(8, $cstrong);
        $aCode = bin2hex($aCode);
        return $aCode;
    }

    function getCookieArray() {
        $cookieDecoded = base64_decode($_COOKIE['pas_auth']);
        $cookieArray = json_decode($cookieDecoded, true);
        return $cookieArray;
    }

    function getBackendScriptDir() {
        $url = $_SERVER['REQUEST_URI']; //returns the current URL
        $parts = explode('/',$url);
        $dir = $_SERVER['SERVER_NAME'];
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $dir .= $parts[$i] . "/";
        }
        return $dir;
    }

    define('FRONTEND_DIR', 'PassportAutomationSystem/');

    function getFrontendScriptDir() {
        $url = $_SERVER['REQUEST_URI']; //returns the current URL
        $parts = explode('/',$url);
        $dir = $_SERVER['SERVER_NAME'];
        for ($i = 0; $i < count($parts) - 2; $i++) {
            $dir .= $parts[$i] . "/";
        }
        return $dir . FRONTEND_DIR;
    }
 
    function generate_string($strength = 16) {
        $permitted_chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $input_length = strlen($permitted_chars);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $permitted_chars[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
    
        return $random_string;
    }
?>