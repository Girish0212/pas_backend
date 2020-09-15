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
?>