<?php
// Include the database configuration
require_once __DIR__ . '/../config/database.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Environment Bridge Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Environment Bridge Test</h1>
    
    <h2>Current Environment</h2>
    <p>Detected environment: <strong><?php echo Environment::detect(); ?></strong></p>
    
    <h2>Database Constants</h2>
    <table>
        <tr>
            <th>Constant</th>
            <th>Value</th>
        </tr>
        <tr>
            <td>DB_HOST</td>
            <td><?php echo DB_HOST; ?></td>
        </tr>
        <tr>
            <td>DB_NAME</td>
            <td><?php echo DB_NAME; ?></td>
        </tr>
        <tr>
            <td>DB_USER</td>
            <td><?php echo DB_USER; ?></td>
        </tr>
        <tr>
            <td>DB_PASS</td>
            <td>[hidden for security]</td>
        </tr>
    </table>
    
    <h2>Database Connection Test</h2>
    <?php
    try {
        $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo '<p class="success">✓ Database connection successful!</p>';
        
        // Check if we can see tables
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo '<p>Tables in database:</p>';
        echo '<ul>';
        if (count($tables) > 0) {
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
        } else {
            echo "<li>No tables found</li>";
        }
        echo '</ul>';
        
    } catch (PDOException $e) {
        echo '<p class="error">✗ Database connection failed: ' . $e->getMessage() . '</p>';
    }
    ?>
</body>
</html>

