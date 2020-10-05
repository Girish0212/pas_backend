<?php
    require('include/Utility.php');
    require('include/UserController.php');
    
    /* Response Array */
    $responseArray = array();

    /* User Authentication API */
    if (isset($_POST['password']) && !empty($_POST['password'])) {
        $responseArray['requestType'] = 'validatePassword2';
        $cookieArray = getCookieArray();
        $responseArray['emailID'] = $cookieArray['emailID'];

        $emailID = $cookieArray['emailID'];
        $userController = new UserController($emailID);
        $isPasswordValid = $userController->getPasswordValidity($_POST['password']);
        $responseArray['password'] = $_POST['password'];
        $responseArray['hash'] = hash('sha256', $password);
        if ($isPasswordValid) {
            $responseArray['result'] = 'valid';
        } else {
            $responseArray['result'] = 'invalid';
        }
        echo json_encode($responseArray);
    } else {
        http_response_code(400);
    }
?>