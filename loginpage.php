<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Impoundment Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo -->
            <div class="login-logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 20V10"></path>
                    <path d="M12 20V4"></path>
                    <path d="M6 20v-6"></path>
                </svg>
            </div>

            <!-- Header -->
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Sign in to access Vehicle Impoundment System</p>
            </div>

            <!-- Form -->
            <form action="login.php" method="post">
                <?php if (isset($_GET['error'])) { ?>
                    <div class="error-alert">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php } ?>

                <!-- Username Field -->
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper password-wrapper">
                        <span class="input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <img src="emg/eye-icon.png" style="max-width: none; height: auto;" alt="Description" alt="Toggle" class="toggle-password" onclick="togglePasswordVisibility()">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login">
                    <span>Sign In</span>
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <p>Powered by</p>
                <p class="system-name">Vehicle Impoundment Record Management System</p>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.src = 'emg/eye-slash-icon.png';
            } else {
                passwordInput.type = 'password';
                toggleIcon.src = 'emg/eye-icon.png';
            }
        }
    </script>
</body>
</html>