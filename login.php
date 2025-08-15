<?php
// index.php
session_start();

// --- Helpers ---------------------------------------------------------------
function redirect_home() {
    header('Location: home_screen.html');
    exit;
}

// Already logged in? (either real or temp)
if (!empty($_SESSION['user_id']) || !empty($_SESSION['temp_user'])) {
    redirect_home();
}

// Paths to your future include files (adjust if different)
$DB_STATUS_INCLUDE = __DIR__ . '/includes/db_status.php';
$AUTH_INCLUDE      = __DIR__ . '/includes/auth.php';

// --- Determine DB status for the status line -------------------------------
$db_status_text = 'Unknown';
$db_is_connected = false;

if (file_exists($DB_STATUS_INCLUDE)) {
    // We try to include and call db_status(); expected string: 'OK'
    include_once $DB_STATUS_INCLUDE;
    if (function_exists('db_status')) {
        $result = null;
        try { $result = db_status(); } catch (Throwable $e) { /* ignore */ }
        if ($result === 'OK') {
            $db_is_connected = true;
            $db_status_text = 'Connected';
        } else {
            $db_status_text = 'Not connected';
        }
    } else {
        $db_status_text = 'Not connected';
    }
} else {
    $db_status_text = 'Not connected (file missing)';
}

// --- Handle Temp Continue (e.g., from modal button) ------------------------
if (isset($_POST['temp_continue']) && $_POST['temp_continue'] === '1') {
    $_SESSION['temp_user'] = true;
    $_SESSION['username']  = 'Guest';
    redirect_home();
}

// --- Handle Login Submit ---------------------------------------------------
$login_error = '';
$show_modal_include_failed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Attempt to ensure DB check happens "on send"
    $db_check_ok = false;
    if (file_exists($DB_STATUS_INCLUDE)) {
        include_once $DB_STATUS_INCLUDE;
        if (function_exists('db_status') && db_status() === 'OK') {
            $db_check_ok = true;
        }
    }

    if (!$db_check_ok) {
        // Either include missing or did not return expected 'OK' => show modal
        $show_modal_include_failed = true;
    } else {
        // DB seems up; try to auth
        if (file_exists($AUTH_INCLUDE)) {
            include_once $AUTH_INCLUDE;
            if (function_exists('auth_login')) {
                try {
                    $auth = auth_login($username, $password); // expected array or false
                    if ($auth && is_array($auth) && isset($auth['id'])) {
                        $_SESSION['user_id']  = $auth['id'];
                        $_SESSION['username'] = $auth['username'] ?? $username;
                        redirect_home();
                    } else {
                        $login_error = 'Invalid username or password.';
                    }
                } catch (Throwable $e) {
                    $login_error = 'Login failed due to a server error.';
                }
            } else {
                // Auth include doesn’t expose expected function => treat as include failure modal
                $show_modal_include_failed = true;
            }
        } else {
            // Auth include missing => show modal
            $show_modal_include_failed = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Pet Paradise - Login</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
	<style>
    #petDetails form {
        display: flex;
        flex-direction: column;
        gap: 10px; /* space between form groups */
        max-width: 250px; /* consistent width */
    }

    #petDetails label {
        font-weight: bold;
        margin-bottom: 4px;
    }

    #petDetails input[type="text"],
    #petDetails input[type="password"] {
        width: 100%;
        padding: 6px 8px;
        box-sizing: border-box;
    }
</style>
</head>
<body>
    <div class="header">
        <h1>🌟 Virtual Pet Paradise 🌟</h1>
        <p>Welcome! Please log in or continue as a guest.</p>
    </div>

    <div class="login-container">
        <div id="petDetails" class="pet-details" style="display: block;">
            <h2>Login</h2>
			<form id="loginForm" method="post">
				<div class="form-group">
					<label for="username">Username:</label>
					<input type="text" id="username" name="username" required>
				</div>
				<div class="form-group">
					<label for="password">Password:</label>
					<input type="password" id="password" name="password" required>
					<label class="show-password-label">
						<input type="checkbox" id="showPassword"> Show Password
					</label>
				</div>
				<button type="submit">Login</button>
			</form>
            <a href="#" id="forgotPW">Forgot your password?</a>
			<br>
			<a href="#" id="tempUserLink">Continue as Temp User</a>
			<br>
			<a href="index.php" id="register">I don't have a user</a>
			<div class="status">
				Database Status:
				<?php if ($db_is_connected): ?>
				<span class="ok">Connected ✅</span>
				<?php else: ?>
				<span class="bad">Not Connected ❌</span>
				<div class="center" style="margin-top:6px; font-size:0.9rem;">
					You can still continue as a temp user.
				</div>
				<?php endif; ?>
			</div>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const tempUserLink = document.getElementById('tempUserLink');
        const usernameInput = document.getElementById('username');

        // Handle normal login
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const username = usernameInput.value;
            const password = document.getElementById('password').value;
            // Normally you'd verify credentials with server here
            localStorage.setItem('username', username);
            window.location.href = 'home_screen.html';
        });

		// Updated Temp User flow
		tempUserLink.addEventListener('click', function (e) {
			e.preventDefault();

			let username = usernameInput.value.trim();

			if (!username) {
				username = prompt("Enter a username for temporary play:", "Guest");
				
				// If prompt was cancelled or blank, do nothing
				if (username === null) return; 
				username = username.trim();

				if (!username) return; // also block empty string
			}

			localStorage.setItem('username', username);
			window.location.href = 'home_screen.html';
		});
	</script>

</body>
</html>
