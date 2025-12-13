<?php
include "connection.php";

class Procedures {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->conn;
    }

    public function getConnection() {
        return $this->conn;
    }
    
    // Get all impounded vehicles
    public function getImpoundedVehicles($status = 'impounded') {
        $sql = "SELECT iv.*, vo.name as owner_name, vo.license_number, vo.phone,
                vt.code, vt.description, vt.base_fee, vt.daily_storage_fee,
                u.name as officer_name
                FROM impounded_vehicles iv
                LEFT JOIN vehicle_owners vo ON iv.owner_id = vo.id
                LEFT JOIN violation_types vt ON iv.violation_id = vt.id
                LEFT JOIN users u ON iv.officer_id = u.id
                WHERE iv.status = ?
                ORDER BY iv.impound_date DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Get violation types
    public function getViolationTypes() {
        $sql = "SELECT * FROM violation_types ORDER BY code";
        return $this->conn->query($sql);
    }
    
    // Get vehicle owners
    public function getVehicleOwners() {
        $sql = "SELECT * FROM vehicle_owners ORDER BY name";
        return $this->conn->query($sql);
    }
    
    // Add new vehicle owner
    public function addVehicleOwner($name, $license, $address, $phone, $email) {
        $stmt = $this->conn->prepare(
            "INSERT INTO vehicle_owners (name, license_number, address, phone, email) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $name, $license, $address, $phone, $email);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
    
    // Impound vehicle (PHP version - alternative to Java)
    public function impoundVehicle($plateNumber, $vehicleType, $make, $model, $color, $year,
                                   $ownerId, $violationId, $location, $officerId, $notes) {
        $stmt = $this->conn->prepare(
            "INSERT INTO impounded_vehicles 
             (plate_number, vehicle_type, make, model, color, year, owner_id, 
              violation_id, impound_date, impound_location, officer_id, status, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, 'impounded', ?)"
        );
        
        $stmt->bind_param("sssssiiisis", 
            $plateNumber, $vehicleType, $make, $model, $color, $year,
            $ownerId, $violationId, $location, $officerId, $notes
        );
        
        return $stmt->execute();
    }
    
    // Display impounded vehicles table
    public function showImpoundedVehicles($result) {
        if ($result->num_rows > 0) {
            echo '<div class="list-box">';
            echo '<table class="table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>ID</th>';
            echo '<th>Plate Number</th>';
            echo '<th>Vehicle</th>';
            echo '<th>Owner</th>';
            echo '<th>Violation</th>';
            echo '<th>Impound Date</th>';
            echo '<th>Location</th>';
            echo '<th>Officer</th>';
            echo '<th>Action</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                echo '<td>' . htmlspecialchars($row['plate_number']) . '</td>';
                echo '<td>' . htmlspecialchars($row['year']) . ' ' . 
                     htmlspecialchars($row['make']) . ' ' . 
                     htmlspecialchars($row['model']) . '</td>';
                echo '<td>' . htmlspecialchars($row['owner_name']) . '</td>';
                echo '<td>' . htmlspecialchars($row['description']) . '</td>';
                echo '<td>' . htmlspecialchars($row['impound_date']) . '</td>';
                echo '<td>' . htmlspecialchars($row['impound_location']) . '</td>';
                echo '<td>' . htmlspecialchars($row['officer_name']) . '</td>';
                echo '<td><button onclick="releaseVehicle(' . $row['id'] . ')">Release</button></td>';
                echo '</tr>';
            }
            
            echo '</tbody></table></div>';
        } else {
            echo '<p>No impounded vehicles found.</p>';
        }
    }
    
    // Audit log
    public function addAuditLog($userId, $action, $personnel, $details = null) {
        $stmt = $this->conn->prepare(
            "INSERT INTO audit_log (user_id, action, personnel, details, created_at) 
             VALUES (?, ?, ?, ?, NOW())"
        );
        $stmt->bind_param("isss", $userId, $action, $personnel, $details);
        return $stmt->execute();
    }
}
?>