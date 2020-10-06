<?php
    require('include/Utility.php');
    require('include/UserController.php');
    
    /* Response Array */
    $responseArray = array();

    /* User Authentication API */
    if (isset($_POST['password']) && !empty($_POST['password']) && isset($_POST['role']) && !empty($_POST['role'])) {
        $responseArray['requestType'] = 'validatePassword2';
        $cookieArray = getCookieArray();
        $responseArray['emailID'] = $cookieArray['emailID'];

        $emailID = $cookieArray['emailID'];
        $userController = new UserController($emailID, $_POST['role']);
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