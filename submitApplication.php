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
            $idProofNumber = $_POST['idProofNumber'];
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

            // upload files
            $passportPhoto = uploadImage("passportPhoto");
            $idProof = uploadImage("idProof");
            $addressProof = uploadImage("addressProof");

            if ($passportPhoto != false && $idProof != false && $addressProof != false) {
                $appliedOn = date("Y-m-d");
                $dbController = new DatabaseController();
                $connection = $dbController->getConnection();
                // delete previous application, if existing (don't allow duplicate applications as of now)
                $stmt = $connection->prepare("DELETE FROM application WHERE email=?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                // proceed to insert application row
                $stmt = $connection->prepare("INSERT INTO application (applied_on, full_name, surname, gender, date_of_birth, mobile_number, phone_number, email, address, state, citizenship, id_proof_number, voter_id, rpo_state, rpo_district, rpo_centre, passport_type, passport_booklet_pages, passport_photo, id_proof, address_proof, passport_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', ?);");
                $stmt->bind_param("ssssssssssssssssssssss", $appliedOn, $fullName, $surname, $gender, $dateOfBirth, $mobileNumber, $phoneNumber, $email, $address, $state, $citizenship, $idProofNumber, $voterID, $rpoState, $rpoDistrict, $rpoCentre, $passportType, $passportBookletPages, $passportPhoto, $idProof, $addressProof, $status);
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    $responseArray['result'] = "success";
                } else {
                    $responseArray['result'] = "failure";
                }
            } else {
                $responseArray['result'] = "upload_failure";
            }

            echo json_encode($responseArray);
        } catch (Exception $e) {
            die("Unexpected Server Error");
        }
    } else {
        var_dump($_POST['action']);
        http_response_code(400);
    }

    function uploadImage($name) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES[$name]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $target_filename = $_POST['email'] . "-" . $name . "." . $imageFileType;
        $target_file = $target_dir .  $target_filename;
        //echo "$target_file ";

        // check whether file was properly uploaded
        if (!file_exists($_FILES[$name]['tmp_name']) || !is_uploaded_file($_FILES[$name]['tmp_name'])) 
        {
            return "FILE_NOT_FOUND";
        }
        else
        {
            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES[$name]["tmp_name"]);
            if($check !== false) {
                $uploadOk = 1;
            } else {
                return "FAKE_IMAGE";
            }
        }

        // Check file size
        if ($_FILES[$name]["size"] > 204800) {
            return "FILE_LARGE";
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "pdf" ) {
            return "FILE_INVALID_FORMAT";
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            return "UPLOAD_FLAG_ZERO";
        } else {
            if (move_uploaded_file($_FILES[$name]["tmp_name"], $target_file)) {
                return $target_filename;
            } else {
                return "UPLOAD_MOVE_FAILED";
            }
        }
    }
?>