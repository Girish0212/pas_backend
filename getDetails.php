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
        }
        echo json_encode($responseArray);
    } else {
        http_response_code(400);
    }

    function getScreenName($emailID) {
        $userController = new UserController($emailID);
        return $userController->getScreenName();
    }
?>