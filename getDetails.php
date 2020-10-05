<?php
    require('include/Utility.php');
    require('include/UserController.php');
    
    /* Response Array */
    $responseArray = array();

    /* User Authentication API */
    if (isset($_POST['info']) && !empty($_POST['info'])) {
        switch ($_POST['info']) {
            case 'screenName':
                $cookieArray = getCookieArray();
                $screenName = getScreenName($cookieArray['emailID']);
                $responseArray['requestType'] = 'getScreenName';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $screenName;
            break;
            case 'applicationStatus':
                $cookieArray = getCookieArray();
                $status = getApplicationStatus($cookieArray['emailID']);
                $responseArray['requestType'] = 'getApplicationStatus';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $status;
            break;
            case 'applicationDetails':
                $cookieArray = getCookieArray();
                $status = getApplicationDetails($cookieArray['emailID']);
                $responseArray['requestType'] = 'getApplicationDetails';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $status;
            break;
            case 'profileDetails':
                $cookieArray = getCookieArray();
                $details = getUserProfileDetails($cookieArray['emailID']);
                $responseArray['requestType'] = 'getUserProfileDetails';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $details;
            break;
        }
        echo json_encode($responseArray);
    } else {
        http_response_code(400);
    }

    function getScreenName($emailID) {
        $userController = new UserController($emailID);
        return $userController->getScreenName();
    }

    function getApplicationStatus($emailID) {
        $userController = new UserController($emailID);
        return $userController->getApplicationFormStatus();
    }

    function getApplicationDetails($emailID) {
        $userController = new UserController($emailID);
        return $userController->getApplicationFormDetails();
    }

    function getUserProfileDetails($emailID) {
        $userController = new UserController($emailID);
        return $userController->getUserProfileDetails();
    }
?>