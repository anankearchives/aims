
<?php
include "sidebar.php";

// Get filter parameters
$searchTerm = $_POST['search'] ?? '';
$statusFilter = $_POST['status'] ?? 'impounded';

// Build query
$sql = "SELECT iv.*, vo.name as owner_name, vo.license_number, vo.phone,
        vt.code, vt.description, vt.base_fee, vt.daily_storage_fee,
        u.name as officer_name
        FROM impounded_vehicles iv
        LEFT JOIN vehicle_owners vo ON iv.owner_id = vo.id
        LEFT JOIN violation_types vt ON iv.violation_id = vt.id
        LEFT JOIN users u ON iv.officer_id = u.id
        WHERE iv.status = ?";

$params = [$statusFilter];
$types = "s";

if (!empty($searchTerm)) {
    $sql .= " AND (iv.plate_number LIKE ? OR vo.name LIKE ? OR iv.make LIKE ? OR iv.model LIKE ?)";
    $searchParam = "%$searchTerm%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ssss";
}

$sql .= " ORDER BY iv.impound_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Records</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/inventory.css">
    <link rel="stylesheet" href="css/homepage.css">
</head>
<body>
    <div class="content" style="margin-left: 100px; padding: 20px;">
        <h2>Vehicle Records</h2>

        <!-- Filter Form -->
        <div class="form-div">
            <form method="POST" action="">
                <input type="text" name="search" placeholder="Search plate, owner, make, model..." 
                       value="<?php echo htmlspecialchars($searchTerm); ?>" style="width: 40%;">
                
                <select name="status" style="width: 20%;">
                    <option value="impounded" <?php if($statusFilter === 'impounded') echo 'selected'; ?>>Impounded</option>
                    <option value="released" <?php if($statusFilter === 'released') echo 'selected'; ?>>Released</option>
                    <option value="disposed" <?php if($statusFilter === 'disposed') echo 'selected'; ?>>Disposed</option>
                </select>
                
                <button type="submit">Filter</button>
                <button type="submit" name="reset" onclick="this.form.search.value=''; this.form.status.value='impounded';">Reset</button>
            </form>
        </div>

        <!-- Records Table -->
        <?php
        if ($result->num_rows > 0) {
            echo '<div class="list-box" style="max-height: 600px; overflow-y: auto;">';
            echo '<table class="table" style="width:100%;">';
            echo '<thead style="position: sticky; top: 0; background: #f9f9f9;">';
            echo '<tr>';
            echo '<th>ID</th>';
            echo '<th>Status</th>';  // ADDED STATUS COLUMN
            echo '<th>Plate Number</th>';
            echo '<th>Vehicle</th>';
            echo '<th>Owner</th>';
            echo '<th>Violation</th>';
            echo '<th>Impound Date</th>';
            echo '<th>Location</th>';
            echo '<th>Officer</th>';
            if ($statusFilter === 'impounded') {
                echo '<th>Action</th>';
            }
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                
                // FIXED: Add status badge
                $status = strtolower($row['status']);
                echo '<td><span class="badge ' . $status . '">' . strtoupper($status) . '</span></td>';
                
                echo '<td><strong>' . htmlspecialchars($row['plate_number']) . '</strong></td>';
                echo '<td>' . htmlspecialchars($row['year']) . ' ' . 
                     htmlspecialchars($row['color']) . ' ' .
                     htmlspecialchars($row['make']) . ' ' . 
                     htmlspecialchars($row['model']) . '</td>';
                echo '<td>' . htmlspecialchars($row['owner_name']) . '<br><small>' . 
                     htmlspecialchars($row['license_number']) . '</small></td>';
                echo '<td>' . htmlspecialchars($row['code']) . '<br><small>' .
                     htmlspecialchars($row['description']) . '</small></td>';
                echo '<td>' . htmlspecialchars($row['impound_date']) . '</td>';
                echo '<td>' . htmlspecialchars($row['impound_location']) . '</td>';
                echo '<td>' . htmlspecialchars($row['officer_name']) . '</td>';
                
                if ($statusFilter === 'impounded') {
                    echo '<td><a href="release.php?id=' . $row['id'] . '"><button>Release</button></a></td>';
                }
                
                echo '</tr>';
            }
            
            echo '</tbody></table></div>';
        } else {
            echo '<p>No records found.</p>';
        }
        ?>
    </div>
</body>
</html>