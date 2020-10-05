<?php
    require("include/Utility.php");
    require("include/DatabaseController.php");

    /* Response Array */
    $responseArray = array();

    if (isset($_POST['password']) && !empty($_POST['password'])) {
        try {
            $password = $_POST['password'];
            $passHash = hash('sha256', $password);

            // Response Array Values
            $responseArray['requestType'] = 'updateUserPassword';
            $cookieArray = getCookieArray();
            $emailID = $cookieArray['emailID'];

            $dbController = new DatabaseController();
            $connection = $dbController->getConnection();
            $stmt = $connection->prepare("UPDATE user SET pass_hash=? WHERE email_id=?;");
            $stmt->bind_param("ss", $passHash, $emailID);
            $stmt->execute();
            $responseArray['emailID'] = $emailID;
            $responseArray['passHash'] = $passHash;
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