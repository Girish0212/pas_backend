<?php
    require('include/DatabaseController.php');

    class UserController {

        private $emailID = "";
        private $role = "";
        private $dbController = null;
        private $userValidity = false;
        private $passwordResetCode = "";

        /* Constructor */
        public function __construct($emailID, $role) {
            $this->emailID = $emailID;
            $this->role = $role;
            $this->dbController = new DatabaseController();

            /* Check connection validity */
            if (!$this->dbController->getConnection()) {
                throw new Exception("DB_FAIL");
            }

            /* Check user validity */
            $this->userValidity = $this->isUserValid();
        }

        /* Checks if the user with provided e-mail ID exists, and return true or false depending upon the result */
        private function isUserValid() {
            $connection = $this->dbController->getConnection();
            $stmt = $connection->prepare("SELECT email_id FROM user WHERE email_id=? AND role=?;");
            $stmt->bind_param("ss", $this->emailID, $this->role);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function getUserValidity() {
            return $this->userValidity;
        }

        public function getPasswordValidity($password) {
            $pass_hash = hash('sha256', $password);

            $connection = $this->dbController->getConnection();
            $stmt = $connection->prepare("SELECT pass_hash FROM user WHERE email_id=? AND role=?;");
            $stmt->bind_param("ss", $this->emailID, $this->role);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    if ($pass_hash == $row['pass_hash']) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                return false;
            }
        }

        public function getAccountActivation() {
            $connection = $this->dbController->getConnection();
            $stmt = $connection->prepare("SELECT active FROM user WHERE email_id=?;");
            $stmt->bind_param("s", $this->emailID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $active = $row['active'];
                    if ($active == 'yes') {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                return false;
            }
        }

        public function getTokenValidity($token) {
            // check whether the user is valid first
            $connection = $this->dbController->getConnection();
            $stmt = $connection->prepare("SELECT * FROM user WHERE email_id=?;");
            $stmt->bind_param("s", $this->emailID);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // valid
            } else {
                return 'invalid';
            }

            // then check the token
            $connection = $this->dbController->getConnection();
            $stmt = $connection->prepare("SELECT * FROM auth_token WHERE email_id=? and token=?;");
            $stmt->bind_param("ss", $this->emailID, $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // check if token expired from today
                    $last_signon = new DateTime($row['last_signon']);
                    $date_now = new DateTime();
                    $interval = date_diff($last_signon, $date_now);
                    $days = $interval->format("%a");
                    if ($days > 30) {
                        $this->deleteExistingToken();
                        return 'expired';
                    } else {
                        return 'valid';
                    }
                }
            } else {
                $this->deleteExistingToken();
                return 'invalid';
            }
        }

        public function setToken($token) {
            $connection = $this->dbController->getConnection();
            $last_signon = date("Y-m-d H:i:s");
            $stmt = $connection->prepare("INSERT INTO auth_token (email_id, token, last_signon) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token=?, last_signon=?;");
            $stmt->bind_param("sssss", $this->emailID, $token, $last_signon, $token, $last_signon);
            $stmt->execute();
            if ($stmt->affected_rows >= 0) {
                return true;
            } else {
                return false;
            }
        }

        private function deleteExistingToken() {
            $connection = $this->dbController->getConnection();
            $stmt = $connection->prepare("DELETE FROM auth_token WHERE email_id=?;");
            $stmt->bind_param("s", $this->emailID);
            $stmt->execute();
            $result = $stmt->get_result();
        }

        public function getScreenName() {
            $connection = $this->dbController->getConnection();
            $stmt = $connection->prepare("SELECT first_name, middle_name, last_name FROM user WHERE email_id=?;");
            $stmt->bind_param("s", $this->emailID);
            $stmt->execute();
            $result = $stmt->get_result();

            $firstName = "";
            $middleName = "";
            $lastName = "";

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $firstName = $row['first_name'];
                    $middleName = $row['middle_name'];
                    $lastName = $row['last_name'];

                    if ($middleName != "") {
                        return "$firstName $middleName $lastName";
                    } else {
                        return "$firstName $lastName";
                    }
                }
            } else {
                return false;
            }
        }

        public function updateDetails($emailID,$mobileNumber) {
            $connection = $this->dbController->getConnection();
            $stmt = $connection->prepare("UPDATE user SET email_id=?,mobile_number=? WHERE email_id=?;");
            $stmt->bind_param("sss", $emailID, $mobileNumber,$this->emailID);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function updatePassword($password) {
            $connection = $this->dbController->getConnection();
            $passHash = hash('sha256', $password);

            $stmt = $connection->prepare("UPDATE user SET pass_hash=? WHERE email_id=?;");
            $stmt->bind_param("ss", $passHash, $this->emailID);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function requestPasswordReset() {
            $cstrong = true;
            $resetCode = openssl_random_pseudo_bytes(8, $cstrong);
            $resetCode = bin2hex($resetCode);
            $genTime = date("Y-m-d H:i:s");

            // store reset code to database (duplicate/new)
            $connection = $this->dbController->getConnection();
            $stmt = $connection->prepare("INSERT INTO pending_password_reset (email_id, reset_code, gen_time) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE reset_code=?, gen_time=?;");
            $stmt->bind_param("sssss", $this->emailID, $resetCode, $genTime, $resetCode, $genTime);
            $stmt->execute();
            if ($stmt->affected_rows >= 0) {
                return true;
            } else {
                return false;
            }
        }

        public function getPasswordResetCode() {
            if ($this->passwordResetCode != "") {
                return $this->passwordResetCode;
            } else {
                $connection = $this->dbController->getConnection();
                $stmt = $connection->prepare("SELECT * FROM pending_password_reset WHERE email_id=?;");
                $stmt->bind_param("s", $this->emailID);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $this->passwordResetCode = $row['reset_code'];
                        return $this->passwordResetCode;
                    }
                }
            }   
        }

        public function getApplicationFormStatus() {
            $connection = $this->dbController->getConnection();
            $stmt = $connection->prepare("SELECT status FROM application WHERE email=?;");
            $stmt->bind_param("s", $this->emailID);
            $stmt->execute();
            $result = $stmt->get_result();

            $firstName = "";
            $middleName = "";
            $lastName = "";

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $status = $row['status'];
                    return $status;
                }
            } else {
                return false;
            }
        }

        public function getApplicationFormDetails() {
            $connection = $this->dbController->getConnection();
            $stmt = $connection->prepare("SELECT * FROM application WHERE email=?;");
            $stmt->bind_param("s", $this->emailID);
            $stmt->execute();
            $result = $stmt->get_result();

            $firstName = "";
            $middleName = "";
            $lastName = "";

            $resultRow = array();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $resultRow[] = $row;
                }

                return $resultRow;
            } else {
                return false;
            }
        }

        public function getUserProfileDetails() {
            $connection = $this->dbController->getConnection();
            $stmt = $connection->prepare("SELECT email_id, first_name, middle_name, last_name, mobile_number FROM user WHERE email_id=?;");
            $stmt->bind_param("s", $this->emailID);
            $stmt->execute();
            $result = $stmt->get_result();

            $firstName = "";
            $middleName = "";
            $lastName = "";

            $resultRow = array();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $resultRow[] = $row;
                }
                $resultRow = $resultRow[0];

                return $resultRow;
            } else {
                return false;
            }
        }
    }
?>