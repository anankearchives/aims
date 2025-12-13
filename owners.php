
<!-- ============= owners.php ============= -->
<?php
include "sidebar.php";

// Handle add owner form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'add_owner') {
    $name = ucwords(strtolower(trim($_POST['name'])));
    $license = strtoupper(trim($_POST['license_number']));
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = strtolower(trim($_POST['email']));
    
    $ownerId = $proc->addVehicleOwner($name, $license, $address, $phone, $email);
    
    if ($ownerId) {
        $msg = json_encode('Owner added successfully!');
        echo "<script>localStorage.setItem('pendingAlert', JSON.stringify({msg: $msg, type: 'success'}));</script>";
    } else {
        $msg = json_encode('Failed to add owner.');
        echo "<script>localStorage.setItem('pendingAlert', JSON.stringify({msg: $msg, type: 'error'}));</script>";
    }
}

// Fetch all owners
$owners_result = $proc->getVehicleOwners();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Owners</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/inventory.css">
    <link rel="stylesheet" href="css/homepage.css">
</head>
<body>
    <script src="js/functions.js"></script>
    <div class="content" style="margin-left: 100px; padding: 20px;">
        <h2>Vehicle Owners</h2>

        <button data-popup="add_owner" data-action="show">+ Add New Owner</button>

        <!-- Owners List -->
        <div style="margin-top: 20px;">
            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>License Number</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($owner = $owners_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($owner['id']); ?></td>
                        <td><?php echo htmlspecialchars($owner['name']); ?></td>
                        <td><?php echo htmlspecialchars($owner['license_number']); ?></td>
                        <td><?php echo htmlspecialchars($owner['address']); ?></td>
                        <td><?php echo htmlspecialchars($owner['phone']); ?></td>
                        <td><?php echo htmlspecialchars($owner['email']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Owner Popup -->
    <div id="add_owner" class="popup">
        <h3>Add New Owner</h3>
        <form method="POST" action="">
            <input type="hidden" name="form_type" value="add_owner">
            
            <label>Name:</label>
            <input type="text" name="name" required style="width: 100%;">
            
            <label>License Number:</label>
            <input type="text" name="license_number" placeholder="e.g., N01-12-345678" required style="width: 100%;">
            
            <label>Address:</label>
            <textarea name="address" rows="2" style="width: 100%;"></textarea>
            
            <label>Phone:</label>
            <input type="text" name="phone" placeholder="e.g., 09171234567" required style="width: 100%;">
            
            <label>Email:</label>
            <input type="email" name="email" style="width: 100%;">
            
            <br><br>
            <button type="submit">Add Owner</button>
        </form>
    </div>

    <script src="functions.js"></script>
</body>
</html>
