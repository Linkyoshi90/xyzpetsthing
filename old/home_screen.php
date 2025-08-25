<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neopets Clone - Virtual Pet Game</title>
	<link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="header">
        <h1>🌟 Virtual Pet Paradise 🌟</h1>
        <p>Care for your adorable pets and watch them thrive!</p>
        <a href="vote.php">Vote</a>
    </div>

    <div class="container">
        <div class="pets-grid" id="petsGrid">
            <!-- Pets will be dynamically generated here -->
        </div>

        <div id="petDetails" class="pet-details" style="display: none;">
            <span class="pet-details-close" id="petDetailsClose">&times;</span>
            <h2>Pet Details</h2>
            <div id="detailsContent"></div>
        </div>
    </div>

    <div class="context-menu" id="contextMenu">
        <div class="context-menu-item" data-action="feed">🍎 Feed</div>
        <div class="context-menu-item" data-action="clean">🛁 Clean</div>
        <div class="context-menu-item" data-action="play">🎾 Play</div>
        <div class="context-menu-item" data-action="heal">💊 Heal</div>
    </div>

    <div id="actionModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <svg class="modal-pet-avatar" id="modalPetAvatar" viewBox="0 0 120 120"></svg>
            <h2 id="modalTitle">Action Result</h2>
            <p id="modalMessage">Something happened!</p>
            <div id="modalStats"></div>
        </div>
    </div>
    <script src="js/home_screen.js"></script>
</body>
</html>