<?php
    require('include/Utility.php');
    require('include/DatabaseController.php');

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require_once 'include/PHPMailer/Exception.php';
    require_once    'include/PHPMailer/PHPMailer.php';
    require_once    'include/PHPMailer/SMTP.php';

    /* Response Array */
    $responseArray = array();

    /* User Authentication API */
    if (isset($_POST['firstName']) && !empty($_POST['firstName']) &&
        isset($_POST['middleName']) &&
        isset($_POST['lastName']) && !empty($_POST['lastName']) &&
        isset($_POST['emailID']) && !empty($_POST['emailID']) &&
        isset($_POST['password']) && !empty($_POST['password'])) {

        // Response Array Values
        $responseArray['requestType'] = 'signUp';
        $responseArray['emailID'] = $emailID;
        
        // Fetch details from POST request
        $emailID = $_POST['emailID'];
        $password = $_POST['password'];
        $passHash = hash('sha256', $password);
        $firstName = $_POST['firstName'];
        $middleName = $_POST['middleName'];
        $lastName = $_POST['lastName'];
        $active = "no";

        try {
            $dbController = new DatabaseController();
            $conn = $dbController->getConnection();
    
            // Check if user already exists
            $stmt = $conn->prepare("SELECT * FROM user WHERE email_id=?;");
            $stmt->bind_param("s", $emailID);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result->num_rows > 0) {
                $responseArray['result'] = "alreadyExists";
            } else {
                $stmt = $conn->prepare("INSERT INTO user (email_id, pass_hash, active, first_name, middle_name, last_name) VALUES (?, ?, ?, ?, ?, ?);");
                $stmt->bind_param("ssssss", $emailID, $passHash, $active, $firstName, $middleName, $lastName);
                $stmt->execute();
                
                if ($stmt->affected_rows >= 0) {
                    // Send Activation Email
                    $mail = new PHPMailer(true);

                    try {
                        // Join Names
                        $name = $firstName;
                        if ($middleName != "") {
                            $name .= " $middleName";
                        }
                        $name .= " $lastName";

                        // Generate activation code & link
                        $activationCode = generateActivationCode();
                        // Encode Activation Data
                        $activationData['emailID'] = $emailID;
                        $activationData['code'] = $activationCode;
                        $activationDataJSON = json_encode($activationData);
                        $activationDataEncoded = base64_encode($activationDataJSON);
                        $activationLink = 'https://' . getBackendScriptDir() . "activateAccount.php?data=$activationDataEncoded"; // edit this line
                        $activationTime = date("Y-m-d H:i:s");
                        // Save the activation code in the database
                        $stmt = $conn->prepare("INSERT INTO pending_activation (email_id, activation_code, gen_time) VALUES (?, ?, ?);");
                        $stmt->bind_param("sss", $emailID, $activationCode, $activationTime);
                        $stmt->execute();

                        if ($stmt->affected_rows >= 0) {
                            // Server settings
                            $mail->isSMTP();
                            $mail->Host = 'smtp.gmail.com';
                            $mail->SMTPAuth = true;
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port = 587;

                            $mail->Username = 'saaivignesh20@gmail.com'; // YOUR gmail email
                            $mail->Password = 'saaivignesh@12345'; // YOUR gmail password

                            // Sender and recipient settings
                            $mail->setFrom('noreply_pas@gmail.com', 'PAS Backend');
                            $mail->addAddress($emailID, $name);

                            // Setting the email content
                            $mail->IsHTML(true);
                            $mail->Subject = "Account Activation";
                            $mail->Body = "Hey $name,
                            <br>
                            <br>
                            Here's the <a href=''>link</a> to activate your new account on Passport Enquiry System.<br><br>
                            If the link doesn't work, copy and paste this URL onto your browser's address bar:<br>
                            $activationLink<br><br>
                            <b>This link will expire in 10 minutes.</b><br><br>
                            Regards,<br><br>
                            PAS Team.";
                            $mail->AltBody = "Hey there, $name,
                            \n\n
                            Here's the link to activate your new account on Passport Enquiry System.<br><br>
                            Copy and paste this URL onto your browser's address bar:
                            $activationLink.
                            This link will expire in 10 minutes.\n\n
                            Regards,\n\n
                            PAS Team.";
                            $mail->send();
                            $responseArray['result'] = "success";
                        } else {
                            $responseArray['result'] = "failure";
                            rollbackRegistration();
                        }
                    } catch (Exception $e) {
                        $responseArray['result'] = "failure";
                        rollbackRegistration();
                    }
                } else {
                    $responseArray['result'] = "failure";
                }
            }
        } catch (Exception $e) {
            $responseArray['result'] = "failure";
        }

        echo json_encode($responseArray);
    } else {
        http_response_code(400);
    }

    function rollbackRegistration() {
        global $conn, $emailID;
        try {
            // rollback the registration
            $stmt = $conn->prepare("DELETE FROM pending_activation WHERE email_id=?;");
            $stmt->bind_param("s", $emailID);
            $stmt->execute();
            $stmt = $conn->prepare("DELETE FROM user WHERE email_id=?;");
            $stmt->bind_param("s", $emailID);
            $stmt->execute();
        } catch (Exception $e) {

        }
    }
?>