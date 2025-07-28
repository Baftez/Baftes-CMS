<?php
// Start session to access user login status
session_start();

// Define a static site name for the 404 page for robustness.
// Including a full config.php might introduce dependencies that could fail when the server is already in an error state.
$wow_name = "WoW Private Server";

// Check if user is logged in
$is_logged_in = isset($_SESSION['username']);
$username = $is_logged_in ? htmlspecialchars($_SESSION['username']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Page Not Found | <?php echo $wow_name; ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Link to external stylesheet (assuming it's in /css/style.css relative to the root) -->
    <link rel="stylesheet" href="/css/style.css">
    <!-- Favicon (assuming you have one at /favicon.ico) -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <style>
        /* Custom styles for this specific 404 page, ensuring it matches the WoW theme */
        body {
            display: flex;
            flex-direction: column; /* Stack header and main content vertically */
            min-height: 100vh; /* Ensure content takes full viewport height */
            margin: 0;
            padding: 0; /* Padding is handled by container */
            box-sizing: border-box;
        }

        .main-404-content {
            flex-grow: 1; /* Allows this section to take up available space */
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px; /* Add some padding for smaller screens */
        }

        .error-container {
            background-color: rgba(34, 34, 34, 0.9); /* Slightly lighter semi-transparent background */
            border: 2px solid #5a4b3d; /* Dark brown border */
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            padding: 40px;
            text-align: center;
            max-width: 600px; /* Max width for content */
            width: 100%; /* Ensure it's responsive */
        }

        .error-container h1 {
            font-size: 5rem; /* Large 404 */
            font-weight: bold;
            color: #e74c3c; /* Red for error */
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
        }

        .error-container h2 {
            font-size: 2.5rem; /* "Page Not Found" */
            color: #ffe066; /* Gold-like color */
            margin-bottom: 15px;
        }

        .error-container p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
            color: #ccc;
        }

        /* Re-using btn-wow from style.css, but including it here as a fallback/clarity */
        .btn-wow {
            display: inline-block;
            background: linear-gradient(to bottom, #7a634a, #5a4b3d); /* WoW button gradient */
            color: #ffe066; /* Gold text */
            padding: 12px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2rem;
            border: 2px solid #a0845e; /* Lighter border */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4);
            transition: all 0.2s ease;
            text-shadow: 1px 1px 2px #000;
            cursor: pointer;
        }

        .btn-wow:hover {
            background: linear-gradient(to bottom, #8c7155, #6a5b4d); /* Slightly lighter on hover */
            border-color: #b0946e;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.6);
        }

        .btn-wow:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }
        /* Dropdown menu styles (copied from dashboard.php's style block or style.css) */
        .dropdown-menu {
            /* These styles should ideally be in style.css */
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease-in-out;
            transform-origin: top right;
            transform: scale(0.95);
        }

        .group:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
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
                    Page Not Found
                </p>
            </div>
            <nav class="flex flex-wrap justify-center gap-4">
                <a href="/index" class="btn-wow">Home</a>
                <a href="/news" class="btn-wow">News</a>
                <a href="/connect" class="btn-wow">How to Play</a>
                <a href="/community" class="btn-wow">Community</a>

                <?php if ($is_logged_in): ?>
                    <!-- User dropdown if logged in -->
                    <div class="relative group">
                        <button class="btn-wow flex items-center gap-2">
                            <span><?php echo $username; ?></span>
                            <svg class="w-4 h-4 ml-1 transform group-hover:rotate-180 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-gray-800 rounded-md shadow-lg py-1 z-20 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform scale-95 group-hover:scale-100">
                            <a href="/dashboard" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-yellow-300">Dashboard</a>
                            <a href="/logout" class="block px-4 py-2 text-sm text-red-400 hover:bg-gray-700 hover:text-red-300">Logout</a>
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
                            <a href="/login" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-yellow-300">Login</a>
                            <a href="/register" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-yellow-300">Register</a>
                        </div>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Main 404 Content Area -->
    <main class="main-404-content">
        <div class="error-container">
            <h1>403</h1>
            <h2>Page Not Found!</h2>
            <p>
                It seems you've wandered off the beaten path, adventurer!
                The page you're looking for might have been moved, deleted, or perhaps never existed in these lands.
                Fear not, the journey back to safety is but a click away.
            </p>
            <a href="/index" class="btn-wow">Return to the Homepage</a>
        </div>
    </main>

    <!-- Footer Section -->
    <footer class="header-bg py-6 mt-8 rounded-t-lg text-center text-gray-400 text-sm">
        <div class="container">
            <p>&copy; 2025 <?php echo $wow_name; ?>. All rights reserved. World of Warcraft is a registered trademark of Blizzard Entertainment.</p>
        </div>
    </footer>
</body>
</html>
