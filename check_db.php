<?php
$conn = new mysqli("127.0.0.1", "root", "sa", "Instalabra");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Checking for 'is_blocked' column...\n";
$result = $conn->query("SHOW COLUMNS FROM usuario LIKE 'is_blocked'");
if ($result && $result->num_rows > 0) {
    echo "Column 'is_blocked' exists.\n";
    $result = $conn->query("SELECT COUNT(*) as count FROM usuario WHERE is_blocked IS NULL");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "NULL values in 'is_blocked': " . $row['count'] . "\n";
        
        $result = $conn->query("SELECT * FROM usuario LIMIT 1");
        if ($result && $result->num_rows > 0) {
           echo "Sample row exists.\n";
        }
    }
} else {
    echo "Column 'is_blocked' does NOT exist.\n";
    // Check for 'isBlocked' just in case
    $result = $conn->query("SHOW COLUMNS FROM usuario LIKE 'isBlocked'");
    if ($result && $result->num_rows > 0) {
        echo "Column 'isBlocked' exists.\n";
    } else {
        echo "Column 'isBlocked' does NOT exist either.\n";
        // Show all columns
        $result = $conn->query("SHOW COLUMNS FROM usuario");
        if ($result) {
            echo "Columns in usuario table:\n";
            while($row = $result->fetch_assoc()) {
                echo $row['Field'] . "\n";
            }
        }
    }
}
$conn->close();
?>
