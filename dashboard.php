<!-- ============= dashboard.php ============= -->
<?php
include "sidebar.php";

// Get statistics
$total_impounded_sql = "SELECT COUNT(*) as total FROM impounded_vehicles WHERE status='impounded'";
$total_released_sql = "SELECT COUNT(*) as total FROM impounded_vehicles WHERE status='released'";
$total_owners_sql = "SELECT COUNT(*) as total FROM vehicle_owners";
$total_violations_sql = "SELECT COUNT(*) as total FROM violation_types";

$total_impounded = $conn->query($total_impounded_sql)->fetch_assoc()['total'];
$total_released = $conn->query($total_released_sql)->fetch_assoc()['total'];
$total_owners = $conn->query($total_owners_sql)->fetch_assoc()['total'];
$total_violations = $conn->query($total_violations_sql)->fetch_assoc()['total'];

// Get recent impounds
$recent_sql = "SELECT iv.*, vo.name as owner_name, vt.description
               FROM impounded_vehicles iv
               LEFT JOIN vehicle_owners vo ON iv.owner_id = vo.id
               LEFT JOIN violation_types vt ON iv.violation_id = vt.id
               WHERE iv.status = 'impounded'
               ORDER BY iv.impound_date DESC
               LIMIT 5";
$recent_result = $conn->query($recent_sql);

// Get vehicle type distribution
$type_dist_sql = "SELECT vehicle_type, COUNT(*) as count 
                  FROM impounded_vehicles 
                  WHERE status='impounded' 
                  GROUP BY vehicle_type";
$type_dist_result = $conn->query($type_dist_sql);

$vehicle_types = [];
$vehicle_counts = [];
while ($row = $type_dist_result->fetch_assoc()) {
    $vehicle_types[] = $row['vehicle_type'];
    $vehicle_counts[] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/inventory.css">
    <link rel="stylesheet" href="css/homepage.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="content">
        <h2>VEHICLE IMPOUNDMENT  |  Dashboard</h2>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                    <div class="stat-title">Currently Impounded</div>
                    <div class="stat-value"><?php echo $total_impounded; ?></div>
            </div>
            <div class="stat-card">
                    <div class="stat-title">Total Released</div>
                    <div class="stat-value"><?php echo $total_released; ?></div>
            </div>
            <div class="stat-card">
                    <div class="stat-title">Registered Owners</div>
                    <div class="stat-value"><?php echo $total_owners; ?></div>
            </div>
            <div class="stat-card">
                    <div class="stat-title">Violation Types</div>
                    <div class="stat-value"><?php echo $total_violations; ?></div>
            </div>
        </div>

        <!-- Charts -->
        <div class="chart-row">
            <div class="chart-container">
                <canvas id="vehicleTypeChart"></canvas>
            </div>
        </div>

        <!-- Recent Impounds -->
        <div style="margin: 10px;">
            <h3>Recent Impounds</h3>
            <table class="table" style="width:97%; background: white; border-radius: 8px; overflow: hidden;">
                <thead>
                    <tr>
                        <th>Plate Number</th>
                        <th>Vehicle</th>
                        <th>Owner</th>
                        <th>Violation</th>
                        <th>Date Impounded</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $recent_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['plate_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['year'] . ' ' . $row['make'] . ' ' . $row['model']); ?></td>
                        <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['impound_date']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Vehicle Type Distribution Chart
        const ctx = document.getElementById('vehicleTypeChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($vehicle_types); ?>,
                datasets: [{
                    label: 'Vehicles by Type',
                    data: <?php echo json_encode($vehicle_counts); ?>,
                    backgroundColor: [
                        'rgba(255, 240, 245, 1.0)',
                        'rgba(255, 204, 204, 1.0)',
                        'rgba(255, 111, 97, 1.0)',
                        'rgba(250, 114, 104, 1.0)',
                        'rgba(237, 102, 102, 1.0)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Impounded Vehicles by Type',
                        font: { size: 16 }
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
