<?php
// Include the configuration file from the parent directory
include_once 'config.php'; // Corrected path to config.php

// Start session to store messages and user data
session_start();

$message = '';
$messageType = ''; // 'success' or 'error'

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php"); // Redirect to a dashboard or profile page immediately
    exit();
}

// Function to generate SHA1 hash for WoW passwords
// WoW clients use SHA1(UPPERCASE(USERNAME):UPPERCASE(PASSWORD))
function generateWoWHash($username, $password) {
    $s = strtoupper($username) . ":" . strtoupper($password);
    return sha1($s);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = trim($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username_or_email) || empty($password)) {
        $message = "Both username/email and password are required.";
        $messageType = 'error';
    } else {
        $conn_web = null;
        $conn_wow_auth = null; // New connection for WoW Auth DB

        try {
            // Attempt to connect to Website DB (wow_website)
            $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
            $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Attempt to connect to WoW Auth DB (mop_auth)
            $conn_wow_auth = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_WOW_NAME, DB_WOW_USER, DB_WOW_PASS);
            $conn_wow_auth->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $loggedIn = false;

            // --- Attempt 1: Authenticate against web_users table ---
            $stmt_web_user = $conn_web->prepare(
                "SELECT id, username, email, password_hash, role FROM web_users WHERE username = ? OR email = ?"
            );
            $stmt_web_user->execute([$username_or_email, $username_or_email]);
            $user_web = $stmt_web_user->fetch(PDO::FETCH_ASSOC);

            if ($user_web && password_verify($password, $user_web['password_hash'])) {
                // Web user login successful
                $_SESSION['user_id'] = $user_web['id'];
                $_SESSION['username'] = $user_web['username'];
                $_SESSION['email'] = $user_web['email'];
                $_SESSION['role'] = $user_web['role'];
                $loggedIn = true;
            } else {
                // --- Attempt 2: Authenticate against WoW Auth DB if web login failed ---
                // WoW usernames are typically uppercase
                $username_upper = strtoupper($username_or_email);
                $wow_sha_hash = generateWoWHash($username_or_email, $password); // Use original username for hash generation

                $stmt_wow_account = $conn_wow_auth->prepare(
                    "SELECT id, username, email FROM account WHERE username = ? AND sha_pass_hash = ?"
                );
                $stmt_wow_account->execute([$username_upper, $wow_sha_hash]);
                $user_wow = $stmt_wow_account->fetch(PDO::FETCH_ASSOC);

                if ($user_wow) {
                    // WoW account login successful
                    // Check if this WoW user already has a web_users account
                    $stmt_check_web_user_exists = $conn_web->prepare(
                        "SELECT id, username, email, password_hash, role FROM web_users WHERE username = ?"
                    );
                    $stmt_check_web_user_exists->execute([$user_wow['username']]);
                    $existing_web_user = $stmt_check_web_user_exists->fetch(PDO::FETCH_ASSOC);

                    if ($existing_web_user) {
                        // Web user account exists, log them in
                        $_SESSION['user_id'] = $existing_web_user['id'];
                        $_SESSION['username'] = $existing_web_user['username'];
                        $_SESSION['email'] = $existing_web_user['email'];
                        $_SESSION['role'] = $existing_web_user['role'];
                    } else {
                        // WoW account exists, but no web_user account. Create one.
                        $conn_web->beginTransaction();
                        $hashed_web_password = password_hash($password, PASSWORD_BCRYPT); // Hash the plain text password for the website

                        $stmt_create_web_user = $conn_web->prepare(
                            "INSERT INTO web_users (username, email, password_hash, registration_date, last_login_ip, role)
                             VALUES (?, ?, ?, NOW(), ?, 'user')" // Default role 'user'
                        );
                        $current_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                        $stmt_create_web_user->execute([
                            $user_wow['username'],
                            $user_wow['email'] ?? 'unknown@example.com', // Use WoW email, or a fallback
                            $hashed_web_password,
                            $current_ip
                        ]);
                        $new_web_user_id = $conn_web->lastInsertId();
                        $conn_web->commit();

                        $_SESSION['user_id'] = $new_web_user_id;
                        $_SESSION['username'] = $user_wow['username'];
                        $_SESSION['email'] = $user_wow['email'] ?? 'unknown@example.com';
                        $_SESSION['role'] = 'user'; // Newly created users are 'user' by default
                    }
                    $loggedIn = true;
                }
            }

            if ($loggedIn) {
                // Update last login info for the web user (if it was just created or already existed)
                $current_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $stmt_update_login = $conn_web->prepare(
                    "UPDATE web_users SET last_login_ip = ?, last_login_date = NOW() WHERE id = ?"
                );
                $stmt_update_login->execute([$current_ip, $_SESSION['user_id']]);

                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Invalid username/email or password.";
                $messageType = 'error';
            }

        } catch (PDOException $e) {
            // Rollback any pending transactions if an error occurred during multi-DB operations
            if ($conn_web && $conn_web->inTransaction()) {
                $conn_web->rollBack();
            }
            if ($conn_wow_auth && $conn_wow_auth->inTransaction()) {
                $conn_wow_auth->rollBack();
            }
            $message = "Database error: " . $e->getMessage();
            $messageType = 'error';
        } finally {
            $conn_web = null;
            $conn_wow_auth = null;
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
    <title><?php echo $wow_name; ?> - Login</title>
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
                    Login to Your Account
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
                            <a href="dashboard" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-yellow-300">Dashboard</a>
                            <a href="logout" class="block px-4 py-2 text-sm text-red-400 hover:bg-gray-700 hover:text-red-300">Logout</a>
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

    <!-- Main Content Area - Login Form -->
    <main class="container flex justify-center items-center py-12">
        <section class="section-bg p-8 rounded-lg shadow-xl w-full max-w-md">
            <h2 class="text-4xl font-bold text-yellow-200 mb-6 text-center">Login</h2>

            <?php if ($message): ?>
                <div class="p-4 mb-4 rounded-md <?php echo $messageType === 'success' ? 'bg-green-700 text-green-100' : 'bg-red-700 text-red-100'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-6">
                <div>
                    <label for="username_or_email" class="block text-gray-300 text-lg font-bold mb-2">Username or Email:</label>
                    <input type="text" id="username_or_email" name="username_or_email" required
                           class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500"
                           placeholder="Your username or email">
                </div>
                <div>
                    <label for="password" class="block text-gray-300 text-lg font-bold mb-2">Password:</label>
                    <input type="password" id="password" name="password" required
                           class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500"
                           placeholder="Your password">
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="btn-wow w-full py-3 text-xl">Login</button>
                </div>
                <p class="text-center text-gray-400 text-sm mt-4">
                    Don't have an account? <a href="register" class="text-blue-400 hover:underline">Register Here</a>
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
