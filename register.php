<?php
// Start session to access user login status
session_start();

// Include the configuration file from the parent directory
// Assumes config.php is in the directory above the web files
include_once 'config.php'; // Corrected path to config.php

// Function to generate SHA1 hash for WoW passwords
// WoW clients use SHA1(UPPERCASE(USERNAME):UPPERCASE(PASSWORD))
function generateWoWHash($username, $password) {
    $s = strtoupper($username) . ":" . strtoupper($password);
    return sha1($s);
}

$message = '';
$messageType = ''; // 'success' or 'error'

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Input validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "All fields are required.";
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $messageType = 'error';
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $messageType = 'error';
    } else {
        // Attempt to connect to both databases
        $conn_wow = null;
        $conn_web = null;

        try {
            // Connect to WoW Auth DB (mop_auth)
            $conn_wow = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_WOW_NAME, DB_WOW_USER, DB_WOW_PASS);
            $conn_wow->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Connect to Website DB (wow_website)
            $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
            $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Start a transaction for both databases to ensure atomicity
            $conn_wow->beginTransaction();
            $conn_web->beginTransaction();

            // 1. Check if username or email already exists in WoW Auth DB
            $stmt_wow_check = $conn_wow->prepare("SELECT COUNT(*) FROM account WHERE username = ? OR email = ?");
            $stmt_wow_check->execute([strtoupper($username), $email]);
            if ($stmt_wow_check->fetchColumn() > 0) {
                $message = "Username or email already exists in the WoW game database.";
                $messageType = 'error';
                $conn_wow->rollBack();
                $conn_web->rollBack();
            } else {
                // 2. Check if username or email already exists in Website DB
                $stmt_web_check = $conn_web->prepare("SELECT COUNT(*) FROM web_users WHERE username = ? OR email = ?");
                $stmt_web_check->execute([$username, $email]);
                if ($stmt_web_check->fetchColumn() > 0) {
                    $message = "Username or email already exists in the website database.";
                    $messageType = 'error';
                    $conn_wow->rollBack();
                    $conn_web->rollBack();
                } else {
                    // Generate hashes
                    $wow_sha_hash = generateWoWHash($username, $password);
                    $web_password_hash = password_hash($password, PASSWORD_BCRYPT);
                    $current_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

                    // Insert into WoW Auth DB
                    $stmt_wow_insert = $conn_wow->prepare(
                        "INSERT INTO account (username, sha_pass_hash, email, joindate, last_ip, expansion)
                         VALUES (?, ?, ?, NOW(), ?, 4)" // Assuming expansion 4 for MoP
                    );
                    $stmt_wow_insert->execute([strtoupper($username), $wow_sha_hash, $email, $current_ip]);

                    // Get the last inserted ID from WoW Auth DB for potential linking
                    $wow_account_id = $conn_wow->lastInsertId();

                    // Insert into Website DB
                    $stmt_web_insert = $conn_web->prepare(
                        "INSERT INTO web_users (username, email, password_hash, registration_date, last_login_ip)
                         VALUES (?, ?, ?, NOW(), ?)"
                    );
                    $stmt_web_insert->execute([$username, $email, $web_password_hash, $current_ip]);

                    // Commit both transactions
                    $conn_wow->commit();
                    $conn_web->commit();

                    // Set session variables for the newly registered user and redirect to dashboard
                    $_SESSION['user_id'] = $conn_web->lastInsertId(); // Get the ID from web_users
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;

                    header("Location: dashboard.php"); // Redirect immediately after successful registration
                    exit();
                }
            }
        } catch (PDOException $e) {
            // Rollback transactions if any error occurs
            if ($conn_wow && $conn_wow->inTransaction()) {
                $conn_wow->rollBack();
            }
            if ($conn_web && $conn_web->inTransaction()) {
                $conn_web->rollBack();
            }
            $message = "Database error: " . $e->getMessage();
            $messageType = 'error';
        } finally {
            // Close connections
            $conn_wow = null;
            $conn_web = null;
        }
    }
}

// Set default values for wow_name and IP if not defined in config.php
if (!isset($wow_name)) {
    $wow_name = "WoW Private Server";
}
if (!isset($IP)) {
    $IP = "pal.baftes.com";
}
if (!isset($discord)) {
    $discord = "https://discord.com/";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $wow_name; ?> - Register</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/style.css">
     <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Ensure body takes at least full viewport height */
        }
        main {
            flex-grow: 1; /* Allow main content to grow and push footer down */
        }
    </style>
</head>
<body class="antialiased">
    <!-- Header Section -->
    <header class="header-bg py-6 mb-8 rounded-b-lg">
        <div class="container flex flex-col md:flex-row justify-between items-center">
            <div class="text-center md:text-left mb-4 md:mb-0">
                <h1 class="text-5xl font-bold text-yellow-300 mb-2 leading-tight">
                    <?php echo $wow_name; ?>
                </h1>
                <p class="text-xl text-gray-300">
                    Create Your Account
                </p>
            </div>
            <nav class="flex flex-wrap justify-center gap-4">
                <a href="index" class="btn-wow">Home</a>
                <a href="news" class="btn-wow">News</a>
                <!--<a href="features.php" class="btn-wow">Features</a>-->
                <a href="connect" class="btn-wow">how to play</a>
                <a href="community" class="btn-wow">Community</a>

                <?php if (isset($_SESSION['username'])): ?>
                    <!-- User dropdown if logged in -->
                    <div class="relative group">
                        <button class="btn-wow flex items-center gap-2">
                            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <svg class="w-4 h-4 ml-1 transform group-hover:rotate-180 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-gray-800 rounded-md shadow-lg py-1 z-20 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform scale-95 group-hover:scale-100">
                            <a href="dashboard.php" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-yellow-300">Dashboard</a>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-400 hover:bg-gray-700 hover:text-red-300">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Login/Register dropdown if not logged in -->
                    <div class="relative group">
                        <button class="btn-wow flex items-center gap-2">
                            <span>Account</span>
                            <svg class="w-4 h-4 ml-1 transform group-hover:rotate-180 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-gray-800 rounded-md shadow-lg py-1 z-20 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform scale-95 group-hover:scale-100">
                            <a href="login" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-yellow-300">Login</a>
                            <a href="register" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-yellow-300">Register</a>
                        </div>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Main Content Area - Registration Form -->
    <main class="container flex justify-center items-center py-12">
        <section class="section-bg p-8 rounded-lg shadow-xl w-full max-w-md">
            <h2 class="text-4xl font-bold text-yellow-200 mb-6 text-center">Register New Account</h2>

            <?php if ($message): ?>
                <div class="p-4 mb-4 rounded-md <?php echo $messageType === 'success' ? 'bg-green-700 text-green-100' : 'bg-red-700 text-red-100'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-gray-300 text-lg font-bold mb-2">Username:</label>
                    <input type="text" id="username" name="username" required
                           class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500"
                           placeholder="Choose a username">
                </div>
                <div>
                    <label for="email" class="block text-gray-300 text-lg font-bold mb-2">Email:</label>
                    <input type="email" id="email" name="email" required
                           class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500"
                           placeholder="Enter your email">
                </div>
                <div>
                    <label for="password" class="block text-gray-300 text-lg font-bold mb-2">Password:</label>
                    <input type="password" id="password" name="password" required
                           class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500"
                           placeholder="Enter your password">
                </div>
                <div>
                    <label for="confirm_password" class="block text-gray-300 text-lg font-bold mb-2">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500"
                           placeholder="Confirm your password">
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="btn-wow w-full py-3 text-xl">Register Account</button>
                </div>
                <p class="text-center text-gray-400 text-sm mt-4">
                    Already have an account? <a href="login" class="text-blue-400 hover:underline">Log In Here</a>
                </p>
            </form>
        </section>
    </main>

    <!-- Footer Section -->
    <footer class="header-bg py-6 mt-8 rounded-t-lg text-center text-gray-400 text-sm">
        <div class="container">
            <p>&copy; 2025 <?php echo $wow_name; ?>. All rights reserved. World of Warcraft is a registered trademark of Blizzard Entertainment.</p>
        </div>
    </footer>
    <!-- Link to external JavaScript file -->
    <script src="/js/script.js"></script>
</body>
</html>
