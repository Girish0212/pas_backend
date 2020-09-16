<?php
    require("include/Utility.php");
    require("include/UserController.php");

    /* Response Array */
    $responseArray = array();
    
    if (isset($_POST['data']) && !empty($_POST['data']) && isset($_POST['password']) && !empty($_POST['password'])) {
        try {
            $data = $_POST['data'];
            $decodedData = base64_decode($data);
            $dataArray = json_decode($decodedData, true);
            $emailID = $dataArray['emailID'];
            $resetCode = $dataArray['code'];
            $password = $_POST['password'];

            $responseArray['requestType'] = 'changePassword';
            $responseArray['emailID'] = $emailID;

            // verify
            $dbController = new DatabaseController();
            $connection = $dbController->getConnection();
            $stmt = $connection->prepare("SELECT * FROM pending_password_reset WHERE email_id=? AND reset_code=?;");
            $stmt->bind_param("ss", $emailID, $resetCode);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // get time
                $gen_time = "";
                while ($row = $result->fetch_assoc()) {
                    $gen_time = $row['gen_time'];
                }
                $gen_time = new DateTime($gen_time);
                $now_time = new DateTime();
                $interval = date_diff($gen_time, $now_time);
                $minutes = $interval->format("%i");
                if ($minutes >= 10) {
                    // expired
                    $responseArray['result'] = 'invalid';
                    try {
                        $stmt = $connection->prepare("DELETE FROM pending_password_reset WHERE email_id=? AND reset_code=?;");
                        $stmt->bind_param("ss", $emailID, $resetCode);
                        $stmt->execute();
                    } catch (Exception $e) {
                        
                    }

                    $responseArray['result'] = 'expiry_failure';
                } else {
                    // valid, change password
                    $userController = new UserController($emailID);
                    $isUserValid = $userController->getUserValidity();
                    if ($isUserValid) {
                        $updateResponse = $userController->updatePassword($password);
                        if ($updateResponse) {
                            $stmt = $connection->prepare("DELETE FROM pending_password_reset WHERE email_id=? AND reset_code=?;");
                            $stmt->bind_param("ss", $emailID, $resetCode);
                            $stmt->execute();

                            $responseArray['result'] = 'success';
                        } else {
                            $responseArray['result'] = 'update_failure';
                        }
                    } else {
                        $responseArray['result'] = 'user_validity_failure';
                    }
                }
            } else {
                $responseArray['result'] = 'code_validity_failure';
            }

            echo json_encode($responseArray);
        } catch (Exception $e) {

        }
    } else if (isset($_GET['verify']) && !empty($_GET['verify'])) {
        $data = $_GET['verify'];
        $decodedData = base64_decode($data);
        $dataArray = json_decode($decodedData, true);
        $emailID = $dataArray['emailID'];
        $resetCode = $dataArray['code'];

        $responseArray['requestType'] = 'verifyResetCode';
        $responseArray['emailID'] = $emailID;

        $dbController = new DatabaseController();
        $connection = $dbController->getConnection();
        $stmt = $connection->prepare("SELECT * FROM pending_password_reset WHERE email_id=? AND reset_code=?;");
        $stmt->bind_param("ss", $emailID, $resetCode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // get time
            $gen_time = "";
            while ($row = $result->fetch_assoc()) {
                $gen_time = $row['gen_time'];
            }
            $gen_time = new DateTime($gen_time);
            $now_time = new DateTime();
            $interval = date_diff($gen_time, $now_time);
            $minutes = $interval->format("%i");
            if ($minutes >= 10) {
                // expired
                $responseArray['result'] = 'invalid';

                try {
                    $stmt = $connection->prepare("DELETE FROM pending_password_reset WHERE email_id=? AND reset_code=?;");
                    $stmt->bind_param("ss", $emailID, $resetCode);
                    $stmt->execute();
                } catch (Exception $e) {

                }
            } else {
                $responseArray['result'] = 'valid';
            }
        } else {
            $responseArray['result'] = 'invalid';
        }

        echo json_encode($responseArray);
    } else {
        http_response_code(400);
    }
?>