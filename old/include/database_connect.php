<?php
	$database = new mysqli("localhost","lenz","Stinki15-","MyWorld");
	if ($database->connect_errno > 0){
            die('Keine Verbindung zur Datenbank! '
                    . '[' . $database->connect_error . ']');
	}
	else {
            
	}
?>
