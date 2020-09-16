<?php
    require("include/Utility.php");
    require("include/DatabaseController.php");

    if (isset($_GET['data']) && !empty($_GET['data'])) {
        try {
            $decoded = base64_decode($_GET['data']);
            $activationData = json_decode($decoded, true);
            $emailID = $activationData['emailID'];
            $activationCode = $activationData['code'];
            
            $dbController = new DatabaseController();
            $conn = $dbController->getConnection();
            // check whether the activation code is correct
            $stmt = $conn->prepare("SELECT * FROM pending_activation WHERE email_id=? AND activation_code=?;");
            $stmt->bind_param("ss", $emailID, $activationCode);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // activation code and email id pair valid, check expiry time
                $gen_time = "";
                while ($row = $result->fetch_assoc()) {
                    $gen_time = $row['gen_time'];
                }
                $gen_time_date = new DateTime($gen_time);
                $time_date_now = new DateTime();

                $interval = date_diff($gen_time_date, $time_date_now);
                $time = $interval->format("%i");
                if ($time >= 10) {
                    // expired
                    try {
                        $stmt = $conn->prepare("DELETE FROM pending_activation WHERE email_id=? AND activation_code=?;");
                        $stmt->bind_param("ss", $emailID, $activationCode);
                        $stmt->execute();
                        $stmt = $conn->prepare("DELETE FROM user WHERE email_id=?;");
                        $stmt->bind_param("s", $emailID);
                        $stmt->execute();
                    } catch (Exception $e) {

                    }
                    header('location: /PassportAutomationSystem/index.htm?activation_failure');
                } else {
                    // valid
                    try {
                        $stmt = $conn->prepare("DELETE FROM pending_activation WHERE email_id=? AND activation_code=?;");
                        $stmt->bind_param("ss", $emailID, $activationCode);
                        $stmt->execute();

                        $stmt = $conn->prepare("UPDATE user SET active='yes' WHERE email_id=?;");
                        $stmt->bind_param("s", $emailID);
                        $stmt->execute();

                        header('location: /PassportAutomationSystem/index.htm?activation_success');
                    } catch (Exception $e) {
                        header('location: /PassportAutomationSystem/index.htm?activation_server_error');
                    }
                }
            }
        } catch (Exception $e) {
            die("Unexpected Server Error");
        }
    } else {
        http_response_code(400);
    }
?>