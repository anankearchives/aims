<?php
// ============= connection.php =============
class Database {
    private $sname = "localhost";
    private $uname = "root";
    private $password = "";
    private $db_name = "vehicle_impound_db";
    public $conn;

    public function __construct() {
        $this->conn = mysqli_connect($this->sname, $this->uname, $this->password, $this->db_name);
        
        if (!$this->conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
    }
}

?>