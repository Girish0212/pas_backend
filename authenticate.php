<?php
    require('include/Utility.php');
    require('include/UserController.php');
    
    /* Response Array */
    $responseArray = array();

    /* User Authentication API */
    if (isset($_POST['validate']) && !empty($_POST['validate']) && isset($_POST['role']) && !empty($_POST['role'])) {
        switch ($_POST['validate']) {
            case 'email':
                if (isset($_POST['emailID']) && !empty($_POST['emailID'])) {
                    $valid = checkEmail($_POST['emailID'], $_POST['role']);
                    $responseArray['requestType'] = 'validateEmail';
                    $responseArray['emailID'] = $_POST['emailID'];
                    if ($valid) {
                        $active = checkAccountActivation($_POST['emailID'], $_POST['role']);
                        if ($active) {
                            $responseArray['result'] = 'valid';
                        } else {
                            $responseArray['result'] = 'notactive';
                        }
                    } else {
                        $responseArray['result'] = 'invalid';
                    }
                } else {
                    http_response_code(400);
                }
            break;
            case 'password':
                if (isset($_POST['emailID']) && !empty($_POST['emailID']) && isset($_POST['password']) && !empty($_POST['password']) && isset($_POST['rememberMe']) && !empty($_POST['rememberMe'])) {
                    $valid = checkPassword($_POST['emailID'], $_POST['password'], $_POST['role']);
                    $rememberMe = $_POST['rememberMe'];
                    $responseArray['requestType'] = 'validatePassword';
                    $responseArray['emailID'] = $_POST['emailID'];
                    if ($valid) {

                        

                        // generate new user token
                        $token = generateRandomToken();
                        $cookieInfo['emailID'] = $_POST['emailID'];
                        $cookieInfo['token'] = $token;
                        $cookieInfo['role'] = $_POST['role'];
                        $cookieInfo['rememberUser'] = $rememberMe;
                        $cookieJSON = json_encode($cookieInfo);
                        $cookieEncoded = base64_encode($cookieJSON);

                        // store the token in DB
                        $result = storeToken($_POST['emailID'], $token, $_POST['role']);
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
                    $existingCookie = $_COOKIE['pas_auth'];
                    $cookieArray = getCookieArray();
                    $emailID = $cookieArray['emailID'];
                    $token = $cookieArray['token'];
                    $rememberMe = $cookieArray['rememberUser'];
                    $role = $cookieArray['role'];

                    $valid = checkToken($emailID, $token, $role);

                    $responseArray['requestType'] = 'validateCookie';
                    $responseArray['emailID'] = $emailID;
                    if ($valid == 'valid') {
                        $active = checkAccountActivation($emailID, $role);
                        if ($active) {
                            $responseArray['result'] = 'valid';
                            // set the cookie
                            $time = 0;
                            if ($rememberMe == 'true') {
                                $time = time() + (24 * 3600 * 30);
                            }
                            setcookie("pas_auth", $existingCookie, $time, "/");
                        } else {
                            $responseArray['result'] = 'notactive';    
                        }
                    } else {
                        $responseArray['result'] = 'invalid';
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

    function checkEmail($emailID, $role) {
        $userController = new UserController($emailID, $role);
        return $userController->getUserValidity();
    }

    function checkAccountActivation($emailID, $role) {
        $userController = new UserController($emailID, $role);
        return $userController->getAccountActivation();
    }

    function checkPassword($emailID, $password, $role) {
        $userController = new UserController($emailID, $role);
        return $userController->getPasswordValidity($password);
    }

    function checkToken($emailID, $token, $role) {
        $userController = new UserController($emailID, $role);
        return $userController->getTokenValidity($token);
    }

    function storeToken($emailID, $token, $role) {
        $userController = new UserController($emailID, $role);
        return $userController->setToken($token);
    }
?>