<?php
// Start session to access user login status
session_start();

// Include the configuration file from the parent directory
// Assumes config.php is in the directory above the web files
include_once 'config.php';

// Fallback values in case config.php is not found or variables are not set
if (!isset($wow_name)) {
    $wow_name = "WoW Private Server";
}
if (!isset($IP)) {
    $IP = "pal.baftes.com"; // Default IP/address
}
if (!isset($discord)) {
    $discord = "https://discord.com/"; // Default Discord link
}

// Fallback values for new community links if not set in config.php
if (!isset($Official_Forums)) {
    $Official_Forums = "";
}
if (!isset($Vote_for_Us)) {
    $Vote_for_Us = "";
}
if (!isset($Support_the_Server)) {
    $Support_the_Server = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $wow_name; ?> - Community</title>
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
                    Join Our Vibrant Community
                </p>
            </div>
            <nav class="flex flex-wrap justify-center gap-4">
                <a href="index" class="btn-wow">Home</a>
                <a href="news" class="btn-wow">News</a>
               <!-- <a href="features" class="btn-wow">Features</a> -->
                <a href="connect" class="btn-wow">How To Play</a>
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

    <!-- Main Content Area - Community Links -->
    <main class="container">
        <section id="community-links" class="section-bg p-8 mb-8">
            <h2 class="text-4xl font-bold text-yellow-200 mb-8 text-center">Connect with Fellow Adventurers!</h2>
            <p class="text-gray-300 text-center mb-10 max-w-3xl mx-auto leading-relaxed">
                Our community is the heart of the server. Join us on our various platforms to chat, get support,
                participate in discussions, and stay up-to-date with all the latest server news and events!
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Discord Card -->
                <div class="community-card">
                    <div class="icon text-6xl mb-4">
                        <!-- Discord Icon (Font Awesome or SVG) -->
                        <svg class="w-20 h-20 mx-auto" fill="currentColor" viewBox="0 0 24 24"><path d="M20.212 2.212c-1.85-1.5-4.1-2.2-6.5-2.2-2.4 0-4.65.7-6.5 2.2C.962 3.712.212 6.012.212 8.512v9.5c0 1.9 1.6 3.4 3.5 3.4h16.5c1.9 0 3.5-1.5 3.5-3.4v-9.5c0-2.5-1.3-4.8-3.5-6.3zm-3.5 12.5c-.8 0-1.5-.7-1.5-1.5s.7-1.5 1.5-1.5 1.5.7 1.5 1.5-.7 1.5-1.5 1.5zm-7 0c-.8 0-1.5-.7-1.5-1.5s.7-1.5 1.5-1.5 1.5.7 1.5 1.5-.7 1.5-1.5 1.5z"/></svg>
                    </div>
                    <h3>Discord Server</h3>
                    <p>
                        Join our Discord for real-time chat, voice channels, support, and direct communication with staff and players.
                    </p>
                    <a href="<?php echo $discord; ?>" target="_blank" class="btn-wow">Join Discord</a>
                </div>

                <?php if (!empty($Official_Forums)): ?>
                <!-- Forums Card -->
                <div class="community-card">
                    <div class="icon text-6xl mb-4">
                        <!-- Forum Icon (Font Awesome or SVG) -->
                        <svg class="w-20 h-20 mx-auto" fill="currentColor" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/></svg>
                    </div>
                    <h3>Official Forums</h3>
                    <p>
                        Visit our forums for in-depth discussions, guides, bug reports, suggestions, and official announcements.
                    </p>
                    <a href="<?php echo $Official_Forums; ?>" target="_blank" class="btn-wow">Go to Forums</a>
                </div>
                <?php endif; ?>

                <?php if (!empty($Vote_for_Us)): ?>
                <!-- Vote Card -->
                <div class="community-card">
                    <div class="icon text-6xl mb-4">
                        <!-- Vote Icon (Font Awesome or SVG) -->
                        <svg class="w-20 h-20 mx-auto" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                    </div>
                    <h3>Vote for Us!</h3>
                    <p>
                        Support our server by voting daily on top private server lists. Earn rewards and help us grow!
                    </p>
                    <a href="<?php echo $Vote_for_Us; ?>" target="_blank" class="btn-wow">Vote Now</a>
                </div>
                <?php endif; ?>

                <?php if (!empty($Support_the_Server)): ?>
                <!-- Donate Card -->
                <div class="community-card">
                    <div class="icon text-6xl mb-4">
                        <!-- Donate Icon (Font Awesome or SVG) -->
                        <svg class="w-20 h-20 mx-auto" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v5h-2zm0 6h2v2h-2z"/></svg>
                    </div>
                    <h3>Support the Server</h3>
                    <p>
                        Consider donating to help us cover server costs and fund future development. All donations are greatly appreciated!
                    </p>
                    <a href="<?php echo $Support_the_Server; ?>" target="_blank" class="btn-wow">Donate Here</a>
                </div>
                <?php endif; ?>

                <!-- Account Registration Card -->
                <div class="community-card">
                    <div class="icon text-6xl mb-4">
                        <!-- Account Icon (Font Awesome or SVG) -->
                        <svg class="w-20 h-20 mx-auto" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </div>
                    <h3>Account Registration</h3>
                    <p>
                        Create your game account here to start your adventure on our private server!
                    </p>
                    <a href="register" target="_blank" class="btn-wow">Register Now</a>
                </div>

                <!-- In-Game Rules Card -->
                <div class="community-card">
                    <div class="icon text-6xl mb-4">
                        <!-- Rules Icon (Font Awesome or SVG) -->
                        <svg class="w-20 h-20 mx-auto" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5z"/></svg>
                    </div>
                    <h3>Server Rules</h3>
                    <p>
                        Familiarize yourself with our server rules to ensure a fair and enjoyable experience for everyone.
                    </p>
                    <a href="rules" target="_blank" class="btn-wow">Read Rules</a>
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
