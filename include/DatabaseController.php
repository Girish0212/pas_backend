<?php
    class DatabaseController {

        private $host = 'localhost';
        private $username = 'backend';
        private $password = 'password';
        private $dbname = 'pas';

        private $connection = null;
        
        /* Constructor */
        public function __construct() {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->dbname);
        }

        /* Destructor */
        public function __destruct() {
            $this->connection->close();
        }

        /* Checks if the connection is valid and returns the connection object. If invalid, returns false */
        public function getConnection() {
            if ($this->connection) {
                return $this->connection;
            } else {
                return false;
            }
        }


    }
?>