<?php
// db.php

$servername = "localhost";  // Usually localhost for XAMPP
$username = "root";         // Default XAMPP username
$password = "";             // Default XAMPP password is empty
$dbname = "MISHAL";         // Your database name
$port = 3307;               // Your custom MySQL port

// Create connection with port
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//echo "Connected successfully"; // Uncomment to test connection

?>
