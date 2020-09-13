<?php
    require('include/Utility.php');
    require('include/UserController.php');
    
    /* Response Array */
    $responseArray = array();

    /* User Authentication API */
    if (isset($_POST['validate']) && !empty($_POST['validate'])) {
        switch ($_POST['validate']) {
            case 'email':
                if (isset($_POST['emailID']) && !empty($_POST['emailID'])) {
                    $valid = checkEmail($_POST['emailID']);
                    $responseArray['requestType'] = 'validateEmail';
                    $responseArray['emailID'] = $_POST['emailID'];
                    if ($valid) {
                        $responseArray['result'] = 'valid';
                    } else {
                        $responseArray['result'] = 'invalid';
                    }
                } else {
                    http_response_code(400);
                }
            break;
            case 'password':
                if (isset($_POST['emailID']) && !empty($_POST['emailID']) && isset($_POST['password']) && !empty($_POST['password']) && isset($_POST['rememberMe']) && !empty($_POST['rememberMe'])) {
                    $valid = checkPassword($_POST['emailID'], $_POST['password']);
                    $rememberMe = $_POST['rememberMe'];
                    $responseArray['requestType'] = 'validatePassword';
                    $responseArray['emailID'] = $_POST['emailID'];
                    if ($valid) {

                        // generate new user token
                        $token = generateRandomToken();
                        $cookieInfo['emailID'] = $_POST['emailID'];
                        $cookieInfo['token'] = $token;
                        $cookieInfo['rememberUser'] = $rememberMe;
                        $cookieJSON = json_encode($cookieInfo);
                        $cookieEncoded = base64_encode($cookieJSON);

                        // store the token in DB
                        $result = storeToken($_POST['emailID'], $token);
                        if ($result) {
                            $responseArray['result'] = 'valid';
                            //$responseArray['token'] = $token;
                        } else {
                            $responseArray['result'] = 'cookie_error';
                        }

                        // set the cookie
                        $time = 0;
                        if ($rememberMe == 'true') {
                            $time = time() + (24 * 3600 * 30);
                        }
                        setcookie("pas_auth", $cookieEncoded, $time, "/");
                    } else {
                        $responseArray['result'] = 'invalid';
                        // unset the cookie if it's set
                        setcookie("pas_auth", "", time() - 3600, "/");
                    }
                } else {
                    http_response_code(400);
                }
            break;
            case 'token':
                if (isset($_COOKIE['pas_auth']) && !empty($_COOKIE['pas_auth']) ) {
                    // decode and extract cookie
                    $cookieArray = getCookieArray();
                    $emailID = $cookieArray['emailID'];
                    $token = $cookieArray['token'];
                    $rememberMe = $cookieArray['rememberUser'];

                    $valid = checkToken($emailID, $token);

                    $responseArray['requestType'] = 'validateCookie';
                    $responseArray['emailID'] = $emailID;
                    $responseArray['result'] = $valid;
                    if ($valid == 'valid') {
                        // set the cookie

                        $time = 0;
                        if ($rememberMe == 'true') {
                            $time = time() + (24 * 3600 * 30);
                        }
                        setcookie("pas_auth", $_COOKIE['pas_auth'], $time, "/");
                    }
                } else {
                    http_response_code(400);
                }
            break;
        }
        echo json_encode($responseArray);
    } else {
        http_response_code(400);
    }

    function checkEmail($emailID) {
        $userController = new UserController($emailID);
        return $userController->getUserValidity();
    }

    function checkPassword($emailID, $password) {
        $userController = new UserController($emailID);
        return $userController->getPasswordValidity($password);
    }

    function checkToken($emailID, $token) {
        $userController = new UserController($emailID);
        return $userController->getTokenValidity($token);
    }

    function storeToken($emailID, $token) {
        $userController = new UserController($emailID);
        return $userController->setToken($token);
    }
?>