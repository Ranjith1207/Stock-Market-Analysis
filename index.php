<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name']; // Assuming the user ID is stored in the session after login
include('db_connection.php'); // Include the database connection
// List of top 50 companies
$top_companies = [
    "AAPL - Apple", "MSFT - Microsoft", "GOOGL - Alphabet (Google)", "AMZN - Amazon",
    "TSLA - Tesla", "NVDA - NVIDIA", "BRK-B - Berkshire Hathaway", "META - Meta (Facebook)",
    "V - Visa", "JNJ - Johnson & Johnson", "XOM - ExxonMobil", "PG - Procter & Gamble",
    "JPM - JPMorgan Chase", "UNH - UnitedHealth", "HD - Home Depot", "MA - Mastercard",
    "BAC - Bank of America", "PFE - Pfizer", "KO - Coca-Cola", "DIS - Walt Disney",
    "PEP - PepsiCo", "CSCO - Cisco", "ABT - Abbott", "ADBE - Adobe", "NFLX - Netflix",
    "CMCSA - Comcast", "AVGO - Broadcom", "COST - Costco", "T - AT&T", "INTC - Intel",
    "VZ - Verizon", "MRK - Merck", "WMT - Walmart", "ORCL - Oracle", "CVX - Chevron",
    "MS - Morgan Stanley", "NKE - Nike", "LLY - Eli Lilly", "IBM - IBM",
    "HON - Honeywell", "UPS - UPS", "QCOM - Qualcomm", "MDT - Medtronic", "NEE - NextEra Energy",
    "CAT - Caterpillar", "MMM - 3M", "AXP - American Express", "RTX - Raytheon",
    "BA - Boeing", "GE - General Electric"
];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ticker = escapeshellarg($_POST['ticker']);
    $period = escapeshellarg($_POST['period']);
    $strategy = escapeshellarg($_POST['strategy']);
    $command = escapeshellcmd("python analyze_stock.py $ticker $period $strategy");
    $output = shell_exec($command . ' 2>&1');

    if ($output === null) {
        $result_message = "Error: Could not execute analysis script.";
    } else {
        $result_message = nl2br(htmlspecialchars($output));
    }
    
    // Continue with the rest of the analysis...
    $graph_path = 'output_graph.png';
    $graph_available = file_exists($graph_path);
        // Store search in history
    $user_id = $_SESSION['user_id']; // Get the logged-in user ID from session
    $query = "INSERT INTO history (user_id, ticker, period, strategy) VALUES ('$user_id', '$ticker', '$period', '$strategy')";
    mysqli_query($conn, $query);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Market Analysis</title>
    <style>
        /* General Body and Layout */
        body {
            font-family: 'Arial', sans-serif;
            background: #1e1e1e; /* Dark background */
            color: #ffffff; /* Light text */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
        }

        h1 {
            font-size: 40px;
            color: #00bcd4; /* Cyan color for headings */
            text-align: center;
            margin-top: 30px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .container {
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 900px;
        }

        form {
            background: #2a2a2a; /* Slightly lighter dark background */
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 600px;
            margin-bottom: 40px;
            text-align: center;
        }

        label {
            font-size: 16px;
            color: #e0e0e0; /* Light grey text */
            margin-bottom: 10px;
            display: block;
            text-align: left;
            width: 100%;
        }

        input, select, button {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #444444; /* Subtle border for inputs */
            border-radius: 8px;
            font-size: 16px;
            background: #333333; /* Darker input background */
            color: #ffffff; /* White text */
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            border-color: #00bcd4; /* Cyan border on focus */
            box-shadow: 0 0 5px rgba(0, 188, 212, 0.5);
        }

        button {
            background-color: #00bcd4; /* Cyan button */
            color: #1e1e1e; /* Dark text on button */
            font-size: 18px;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #008c9e; /* Darker cyan on hover */
        }

        button:active {
            transform: scale(0.98);
        }

        .tooltip-container {
            position: relative;
        }

        .tooltip-content {
            display: none;
            position: absolute;
            background: #2a2a2a; /* Dark background */
            border: 1px solid #444444; /* Border for tooltip */
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            padding: 10px;
            max-height: 250px;
            overflow-y: auto;
            z-index: 10;
            width: 300px;
            top: calc(100% + 5px);
        }

        .tooltip-content ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .tooltip-content li {
            padding: 8px 12px;
            cursor: pointer;
            font-size: 14px;
            color: #ffffff; /* White text for options */
        }

        .tooltip-content li:hover {
            background: #444444; /* Highlight hovered option */
        }

        .result-section {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            background: #2a2a2a; /* Dark result background */
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 600px;
            color: #ffffff; /* White text */
        }

        .result-section h2 {
            color: #00bcd4; /* Cyan headings */
        }

        .result-section .graph-container img {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 20px;
        }

        @media (max-width: 600px) {
            form {
                width: 90%;
                padding: 20px;
            }

            h1 {
                font-size: 28px;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('ticker');
            const tooltip = document.querySelector('.tooltip-content');
            const tooltipItems = document.querySelectorAll('.tooltip-content li');

            input.addEventListener('focus', () => {
                tooltip.style.display = 'block';
            });

            document.addEventListener('click', (event) => {
                if (!event.target.closest('.tooltip-container')) {
                    tooltip.style.display = 'none';
                }
            });

            tooltipItems.forEach(item => {
                item.addEventListener('click', function () {
                    input.value = this.textContent.split(" - ")[0];
                    tooltip.style.display = 'none';
                });
            });
        });
    </script>
</head>
<body>
<h1>Welcome to Your Dashboard</h1>

    <div class="profile-container">
        <div class="profile-logo">
            <!-- Profile Picture or Icon -->
<h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
            <img src="profile-icon.png" alt="Profile Logo">
        </div>
        <div class="menu">
            <a href="history.php">History</a>
            <a href="change_password.php">Change Password</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    
    <div class="container">
        <h1>Stock Market Analysis</h1>
        <form method="POST" action="">
            <label for="ticker">Stock Ticker:</label>
            <div class="tooltip-container">
                <input type="text" id="ticker" name="ticker" placeholder="e.g., AAPL, TSLA" required>
                <div class="tooltip-content">
                    <ul>
                        <?php foreach ($top_companies as $company): ?>
                            <li><?php echo htmlspecialchars($company); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <label for="period">Select Analysis Period:</label>
            <select id="period" name="period">
                <option value="1mo">1 Month</option>
                <option value="3mo">3 Months</option>
                <option value="6mo">6 Months</option>
                <option value="1y">1 Year</option>
            </select>

            <label for="strategy">Select Strategy:</label>
            <select id="strategy" name="strategy">
                <option value="moving_avg">Moving Averages</option>
                <option value="rsi">RSI</option>
            </select>

            <button type="submit">Analyze</button>
        </form>
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <div class="result-section">
                <h2>Analysis Results</h2>
                <p><b><?php echo $result_message; ?></b></p>
                <?php if ($graph_available): ?>
                    <div class="graph-container">
                        <img src="output_graph.png" alt="Stock Analysis Graph">
                    </div>
                <?php else: ?>
                    <p>No graph available.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
