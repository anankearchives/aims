<!-- ============= auditlog.php ============= -->
<?php
include "sidebar.php";

// Get filter parameters
$selected_personnel = $_POST['personnel'] ?? null;
$selected_action = $_POST['action'] ?? null;
$selected_date = $_POST['created_date'] ?? null;

if (isset($_POST['reset'])) {
    $selected_personnel = $selected_action = $selected_date = null;
}

// Fetch distinct personnel and dates for filters
$personnel_sql = "SELECT DISTINCT personnel FROM audit_log ORDER BY personnel";
$date_sql = "SELECT DISTINCT DATE(created_at) AS created_date FROM audit_log ORDER BY created_date DESC";

$personnel_result = $conn->query($personnel_sql);
$date_result = $conn->query($date_sql);

// Build filtered query
$query = "SELECT * FROM audit_log WHERE 1=1";
$params = [];
$types = "";

if (!empty($selected_personnel)) {
    $query .= " AND personnel = ?";
    $params[] = $selected_personnel;
    $types .= "s";
}

if (!empty($selected_action)) {
    $query .= " AND action = ?";
    $params[] = $selected_action;
    $types .= "s";
}

if (!empty($selected_date)) {
    $query .= " AND DATE(created_at) = ?";
    $params[] = $selected_date;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log</title>
    <link rel="stylesheet" href="audit.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/inventory.css">
    <link rel="stylesheet" href="css/homepage.css">
</head>
<body>
    <div class="content" style="margin-left: 100px; padding: 20px;">
        <h2>Audit Log</h2>

        <!-- Filter Form -->
        <div class="form-div">
            <form method="POST" action="">
                <label>Personnel:</label>
                <select name="personnel" style="width: 20%;">
                    <option value="">All</option>
                    <?php
                    $personnel_result->data_seek(0);
                    while ($row = $personnel_result->fetch_assoc()):
                    ?>
                        <option value="<?php echo htmlspecialchars($row['personnel']); ?>" 
                                <?php if ($selected_personnel === $row['personnel']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['personnel']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Action:</label>
                <select name="action" style="width: 20%;">
                    <option value="">All</option>
                    <option value="login" <?php if ($selected_action === 'login') echo 'selected'; ?>>Login</option>
                    <option value="logout" <?php if ($selected_action === 'logout') echo 'selected'; ?>>Logout</option>
                    <option value="impound" <?php if ($selected_action === 'impound') echo 'selected'; ?>>Impound</option>
                    <option value="release" <?php if ($selected_action === 'release') echo 'selected'; ?>>Release</option>
                </select>

                <label>Date:</label>
                <select name="created_date" style="width: 20%;">
                    <option value="">All</option>
                    <?php
                    $date_result->data_seek(0);
                    while ($row = $date_result->fetch_assoc()):
                    ?>
                        <option value="<?php echo htmlspecialchars($row['created_date']); ?>"
                                <?php if ($selected_date === $row['created_date']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['created_date']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button type="submit">Filter</button>
                <button type="submit" name="reset">Reset</button>
            </form>
        </div>

        <!-- Audit Log Table -->
        <?php if ($result->num_rows > 0): ?>
            <div class="list-box" style="max-height: 600px; overflow-y: auto;">
                <table class="queue-table" style="width: 100%;">
                    <thead style="position: sticky; top: 0;">
                        <tr>
                            <th>ID</th>
                            <th>Personnel</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['personnel']); ?></td>
                            <td>
                                <span style="padding: 4px 8px; border-radius: 4px; 
                                    <?php
                                    switch($row['action']) {
                                        case 'login': echo 'background: #d4edda; color: #155724;'; break;
                                        case 'logout': echo 'background: #f8d7da; color: #721c24;'; break;
                                        case 'impound': echo 'background: #fff3cd; color: #856404;'; break;
                                        case 'release': echo 'background: #d1ecf1; color: #0c5460;'; break;
                                        default: echo 'background: #e2e3e5; color: #383d41;';
                                    }
                                    ?>">
                                    <?php echo strtoupper(htmlspecialchars($row['action'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['details'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No audit records found.</p>
        <?php endif; ?>
    </div>
</body>
</html>