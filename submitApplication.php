<?php
    require("include/Utility.php");
    require("include/DatabaseController.php");

    /* Response Array */
    $responseArray = array();

    if (isset($_POST['action']) && !empty($_POST['action'])) {
        try {
            $fullName = $_POST['fullName'];
            $surname = $_POST['surname'];
            $gender = $_POST['gender'];
            $dateOfBirth = $_POST['dateOfBirth'];
            $mobileNumber = $_POST['mobileNumber'];
            $phoneNumber = $_POST['phoneNumber'];
            $email = $_POST['email'];
            $address = $_POST['address'];
            $state = $_POST['state'];
            $citizenship = $_POST['citizenship'];
            $panNumber = $_POST['panNumber'];
            $voterID = $_POST['voterID'];
            $rpoState = $_POST['rpoState'];
            $rpoDistrict = $_POST['rpoDistrict'];
            $rpoCentre = $_POST['rpoCentre'];
            $passportType = $_POST['passportType'];
            $passportBookletPages = $_POST['passportBookletPages'];
            $status = "pending";

            // Response Array Values
            $responseArray['requestType'] = 'submitApplication';
            $cookieArray = getCookieArray();
            $responseArray['emailID'] = $cookieArray['emailID'];

            if ($_POST['action'] == "submitApplication") {
                $status = "pending";
            }

            $dbController = new DatabaseController();
            $connection = $dbController->getConnection();
            $stmt = $connection->prepare("INSERT INTO application (full_name, surname, gender, date_of_birth, mobile_number, phone_number, email, address, state, citizenship, pan_number, voter_id, rpo_state, rpo_district, rpo_centre, passport_type, passport_booklet_pages, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
            $stmt->bind_param("ssssssssssssssssss", $fullName, $surname, $gender, $dateOfBirth, $mobileNumber, $phoneNumber, $email, $address, $state, $citizenship, $panNumber, $voterID, $rpoState, $rpoDistrict, $rpoCentre, $passportType, $passportBookletPages, $status);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $responseArray['result'] = "success";
            } else {
                $responseArray['result'] = "failure";
            }

            echo json_encode($responseArray);
        } catch (Exception $e) {
            die("Unexpected Server Error");
        }
    } else {
        http_response_code(400);
    }
?>