<?php
// Start session to access user login status
session_start();

// Include the configuration file from the parent directory
// Assumes config.php is in the directory above the web files
include_once 'config.php';

// Fallback values in case config.php is not found or variables are not set
if (!isset($wow_name)) { // Using $wow_name as per config.php
    $wow_name = "WoW Private Server";
}
if (!isset($IP)) {
    $IP = "your.server.address.com"; // Default IP/address
}
if (!isset($discord)) {
    $discord = "https://discord.com/"; // Default Discord link
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $wow_name; ?> - Features</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/style.css">
     <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
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
                    Discover Our Unique Features
                </p>
            </div>
            <nav class="flex flex-wrap justify-center gap-4">
                <a href="index.php" class="btn-wow">Home</a>
                <a href="news.php" class="btn-wow">News</a>
                <!--<a href="features.php" class="btn-wow">Features</a>-->
                <a href="connect.php" class="btn-wow">Connect</a>
                <a href="community.php" class="btn-wow">Community</a>

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

    <!-- Main Content Area - Features List -->
    <main class="container">
        <section id="features-list" class="section-bg p-8 mb-8">
            <h2 class="text-4xl font-bold text-yellow-200 mb-8 text-center">Server Features</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature Item 1 -->
                <div class="feature-item">
                    <img src="https://placehold.co/100x100/000000/FFFFFF?text=Blizzlike" alt="Blizzlike Icon" class="mx-auto mb-4 rounded-full border-2 border-yellow-500">
                    <h3>Blizzlike Experience</h3>
                    <p>
                        Relive the classic World of Warcraft experience with meticulously recreated game mechanics, rates, and content, true to the original.
                    </p>
                </div>

                <!-- Feature Item 2 -->
                <div class="feature-item">
                    <img src="https://placehold.co/100x100/000000/FFFFFF?text=Community" alt="Community Icon" class="mx-auto mb-4 rounded-full border-2 border-yellow-500">
                    <h3>Thriving Community</h3>
                    <p>
                        Join a friendly and active community of players. Engage in discussions, form guilds, and embark on adventures together.
                    </p>
                </div>

                <!-- Feature Item 3 -->
                <div class="feature-item">
                    <img src="https://placehold.co/100x100/000000/FFFFFF?text=Staff" alt="Staff Icon" class="mx-auto mb-4 rounded-full border-2 border-yellow-500">
                    <h3>Dedicated & Responsive Staff</h3>
                    <p>
                        Our passionate Game Masters and developers are committed to providing a fair, stable, and enjoyable environment. We're here to help!
                    </p>
                </div>

                <!-- Feature Item 4 -->
                <div class="feature-item">
                    <img src="https://placehold.co/100x100/000000/FFFFFF?text=Events" alt="Events Icon" class="mx-auto mb-4 rounded-full border-2 border-yellow-500">
                    <h3>Regular In-Game Events</h3>
                    <p>
                        Participate in exciting custom events, PvP tournaments, and holiday celebrations with unique rewards and challenges.
                    </p>
                </div>

                <!-- Feature Item 5 -->
                <div class="feature-item">
                    <img src="https://placehold.co/100x100/000000/FFFFFF?text=Stability" alt="Stability Icon" class="mx-auto mb-4 rounded-full border-2 border-yellow-500">
                    <h3>High Stability & Performance</h3>
                    <p>
                        Enjoy a smooth, lag-free gameplay experience on our optimized and regularly maintained server infrastructure.
                    </p>
                </div>

                <!-- Feature Item 6 -->
                <div class="feature-item">
                    <img src="https://placehold.co/100x100/000000/FFFFFF?text=Anti-Cheat" alt="Anti-Cheat Icon" class="mx-auto mb-4 rounded-full border-2 border-yellow-500">
                    <h3>Robust Anti-Cheat System</h3>
                    <p>
                        We employ advanced anti-cheat measures to ensure fair play and a level playing field for all adventurers.
                    </p>
                </div>

                <!-- Feature Item 7 (Example of a more specific feature) -->
                <div class="feature-item">
                    <img src="https://placehold.co/100x100/000000/FFFFFF?text=Transmog" alt="Transmog Icon" class="mx-auto mb-4 rounded-full border-2 border-yellow-500">
                    <h3>Custom Transmogrification (Optional)</h3>
                    <p>
                        Express your unique style with our custom transmogrification system, allowing you to customize your gear's appearance.
                    </p>
                </div>

                <!-- Feature Item 8 (Example of another specific feature) -->
                <div class="feature-item">
                    <img src="https://placehold.co/100x100/000000/FFFFFF?text=Cross-Faction" alt="Cross-Faction Icon" class="mx-auto mb-4 rounded-full border-2 border-yellow-500">
                    <h3>Cross-Faction Battlegrounds (Optional)</h3>
                    <p>
                        Experience faster queues and more dynamic PvP with our cross-faction battleground system.
                    </p>
                </div>
            </div>

            
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
