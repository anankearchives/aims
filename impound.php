<!-- ============= impound.php ============= -->
<?php
include "sidebar.php";

// Fetch owners and violations for dropdowns
$owners_result = $proc->getVehicleOwners();
$violations_result = $proc->getViolationTypes();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'impound') {
    $plateNumber = strtoupper(trim($_POST['plate_number']));
    $vehicleType = $_POST['vehicle_type'];
    $make = ucwords(strtolower(trim($_POST['make'])));
    $model = ucwords(strtolower(trim($_POST['model'])));
    $color = ucwords(strtolower(trim($_POST['color'])));
    $year = (int)$_POST['year'];
    $ownerId = (int)$_POST['owner_id'];
    $violationId = (int)$_POST['violation_id'];
    $location = trim($_POST['location']);
    $notes = trim($_POST['notes']);
    $officerId = $_SESSION['id'];
    
    try {
        $success = $proc->impoundVehicle(
            $plateNumber, $vehicleType, $make, $model, $color, $year,
            $ownerId, $violationId, $location, $officerId, $notes
        );
        
        if ($success) {
            $proc->addAuditLog($_SESSION['id'], 'impound', $_SESSION['name'], 
                              "Impounded vehicle: $plateNumber");
            $msg = json_encode('Vehicle impounded successfully!');
            echo "<script>localStorage.setItem('pendingAlert', JSON.stringify({msg: $msg, type: 'success', redirect: 'records.php', delay: 1200}));</script>";
        } else {
            $msg = json_encode('Failed to impound vehicle.');
            echo "<script>localStorage.setItem('pendingAlert', JSON.stringify({msg: $msg, type: 'error'}));</script>";
        }
    } catch (Exception $e) {
        $msg = json_encode('Error: ' . $e->getMessage());
        echo "<script>localStorage.setItem('pendingAlert', JSON.stringify({msg: $msg, type: 'error'}));</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impound Vehicle</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/inventory.css">
    <link rel="stylesheet" href="css/homepage.css">
</head>
<body>
    <div class="form-div" style="max-width: 800px; margin: 40px auto;">
        <h2>Impound Vehicle</h2>
        <p>Enter vehicle details to impound</p>
        <form method="POST" action="">
            <input type="hidden" name="form_type" value="impound">
            
            <label for="plate_number">Plate Number:</label>
            <input type="text" name="plate_number" id="plate_number" required style="width: 100%;">
            
            <label for="vehicle_type">Vehicle Type:</label>
            <select name="vehicle_type" id="vehicle_type" required style="width: 100%;">
                <option value="">-- Select Type --</option>
                <option value="motorcycle">Motorcycle</option>
                <option value="sedan">Sedan</option>
                <option value="suv">SUV</option>
                <option value="van">Van</option>
                <option value="truck">Truck</option>
                <option value="bus">Bus</option>
            </select>
            
            <label for="make">Make:</label>
            <input type="text" name="make" id="make" placeholder="e.g., Toyota" required style="width: 100%;">
            
            <label for="model">Model:</label>
            <input type="text" name="model" id="model" placeholder="e.g., Vios" required style="width: 100%;">
            
            <label for="color">Color:</label>
            <input type="text" name="color" id="color" required style="width: 100%;">
            
            <label for="year">Year:</label>
            <input type="number" name="year" id="year" min="1900" max="2025" required style="width: 100%;">
            
            <label for="owner_id">Vehicle Owner:</label>            <a href="owners.php"> (+ add New Owner)</a>
            <select name="owner_id" id="owner_id" required style="width: 100%;">
                <option value="">-- Select Owner --</option>
                <?php while ($owner = $owners_result->fetch_assoc()): ?>
                    <option value="<?php echo $owner['id']; ?>">
                        <?php echo htmlspecialchars($owner['name'] . ' - ' . $owner['license_number']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            
            <label for="violation_id">Violation:</label>
            <select name="violation_id" id="violation_id" required style="width: 100%;">
                <option value="">-- Select Violation --</option>
                <?php while ($violation = $violations_result->fetch_assoc()): ?>
                    <option value="<?php echo $violation['id']; ?>">
                        <?php echo htmlspecialchars($violation['code'] . ' - ' . $violation['description']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <label for="location">Impound Location:</label>
            <input type="text" name="location" id="location" required style="width: 100%;">
            
            <label for="notes">Notes:</label>
            <textarea name="notes" id="notes" rows="3" style="width: 100%;"></textarea>
            
            <br><br>
            <button type="submit" style="width: 100%;">Impound Vehicle</button>
        </form>
    </div>

    <!-- Custom Alert Modal -->
    <div id="customAlert" class="custom-alert">
        <div class="custom-alert-content">
            <div class="icon-container">
                <div class="checkmark"></div>
                <div class="cross">
                    <span class="cross-line cross-line1"></span>
                    <span class="cross-line cross-line2"></span>
                </div>
            </div>
            <p id="customAlertMessage">Message</p>
            <button id="customAlertOk">OK</button>
        </div>
    </div>

    <script>
        // Alert handling
        document.addEventListener('DOMContentLoaded', () => {
            const pending = localStorage.getItem('pendingAlert');
            if (pending) {
                try {
                    const obj = JSON.parse(pending);
                    showCustomAlert(obj.msg, obj.type);
                    if (obj.redirect) {
                        setTimeout(() => { window.location.href = obj.redirect; }, obj.delay || 1200);
                    }
                } catch (e) {
                    console.error('Invalid pendingAlert', e);
                }
                localStorage.removeItem('pendingAlert');
            }
        });

        function showCustomAlert(message, type) {
            const alertBox = document.getElementById('customAlert');
            const alertMessage = document.getElementById('customAlertMessage');
            const checkmark = alertBox.querySelector('.checkmark');
            const cross = alertBox.querySelector('.cross');

            alertMessage.textContent = message;

            if (type === 'success') {
                checkmark.style.display = 'block';
                cross.style.display = 'none';
            } else {
                checkmark.style.display = 'none';
                cross.style.display = 'block';
            }

            alertBox.classList.add('show');
            document.getElementById('customAlertOk').onclick = () => {
                alertBox.classList.remove('show');
            };
        }
    </script>
</body>
</html>

