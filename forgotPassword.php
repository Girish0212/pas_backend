<?php
    require("include/Utility.php");
    require("include/UserController.php");

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require_once 'include/PHPMailer/Exception.php';
    require_once 'include/PHPMailer/PHPMailer.php';
    require_once 'include/PHPMailer/SMTP.php';

    /* Response Array */
    $responseArray = array();

    if (isset($_POST['emailID']) && !empty($_POST['emailID']) && isset($_POST['role']) && !empty($_POST['role'])) {
        $emailID = $_POST['emailID'];
        $role = $_POST['role'];

        $responseArray['requestType'] = 'resetPassword';
        $responseArray['emailID'] = $emailID;

        try {
            // check user validity
            $userController = new UserController($emailID, $role);
            $isValid = $userController->getUserValidity();

            if (!$isValid) {
                $responseArray['result'] = 'notexist';
            } else {
                // request for password reset
                $response = $userController->requestPasswordReset(); 
                if ($response) {
                    $mail = new PHPMailer(true);

                    // Encode Activation Data
                    $passwordResetData['emailID'] = $emailID;
                    $passwordResetData['code'] = $userController->getPasswordResetCode();
                    $passwordResetDataJSON = json_encode($passwordResetData);
                    $passwordResetDataEncoded = base64_encode($passwordResetDataJSON);

                    $resetLink = 'https://' . getFrontendScriptDir() . "resetPassword.htm?data=$passwordResetDataEncoded"; // edit this line

                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->Username = 'pingteambacktrack@gmail.com'; // YOUR gmail email
                    $mail->Password = '54575457'; // YOUR gmail password

                    // Sender and recipient settings
                    $mail->setFrom('noreply_pas@gmail.com', 'PAS Backend');
                    $mail->addAddress($emailID, $name);

                    // Setting the email content
                    $mail->IsHTML(true);
                    $mail->Subject = "Reset Password";
                    $mail->Body = "Hey $name,
                    <br>
                    <br>
                    Here's the <a href='$resetLink'>link</a> to reset the password for your account on Passport Enquiry System.<br><br>
                    If the link doesn't work, copy and paste this URL onto your browser's address bar:<br>
                    $resetLink<br><br>
                    <b>This link will expire in 10 minutes.</b><br><br>
                    Regards,<br><br>
                    PAS Team.";
                    $mail->AltBody = "Hey there, $name,
                    \n\n
                    Here's the link to reset the password for your new account on Passport Enquiry System.<br><br>
                    Copy and paste this URL onto your browser's address bar:
                    $resetLink.
                    This link will expire in 10 minutes.\n\n
                    Regards,\n\n
                    PAS Team.";
                    $mail->send();
                    $responseArray['result'] = "success";
                } else {
                    $responseArray['result'] = 'failure';
                }
            }
        } catch (Exception $e) {
            $responseArray['result'] = 'failure';
        }

        echo json_encode($responseArray);
    } else {
        http_response_code(400);
    }
?>