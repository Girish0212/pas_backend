<?php
    require('include/Utility.php');
    require('include/UserController.php');
    
    /* Response Array */
    $responseArray = array();

    /* User Authentication API */
    if (isset($_POST['info']) && !empty($_POST['info']) && isset($_POST['role']) && !empty($_POST['role'])) {
        switch ($_POST['info']) {
            case 'screenName':
                $cookieArray = getCookieArray();
                $screenName = getScreenName($cookieArray['emailID'], $_POST['role']);
                $responseArray['requestType'] = 'getScreenName';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $screenName;
            break;
            case 'applicationStatus':
                $cookieArray = getCookieArray();
                $status = getApplicationStatus($cookieArray['emailID'], $_POST['role']);
                $responseArray['requestType'] = 'getApplicationStatus';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $status;
            break;
            case 'applicationDetails':
                $cookieArray = getCookieArray();
                $status = getApplicationDetails($cookieArray['emailID'], $_POST['role']);
                $responseArray['requestType'] = 'getApplicationDetails';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $status;
            break;
            case 'passportApplicationDate':
                $cookieArray = getCookieArray();
                $status = getPassportApplicationDate($cookieArray['emailID'], $_POST['role']);
                $responseArray['requestType'] = 'getPassportApplicationDate';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $status;
            break;
            case 'passportNumber':
                $cookieArray = getCookieArray();
                $status = getPassportNumber($cookieArray['emailID'], $_POST['role']);
                $responseArray['requestType'] = 'getPassportNumber';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $status;
            break;
            case 'allApplicationDetails':
                $cookieArray = getCookieArray();
                $status = getAllApplicationDetails($cookieArray['emailID'], $_POST['role']);
                $responseArray['requestType'] = 'getAllApplicationDetails';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $status;
            break;
            case 'specificApplicationDetails':
                $cookieArray = getCookieArray();
                $status = getSpecificApplicationDetails($_POST['application_number'], $_POST['role']);
                $responseArray['requestType'] = 'getSpecificApplicationDetails';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $status;
            break;
            case 'profileDetails':
                $cookieArray = getCookieArray();
                $details = getUserProfileDetails($cookieArray['emailID'], $_POST['role']);
                $responseArray['requestType'] = 'getUserProfileDetails';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $details;
            break;
            case 'pendingApplicationsCount':
                $cookieArray = getCookieArray();
                $count = getApplicationsCount("pending", $_POST['role']);
                $responseArray['requestType'] = 'getPendingApplicationsCount';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $count;
            break;
            case 'verificationApplicationsCount':
                $cookieArray = getCookieArray();
                $count = getApplicationsCount("verification", $_POST['role']);
                $responseArray['requestType'] = 'getVerificationApplicationsCount';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $count;
            break;
            case 'approvedApplicationsCount':
                $cookieArray = getCookieArray();
                $count = getApplicationsCount("approved", $_POST['role']);
                $responseArray['requestType'] = 'getApprovedApplicationsCount';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $count;
            break;
            case 'rejectedApplicationsCount':
                $cookieArray = getCookieArray();
                $count = getApplicationsCount("rejected", $_POST['role']);
                $responseArray['requestType'] = 'getRejectedApplicationsCount';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $count;
            break;
        }
        echo json_encode($responseArray);
    } else {
        http_response_code(400);
    }

    function getScreenName($emailID, $role) {
        $userController = new UserController($emailID, $role);
        return $userController->getScreenName();
    }

    function getApplicationStatus($emailID, $role) {
        $userController = new UserController($emailID, $role);
        return $userController->getApplicationFormStatus();
    }

    function getApplicationDetails($emailID, $role) {
        $userController = new UserController($emailID, $role);
        return $userController->getApplicationFormDetails();
    }

    function getAllApplicationDetails($emailID, $role) {
        $dbController = new DatabaseController();
        $connection = $dbController->getConnection();
        $stmt = $connection->prepare("SELECT * FROM application;");
        $stmt->execute();
        $result = $stmt->get_result();

        $resultRows = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $resultRows[] = $row;
            }
            return $resultRows;
        } else {
            return false;
        }
    }

    function getPassportApplicationDate($emailID, $role) {
        $dbController = new DatabaseController();
        $connection = $dbController->getConnection();
        $stmt = $connection->prepare("SELECT applied_on FROM application WHERE email=?;");
        $stmt->bind_param("s", $emailID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $date = $row['applied_on'];
                return $date;
            }
        } else {
            return false;
        }
    }

    function getPassportNumber($emailID, $role) {
        $dbController = new DatabaseController();
        $connection = $dbController->getConnection();
        $stmt = $connection->prepare("SELECT passport_number FROM application WHERE email=?;");
        $stmt->bind_param("s", $emailID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $date = $row['passport_number'];
                return $date;
            }
        } else {
            return false;
        }
    }

    function getSpecificApplicationDetails($applicationNumber, $role) {
        $dbController = new DatabaseController();
        $connection = $dbController->getConnection();
        $stmt = $connection->prepare("SELECT * FROM application WHERE application_number=?;");
        $stmt->bind_param("s", $applicationNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        $resultRows = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $resultRows[] = $row;
            }
            return $resultRows;
        } else {
            return false;
        }
    }

    function getUserProfileDetails($emailID, $role) {
        $userController = new UserController($emailID, $role);
        return $userController->getUserProfileDetails();
    }

    // statistical
    function getApplicationsCount($status, $role) {
        $dbController = new DatabaseController();
        $connection = $dbController->getConnection();
        $stmt = $connection->prepare("SELECT * FROM application WHERE status=?;");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows;
    }
?>