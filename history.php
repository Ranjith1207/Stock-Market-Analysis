<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id']; // Get logged-in user ID
include('db_connection.php'); // Include the database connection

$query = "SELECT * FROM history WHERE user_id = '$user_id' ORDER BY search_time DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search History</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Your Search History</h1>
        </header>
        <div class="history-table">
            <table>
                <thead>
                    <tr>
                        <th>Ticker</th>
                        <th>Period</th>
                        <th>Strategy</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['ticker']); ?></td>
                            <td><?php echo htmlspecialchars($row['period']); ?></td>
                            <td><?php echo htmlspecialchars($row['strategy']); ?></td>
                            <td><?php echo htmlspecialchars($row['search_time']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
<div class="back-link">
                <a href="index.php">Back to Dashboard</a>
            </div>
    </div>
</body>
</html>
