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

    function getCookieArray() {
        $cookieDecoded = base64_decode($_COOKIE['pas_auth']);
        $cookieArray = json_decode($cookieDecoded, true);
        return $cookieArray;
    }
?>