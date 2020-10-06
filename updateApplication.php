<?php
    require('include/Utility.php');
    require('include/DatabaseController.php');
    
    /* Response Array */
    $responseArray = array();

    /* User Authentication API */
    if (isset($_POST['role']) && !empty($_POST['role']) && isset($_POST['action']) && !empty($_POST['action']) && isset($_POST['application_number']) && !empty($_POST['application_number'])) {
        switch ($_POST['action']) {
            case 'approve':
                $cookieArray = getCookieArray();
                $details = approveApplication($_POST['application_number']);
                $responseArray['requestType'] = 'approveApplication';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $details;
            break;
            case 'reject':
                $cookieArray = getCookieArray();
                $details = rejectApplication($_POST['application_number']);
                $responseArray['requestType'] = 'rejectApplication';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $details;
            break;
            case 'passToPoliceVerification':
                $cookieArray = getCookieArray();
                $details = passToPoliceVerification($_POST['application_number']);
                $responseArray['requestType'] = 'passToPoliceVerification';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $details;
            break;
            case 'delete':
                $cookieArray = getCookieArray();
                $details = deleteApplication($_POST['application_number']);
                $responseArray['requestType'] = 'deleteApplication';
                $responseArray['emailID'] = $cookieArray['emailID'];
                $responseArray['result'] = $details;
            break;
        }
        echo json_encode($responseArray);
    } else {
        http_response_code(400);
    }

    function approveApplication($applicationNumber) {
        $dbController = new DatabaseController();
        $connection = $dbController->getConnection();

        // get unique passport number
        $passportNumber = generate_string(12);
        $collision = true;
        // collision checking passport number
        while ($collision == true) {
            $stmt = $connection->prepare("SELECT status FROM application WHERE passport_number=?");
            $stmt->bind_param("s", $passportNumber);
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                $collision = false;
            } else {
                $passportNumber = generate_string(12);
            }
        }
        $stmt = $connection->prepare("UPDATE application SET status='approved', passport_number=? WHERE application_number=?;");
        $stmt->bind_param("ss", $passportNumber, $applicationNumber);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    function rejectApplication($applicationNumber) {
        $dbController = new DatabaseController();
        $connection = $dbController->getConnection();
        $stmt = $connection->prepare("UPDATE application SET status='rejected' WHERE application_number=?;");
        $stmt->bind_param("s", $applicationNumber);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    function passToPoliceVerification($applicationNumber) {
        $dbController = new DatabaseController();
        $connection = $dbController->getConnection();
        $stmt = $connection->prepare("UPDATE application SET status='verification' WHERE application_number=?;");
        $stmt->bind_param("s", $applicationNumber);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    function deleteApplication($applicationNumber) {
        $dbController = new DatabaseController();
        $connection = $dbController->getConnection();
        $stmt = $connection->prepare("DELETE FROM application WHERE application_number=?;");
        $stmt->bind_param("s", $applicationNumber);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }
?>