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
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style.css">
	<style>
    .header {
        margin-top: -24px;
    }
    </style>
    <body>
        <div class="header">
            <h1>🌟 Virtual Pet Paradise 🌟</h1>
            <p>Welcome! Please log in or continue as a guest.</p>
            <a href="vote.php">Vote</a>
        </div>
    </body>
</html>