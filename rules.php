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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $wow_name; ?> - Server Rules</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/style.css">
     <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    <style>
        .rules-section {
            background-color: #2a2a2a; /* Dark background for the section */
            border: 1px solid #5a4b3d; /* Subtle border */
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .rule-item {
            background-color: #3a3a3a; /* Slightly lighter background for each rule */
            border-left: 4px solid #ffcc00; /* Gold-like accent border */
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .rule-item h3 {
            color: #ffe066; /* Gold-like color for rule titles */
            font-size: 1.75rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .rule-item p {
            color: #d4c1a7; /* Off-white for rule descriptions */
            font-size: 1.05rem;
            line-height: 1.6;
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
                    Server Rules & Guidelines
                </p>
            </div>
            <nav class="flex flex-wrap justify-center gap-4">
                <a href="index" class="btn-wow">Home</a>
                <a href="news" class="btn-wow">News</a>
                <!-- <a href="features.php" class="btn-wow">Features</a> -->
                <a href="connect" class="btn-wow">Connect</a>
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

    <!-- Main Content Area - Server Rules -->
    <main class="container">
        <section id="server-rules" class="rules-section mb-8">
            <h2 class="text-4xl font-bold text-yellow-200 mb-8 text-center">Our Server Rules</h2>
            <p class="text-gray-300 text-center mb-10 max-w-3xl mx-auto leading-relaxed">
                To ensure a fair, fun, and respectful environment for all players, please adhere to the following server rules.
                Violation of these rules may result in warnings, temporary bans, or permanent account suspension.
            </p>

            <div class="space-y-6">
                <div class="rule-item">
                    <h3>1. Respect All Players and Staff</h3>
                    <p>Treat everyone with respect. Harassment, hate speech, discrimination, excessive profanity, or any form of abusive behavior will not be tolerated. This applies to in-game chat, whispers, mail, and any associated community platforms (e.g., Discord, forums).</p>
                </div>

                <div class="rule-item">
                    <h3>2. No Exploiting or Cheating</h3>
                    <p>The use of any third-party programs, hacks, bots, or exploits that give an unfair advantage is strictly forbidden. This includes, but is not limited to, speed hacks, teleport hacks, duping, or abusing game bugs. Report any exploits you find to the staff immediately.</p>
                </div>

                <div class="rule-item">
                    <h3>3. Fair Play and PvP Conduct</h3>
                    <p>While PvP is encouraged, win-trading, griefing (repeatedly killing low-level players without purpose, corpse camping for extended periods), or any other behavior designed solely to ruin another player's experience is not allowed. Play fair and respect the spirit of competition.</p>
                </div>

                <div class="rule-item">
                    <h3>4. Account Security</h3>
                    <p>You are responsible for your account's security. Do not share your account information (username, password) with anyone. Staff will never ask for your password. Report any suspicious activity on your account immediately.</p>
                </div>

                <div class="rule-item">
                    <h3>5. Naming Conventions</h3>
                    <p>Character names, guild names, and pet names must be appropriate. Names that are offensive, racist, sexually explicit, or impersonate staff members are forbidden. Staff reserve the right to change inappropriate names without warning.</p>
                </div>

                <div class="rule-item">
                    <h3>6. No Real-Money Trading (RMT)</h3>
                    <p>Selling or buying in-game items, gold, or accounts for real-world currency or other external goods/services is strictly prohibited. This includes trading game assets for assets on other servers.</p>
                </div>

                <div class="rule-item">
                    <h3>7. Advertisement</h3>
                    <p>Do not advertise other private servers, websites, or services unrelated to <?php echo $wow_name; ?> in any in-game chat channel or on our community platforms.</p>
                </div>

                <div class="rule-item">
                    <h3>8. Staff Decisions Are Final</h3>
                    <p>Game Masters and Administrators have the final say in all in-game and community matters. Arguing with staff decisions in public channels or attempting to circumvent their authority will lead to disciplinary action.</p>
                </div>
            </div>

            <p class="text-gray-400 text-center mt-10">
                By playing on <?php echo $wow_name; ?>, you agree to abide by these rules. We reserve the right to modify these rules at any time.
                For any questions or to report a violation, please contact our staff on <a href="<?php echo $discord; ?>" class="text-blue-400 hover:underline">Discord</a> or through our forums.
            </p>
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
