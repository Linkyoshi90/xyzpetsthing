<!DOCTYPE html>
<html lang="en">
<head>
    <title>Virtual Pet Paradise</title>
</head>
<body>
	<?php
		include("include/include.php");
	?>
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
    <div class="login-container">
        <div id="petDetails" class="pet-details" style="display: block;">
            <h2>Register</h2>
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
				<div class="form-group">
					<label for="email">E-Mail:</label>
					<input type="text" id="email" name="email" required>
				</div>
				<button type="submit">Register</button>
			</form>
			<a href="#" id="tempUserLink">Continue as Temp User</a>
            <br>
			<a href="login.php" id="login">I already have a user</a>
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
            window.location.href = 'home_screen.php';
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
			window.location.href = 'home_screen.php';
		});
	</script>

</body>
</html>
