<?php
    require("include/Utility.php");
    require("include/DatabaseController.php");

    /* Response Array */
    $responseArray = array();

    if (isset($_POST['emailID']) && !empty($_POST['emailID']) && isset($_POST['mobileNumber']) && !empty($_POST['mobileNumber']) && isset($_POST['role']) && !empty($_POST['role'])) {
        try {
            $mobileNumber = $_POST['mobileNumber'];
            $emailID = $_POST['emailID'];
           

            // Response Array Values
            $responseArray['requestType'] = 'updateProfileDetails';
            $cookieArray = getCookieArray();
            $responseArray['emailID'] = $cookieArray['emailID'];

            $dbController = new DatabaseController();
            $connection = $dbController->getConnection();
            $stmt = $connection->prepare("DELETE FROM auth_token WHERE email_id=?;");
            $stmt->bind_param("s", $cookieArray['emailID']);
            $stmt->execute();            
            $stmt = $connection->prepare("DELETE FROM pending_password_reset WHERE email_id=?;");
            $stmt->bind_param("s", $cookieArray['emailID']);
            $stmt->execute();            
            $stmt = $connection->prepare("UPDATE user SET email_id=?, mobile_number=? WHERE email_id=?;");
            $stmt->bind_param("sss", $emailID, $mobileNumber, $cookieArray['emailID']);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $responseArray['result'] = "success";
            } else if ($stmt->affected_rows == 0) {
                $responseArray['result'] = "dataEqual";
            } else {
                $responseArray['result'] = 'failure';
            }

            echo json_encode($responseArray);
        } catch (Exception $e) {
            die("Unexpected Server Error");
        }
    } else {
        http_response_code(400);
    }
?>