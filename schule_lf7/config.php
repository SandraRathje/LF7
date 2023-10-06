<?php
$servername = "192.168.137.241";
$username = "esel";
$password = "pass";
$dbname = "Wasserstand";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error  );
}

?>