<?php
include "sidebar.php";

$vehicleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($vehicleId > 0) {
    // Fetch vehicle details
    $stmt = $conn->prepare("SELECT iv.*, vo.name as owner_name, vo.license_number,
                           vt.code, vt.description, vt.base_fee, vt.daily_storage_fee
                           FROM impounded_vehicles iv
                           LEFT JOIN vehicle_owners vo ON iv.owner_id = vo.id
                           LEFT JOIN violation_types vt ON iv.violation_id = vt.id
                           WHERE iv.id = ?");
    $stmt->bind_param("i", $vehicleId);
    $stmt->execute();
    $vehicle = $stmt->get_result()->fetch_assoc();
    
    if (!$vehicle) {
        die("Vehicle not found");
    }
    
    // Calculate fees
    $impoundDate = new DateTime($vehicle['impound_date']);
    $now = new DateTime();
    $daysImpounded = $impoundDate->diff($now)->days;
    if ($daysImpounded < 1) $daysImpounded = 1;
    
    // Storage multiplier based on vehicle type (demonstrating OOP polymorphism concept)
    $storageMultiplier = 1.0;
    switch($vehicle['vehicle_type']) {
        case 'motorcycle': $storageMultiplier = 0.5; break;
        case 'truck': $storageMultiplier = 1.5; break;
        case 'bus': $storageMultiplier = 2.0; break;
        default: $storageMultiplier = 1.0;
    }
    
    $violationFee = $vehicle['base_fee'];
    $storageFee = $vehicle['daily_storage_fee'] * $storageMultiplier * $daysImpounded;
    $totalFee = $violationFee + $storageFee;
}

// Handle release submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'release') {
    $vehicleId = (int)$_POST['vehicle_id'];
    $amountPaid = (float)$_POST['amount_paid'];
    $paymentMethod = $_POST['payment_method'];
    $releasedTo = trim($_POST['released_to']);
    $idType = trim($_POST['id_type']);
    $idNumber = trim($_POST['id_number']);
    
    $daysImpounded = (int)$_POST['days_impounded'];
    $violationFee = (float)$_POST['violation_fee'];
    $storageFee = (float)$_POST['storage_fee'];
    $totalFee = (float)$_POST['total_fee'];
    
    if ($amountPaid < $totalFee) {
        $msg = json_encode('Insufficient payment. Required: ₱' . number_format($totalFee, 2));
        echo "<script>localStorage.setItem('pendingAlert', JSON.stringify({msg: $msg, type: 'error'}));</script>";
    } else {
        $conn->begin_transaction();
        
        try {
            // Insert release record
            $stmt = $conn->prepare("INSERT INTO release_records 
                (vehicle_id, release_date, days_impounded, violation_fee, storage_fee, 
                 total_fee, amount_paid, payment_method, released_by, released_to, id_type, id_number)
                VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("iiddddsssss", $vehicleId, $daysImpounded, $violationFee, 
                            $storageFee, $totalFee, $amountPaid, $paymentMethod, 
                            $_SESSION['id'], $releasedTo, $idType, $idNumber);
            $stmt->execute();
            
            // Update vehicle status
            $stmt = $conn->prepare("UPDATE impounded_vehicles SET status='released' WHERE id=?");
            $stmt->bind_param("i", $vehicleId);
            $stmt->execute();
            
            // Audit log
            $proc->addAuditLog($_SESSION['id'], 'release', $_SESSION['name'], 
                              "Released vehicle ID: $vehicleId");
            
            $conn->commit();
            
            $msg = json_encode('Vehicle released successfully!');
            echo "<script>localStorage.setItem('pendingAlert', JSON.stringify({msg: $msg, type: 'success', redirect: 'records.php', delay: 1500}));</script>";
        } catch (Exception $e) {
            $conn->rollback();
            $msg = json_encode('Error: ' . $e->getMessage());
            echo "<script>localStorage.setItem('pendingAlert', JSON.stringify({msg: $msg, type: 'error'}));</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Release Vehicle</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/inventory.css">
    <link rel="stylesheet" href="css/homepage.css">
</head>
<body>
    <div class="form-div" style="max-width: 800px; margin: 40px auto;">
        <h2>Release Vehicle</h2>
        
        <?php if ($vehicleId > 0 && $vehicle): ?>
            <div style="background: #fff5f5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <h3>Vehicle Information</h3>
                <p><strong>Plate Number:</strong> <?php echo htmlspecialchars($vehicle['plate_number']); ?></p>
                <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?></p>
                <p><strong>Owner:</strong> <?php echo htmlspecialchars($vehicle['owner_name']); ?></p>
                <p><strong>Violation:</strong> <?php echo htmlspecialchars($vehicle['description']); ?></p>
                <p><strong>Impound Date:</strong> <?php echo htmlspecialchars($vehicle['impound_date']); ?></p>
                <p><strong>Days Impounded:</strong> <?php echo $daysImpounded; ?> day(s)</p>
                <hr>
                <p><strong>Violation Fee:</strong> ₱<?php echo number_format($violationFee, 2); ?></p>
                <p><strong>Storage Fee:</strong> ₱<?php echo number_format($storageFee, 2); ?> 
                   (₱<?php echo number_format($vehicle['daily_storage_fee'], 2); ?>/day × <?php echo $storageMultiplier; ?> × <?php echo $daysImpounded; ?> days)</p>
                <h3 style="color: #636e72;"><strong>Total Fee: ₱<?php echo number_format($totalFee, 2); ?></strong></h3>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="release">
                <input type="hidden" name="vehicle_id" value="<?php echo $vehicleId; ?>">
                <input type="hidden" name="days_impounded" value="<?php echo $daysImpounded; ?>">
                <input type="hidden" name="violation_fee" value="<?php echo $violationFee; ?>">
                <input type="hidden" name="storage_fee" value="<?php echo $storageFee; ?>">
                <input type="hidden" name="total_fee" value="<?php echo $totalFee; ?>">
                
                <label for="released_to">Released To:</label>
                <input type="text" name="released_to" id="released_to" required style="width: 100%;">
                
                <label for="id_type">ID Type:</label>
                <select name="id_type" id="id_type" required style="width: 100%;">
                    <option value="">-- Select ID Type --</option>
                    <option value="Driver's License">Driver's License</option>
                    <option value="Passport">Passport</option>
                    <option value="National ID">National ID</option>
                    <option value="Company ID">Company ID</option>
                </select>
                
                <label for="id_number">ID Number:</label>
                <input type="text" name="id_number" id="id_number" required style="width: 100%;">
                
                <label for="amount_paid">Amount Paid (₱):</label>
                <input type="number" name="amount_paid" id="amount_paid" step="0.01" min="<?php echo $totalFee; ?>" required style="width: 100%;">
                <p id="change_display" style="font-weight: bold; color: #2d3436;"></p>
                
                <label for="payment_method">Payment Method:</label>
                <select name="payment_method" id="payment_method" required style="width: 100%;">
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="check">Check</option>
                </select>
                
                <br><br>
                <button type="submit" style="width: 100%;">Release Vehicle</button>
            </form>
        <?php else: ?>
            <p>Please select a vehicle from the <a href="records.php">records page</a>.</p>
        <?php endif; ?>
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
        // Calculate change dynamically
        document.getElementById('amount_paid')?.addEventListener('input', function() {
            const totalFee = <?php echo $totalFee ?? 0; ?>;
            const amountPaid = parseFloat(this.value) || 0;
            const change = amountPaid - totalFee;
            
            document.getElementById('change_display').textContent = 
                change >= 0 ? 'Change: ₱' + change.toFixed(2) : '';
        });

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
?>