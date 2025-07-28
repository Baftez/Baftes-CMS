<?php
// Start session to access user login status
session_start();

// Include the configuration file from the same directory
// Corrected path: config.php is now in the same directory as index.php
include_once 'config.php';

// Fallback values in case config.php is not found or variables are not set
if (!isset($wow_name)) {
    $wow_name = "WoW Private Server";
}
if (!isset($IP)) {
    $IP = "pal.baftes.com"; // Default IP/address - this should be overridden by config.php
}
if (!isset($discord)) {
    $discord = "https://discord.com/"; // Default Discord link
}
// Ensure $port_wow is defined, otherwise set a default
if (!isset($port_wow)) {
    $port_wow = "8085"; // Default WoW game port (numeric string)
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

// --- Server Status Check ---
$server_status_text = "Offline";
$status_color_class = "text-red-400"; // Default to red for offline
$crest_glow_class = "crest-glow-red"; // Default crest glow to red for offline
$crest_border_class = "border-red-500"; // Default crest border to red for offline
$js_glow_color = 'rgba(255, 0, 0, 1)'; // Default JavaScript glow color to red for offline

// Convert $port_wow to an integer for fsockopen, safely removing any leading colon if present
$game_port_numeric = (int)ltrim($port_wow, ':');

// Attempt to connect to the game server IP and port
// Using fsockopen with a very short timeout to avoid long delays if server is down
$fp = @fsockopen($IP, $game_port_numeric, $errno, $errstr, 0.5); // 0.5 second timeout

if ($fp) {
    $server_status_text = "Online";
    $status_color_class = "text-green-400";
    $crest_glow_class = "crest-glow-green"; // Set crest glow to green for online
    $crest_border_class = "border-green-500"; // Set crest border to green for online
    $js_glow_color = 'rgba(0, 247, 0, 1)'; // Set JavaScript glow color to green for online
    fclose($fp);
}
// --- End Server Status Check ---


// Fetch online player count
$players_online = 0;
try {
    // Ensure DB_CHAR_NAME is defined in config.php
    if (defined('DB_CHAR_NAME')) {
        $conn_characters = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_CHAR_NAME, DB_WOW_USER, DB_WOW_PASS);
        $conn_characters->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt_online_players = $conn_characters->prepare("SELECT COUNT(*) FROM characters WHERE online = 1");
        $stmt_online_players->execute();
        $players_online = $stmt_online_players->fetchColumn();
    } else {
        $players_online = "N/A (DB_CHAR_NAME not defined)";
        error_log("DB_CHAR_NAME is not defined in config.php. Cannot fetch online players.");
    }
} catch (PDOException $e) {
    // Log the error but don't prevent page load
    error_log("Error fetching online players: " . $e->getMessage());
    $players_online = "N/A (DB Error)"; // Display N/A if there's a database error
} finally {
    $conn_characters = null;
}

// --- Fetch Latest News Articles for Index Page ---
$latest_news_articles = [];
try {
    $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
    $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the latest 2 news posts
    $stmt_latest_news = $conn_web->query("SELECT id, title, content, author, publication_date FROM news ORDER BY publication_date DESC LIMIT 2");
    $latest_news_articles = $stmt_latest_news->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error fetching latest news for index page: " . $e->getMessage());
    // Optionally set a message for the user that news couldn't be loaded
} finally {
    $conn_web = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $wow_name; ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/style.css">
     <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    <style>
        /* Base style for the crest glow animation */
        .crest-glow {
            animation-duration: 2s;
            animation-iteration-count: infinite;
            animation-direction: alternate;
        }

        /* Green glow for online status */
        .crest-glow-green {
            animation-name: pulse-green;
        }

        @keyframes pulse-green {
            0% {
                box-shadow: 0 0 10px 3px rgba(0, 255, 0, 0.4); /* Start with a subtle green glow */
            }
            50% {
                box-shadow: 0 0 25px 8px rgba(0, 255, 0, 0.8); /* Peak glow */
            }
            100% {
                box-shadow: 0 0 10px 3px rgba(0, 255, 0, 0.4); /* Return to subtle glow */
            }
        }

        /* Red glow for offline status */
        .crest-glow-red {
            animation-name: pulse-red;
        }

        @keyframes pulse-red {
            0% {
                box-shadow: 0 0 10px 3px rgba(255, 0, 0, 0.4); /* Start with a subtle red glow */
            }
            50% {
                box-shadow: 0 0 25px 8px rgba(255, 0, 0, 0.8); /* Peak glow */
            }
            100% {
                box-shadow: 0 0 10px 3px rgba(255, 0, 0, 0.4); /* Return to subtle glow */
            }
        }

        /* Custom border color for the crest */
        /* This class is now dynamically applied via PHP */
        /* .border-wow-green {
            border-color: rgb(39 122 23);
        } */

        /* Styles for news articles on index page - similar to news.php but tailored for snippets */
        .news-article {
            background-color: #2a2a2a; /* Dark background for articles */
            border: 1px solid #5a4b3d; /* Subtle border */
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s ease-in-out;
        }

        .news-article:hover {
            transform: translateY(-5px);
        }

        .news-article h3 {
            color: #ffcc00; /* Gold-like color for titles */
            font-size: 2.2rem;
            font-weight: bold;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .news-article .meta {
            color: #b0a08e; /* Lighter brown for metadata */
            font-size: 0.95rem;
            margin-bottom: 15px;
            font-style: italic;
        }

        .news-article .content-display {
            color: #d4c1a7; /* Off-white for content */
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 15px;
            word-wrap: break-word; /* Ensure long words break */
            word-break: break-word; /* More aggressive breaking */
        }

        /* Styles for truncated content on index page */
        .truncated-content {
            max-height: 80px; /* Adjusted to make it smaller */
            overflow: hidden;
            position: relative;
            margin-bottom: 15px;
        }

        .truncated-content::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 50px; /* Fade out effect height */
            background: linear-gradient(to bottom, rgba(42,42,42,0) 0%, rgba(42,42,42,1) 100%);
            pointer-events: none; /* Allows clicking through the overlay */
        }

        .news-article .btn-read-more {
            background-color: #4a90e2; /* Blue for "Read More" */
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.2s ease;
            display: inline-block; /* To allow margin and padding */
            margin-top: 10px;
            text-decoration: none;
        }

        .news-article .btn-read-more:hover {
            background-color: #357ABD;
        }

        /* Basic styling for HTML elements within news content */
        .news-article .content-display strong,
        .news-article .content-display b {
            color: #ffe066; /* Gold for bold text */
        }

        .news-article .content-display em,
        .news-article .content-display i {
            font-style: italic;
            color: #c0c0c0; /* Lighter gray for italic */
        }

        .news-article .content-display u {
            text-decoration: underline;
        }

        .news-article .content-display a {
            color: #63b3ed; /* Blue for links */
            text-decoration: underline;
            font-weight: 600;
        }

        .news-article .content-display img {
            max-width: 100%; /* Ensure images are responsive */
            height: auto;
            display: block; /* Remove extra space below images */
            margin: 15px auto; /* Center images and add vertical space */
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .news-article .content-display ul,
        .news-article .content-display ol {
            list-style-position: inside;
            margin-left: 20px;
            margin-bottom: 15px;
            color: #d4c1a7;
        }

        .news-article .content-display li {
            margin-bottom: 5px;
        }

        .news-article .content-display pre {
            background-color: #1a1a1a;
            border: 1px solid #4a3b2d;
            border-left: 4px solid #ffe066;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            overflow-x: auto;
            font-family: 'Consolas', 'Monaco', monospace;
            color: #e0e0e0;
            font-size: 0.95rem;
        }

        .news-article .content-display blockquote {
            border-left: 4px solid #63b3ed;
            padding-left: 15px;
            margin: 15px 0;
            font-style: italic;
            color: #a0a0a0;
        }

        /* New CSS for glow effect */
        #status {
            position: relative; /* Essential for positioning the canvas absolutely over it */
            overflow: hidden; /* Hide any overflow from the glow */
        }
        #statusGlowCanvas {
            position: absolute; /* Positioned by JS, but good to set initially */
            top: 0;
            left: 0;
            pointer-events: none; /* Allows clicks to pass through to the element below */
            z-index: 1; /* Ensure it's above the background but below content if needed */
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
                    Relive the Adventure!
                </p>
            </div>
            <nav class="flex flex-wrap justify-center gap-4">
                <a href="index" class="btn-wow">Home</a>
                <a href="news" class="btn-wow">News</a>
                <!--<a href="features.php" class="btn-wow">Features</a>-->
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

    <!-- Main Content Area -->
    <main class="container">
        <!-- Server Status & Player Count -->
        <section id="status" class="section-bg p-8 mb-8 text-center rounded-lg border border-gray-700 shadow-md">
            <h2 class="text-3xl font-bold text-yellow-200 mb-4">Server Status</h2>
            <div class="flex flex-col md:flex-row justify-center items-center gap-8">
                <div class="flex flex-col items-center">
                    <div class="text-2xl mb-2">Server: <span class="<?php echo $status_color_class; ?>"><?php echo $server_status_text; ?></span></div>
                    <div class="text-2xl">Players Online: <span class="text-green-400"><?php echo $players_online; ?></span></div>
                </div>
                <!-- Apply dynamic crest glow and border classes -->
                <img src="/images/Pandaren_Crest.png" alt="WoW Crest" class="w-24 h-24 rounded-full border-2 shadow-lg crest-glow <?php echo $crest_glow_class; ?> <?php echo $crest_border_class; ?>">
            </div>
        </section>
        <!-- Canvas for the glow effect around the status section -->
        <canvas id="statusGlowCanvas"></canvas>

        <!-- News & Announcements -->
        <section id="news" class="section-bg p-8 mb-8">
            <h2 class="text-3xl font-bold text-yellow-200 mb-6 text-center">Latest News & Announcements</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php if (!empty($latest_news_articles)): ?>
                    <?php foreach ($latest_news_articles as $article): ?>
                        <div class="news-article">
                            <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                            <p class="meta">Posted on <?php echo date('F j, Y', strtotime($article['publication_date'])); ?> by <?php echo htmlspecialchars($article['author']); ?></p>
                            <div class="content-display truncated-content">
                                <?php echo $article['content']; ?>
                            </div>
                            <a href="news?id=<?php echo htmlspecialchars($article['id']); ?>" class="btn-read-more">Read More</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-400 text-center col-span-full">No news articles found at this time. Please check back later!</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Server Features -->
        <section id="features" class="section-bg p-8 mb-8">
            <h2 class="text-3xl font-bold text-yellow-200 mb-6 text-center">Server Features</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-md">
                    <h3 class="text-2xl font-semibold text-yellow-300 mb-2">Blizzlike Experience</h3>
                    <p class="text-gray-300">
                        Experience World of Warcraft as it was meant to be, with authentic progression and mechanics.
                    </p>
                </div>
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-md">
                    <h3 class="text-2xl font-semibold text-yellow-300 mb-2">Active Community</h3>
                    <p class="text-gray-300">
                        Join a vibrant and welcoming community of fellow adventurers.
                    </p>
                </div>
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-md">
                    <h3 class="text-2xl font-semibold text-yellow-300 mb-2">Dedicated Staff</h3>
                    <p class="text-gray-300">
                        Our passionate GMs are always working to ensure a smooth and fair game.
                    </p>
                </div>
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-md">
                    <h3 class="text-2xl font-semibold text-yellow-300 mb-2">Regular Events</h3>
                    <p class="text-gray-300">
                        Participate in exciting in-game events and win unique rewards.
                    </p>
                </div>
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-md">
                    <h3 class="text-2xl font-semibold text-yellow-300 mb-2">Stable & Secure</h3>
                    <p class="text-gray-300">
                        Enjoy a lag-free and secure environment for your adventures.
                    </p>
                </div>
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-md">
                    <h3 class="text-2xl font-semibold text-yellow-300 mb-2">Custom Content (Optional)</h3>
                    <p class="text-gray-300">
                        Explore unique custom content (if applicable to your server).
                    </p>
                </div>
            </div>
        </section>

        <!-- How to Connect -->
        <!--<section id="connect" class="section-bg p-8 mb-8">
            <h2 class="text-3xl font-bold text-yellow-200 mb-6 text-center">How to Connect</h2>
            <div class="flex flex-col md:flex-row flex-col-reverse-md items-center gap-8">
                <div class="md:w-1/2 text-gray-300 leading-relaxed">
                    <ol class="list-decimal list-inside space-y-3">
                        <h3 class="text-2xl font-semibold text-yellow-200 mb-4">Create or Log In to Your Account:</h3>
                            <p class="mb-4">
                                First, you have to create your <?php echo $wow_name; ?> account. You can do that by clicking the button below. If you already have an account, you can log in.
                            </p>
                            <div class="flex justify-center mb-8">
                                <a href="register." class="btn-wow py-3 px-6 text-xl">Create Account</a>
                            </div>
                        </li>
                        <li>
                            <strong>Locate your realmlist.wtf file:</strong> This file is usually found in your WoW client's Data/enUS (or enGB, etc.) folder.
                        </li>
                        <li>
                            <strong>Edit the realmlist.wtf file:</strong> Open it with Notepad (Windows) or TextEdit (Mac) and change its content to:
                            <pre class="bg-gray-900 p-3 rounded mt-2 text-green-400"><code>set realmlist <?php echo $IP; ?></code></pre>
                            (Replace `<?php echo $IP; ?>` with your actual server's IP address or domain name).
                        </li>
                        <li>
                            <strong>Launch the Game:</strong> Start WoW.exe (or Wow.app on Mac) and log in with your account.
                        </li>
                        <li>
                            <strong>Create an Account:</strong> If you don't have one, register for a game account on our <a href="community.php" class="text-blue-400 hover:underline">community page</a> or directly in-game if auto-creation is enabled.
                        </li>
                    </ol>
                </div>
				
                <div class="md:w-1/2 flex justify-center">
                    <img src="images/chen.jpg" alt="WoW GIF 2" class="rounded-lg shadow-lg border border-gray-700">
                </div>
            </div>
        </section> -->

        <!-- Community & Support -->
        <section id="community" class="section-bg p-8 mb-8 text-center">
            <h2 class="text-3xl font-bold text-yellow-200 mb-6">Join Our Community</h2>
            <p class="text-gray-300 mb-6 leading-relaxed">
                Connect with other players, get support, and stay updated on all server activities!
            </p>
            <div class="flex flex-wrap justify-center gap-6">
                <a href="<?php echo $discord; ?>" target="_blank" class="btn-wow flex items-center gap-2">
                    <!-- Discord Icon (Placeholder) -->
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M20.212 2.212c-1.85-1.5-4.1-2.2-6.5-2.2-2.4 0-4.65.7-6.5 2.2C.962 3.712.212 6.012.212 8.512v9.5c0 1.9 1.6 3.4 3.5 3.4h16.5c1.9 0 3.5-1.5 3.5-3.4v-9.5c0-2.5-1.3-4.8-3.5-6.3zm-3.5 12.5c-.8 0-1.5-.7-1.5-1.5s.7-1.5 1.5-1.5 1.5.7 1.5 1.5-.7 1.5-1.5 1.5zm-7 0c-.8 0-1.5-.7-1.5-1.5s.7-1.5 1.5-1.5 1.5.7 1.5 1.5-.7 1.5-1.5 1.5z"/></svg>
                    Discord
                </a>
                <?php if (!empty($Official_Forums)): ?>
                <a href="<?php echo $Official_Forums; ?>" target="_blank" class="btn-wow flex items-center gap-2">
                    <!-- Forum Icon (Placeholder) -->
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/></svg>
                    Forums
                </a>
                <?php endif; ?>
                <?php if (!empty($Vote_for_Us)): ?>
                <a href="<?php echo $Vote_for_Us; ?>" target="_blank" class="btn-wow flex items-center gap-2">
                    <!-- Vote Icon (Placeholder) -->
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                    Vote
                </a>
                <?php endif; ?>
                <?php if (!empty($Support_the_Server)): ?>
                <a href="<?php echo $Support_the_Server; ?>" target="_blank" class="btn-wow flex items-center gap-2">
                    <!-- Donate Icon (Placeholder) -->
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v5h-2zm0 6h2v2h-2z"/></svg>
                    Donate
                </a>
                <?php endif; ?>
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
    <!-- Link to glowAnimation.js -->
    <script src="/js/glowAnimation.js"></script>
    <script>
        // Initialize the chasing glow for the Server Status box
        // Parameters: canvasId, targetSelector, glowColor, glowThickness, glowBlur, animationSpeed, glowLength
        createChasingGlow(
            'statusGlowCanvas',        // ID of the canvas for the status box glow
            '#status',                 // Selector for the element the glow should wrap
            '<?php echo $js_glow_color; ?>', // Dynamic glow color based on server status
            8,                         // Glow thickness
            10,                        // Glow blur
            0.003,                     // Fixed animation speed
            0.15                       // Length of each glow segment
        );
    </script>
</body>
</html>
         <!-- Credits -->
<!-- Baftes for website template -->