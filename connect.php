<?php
// Start session to access user login status
session_start();

// Include the configuration file from the same directory
// Assumes config.php is in the same directory as this file
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

// Fallback values for download links if not set in config.php
// These should be empty strings by default so they don't show up if not configured.
if (!isset($download_windows)) {
    $download_windows = "";
}
if (!isset($download_mac)) {
    $download_mac = "";
}
if (!isset($download_torrent)) {
    $download_torrent = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $wow_name; ?> - How To Play</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/style.css">
     <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    <style>
        /* Custom styles for the download cards */
        .download-card {
            background-color: #2a2a2a;
            border: 1px solid #5a4b3d;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between; /* Distribute content vertically */
        }

        .download-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
        }

        .download-card img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #4a3b2d;
        }

        .download-card h3 {
            color: #ffcc00;
            font-size: 1.75rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .download-card p {
            color: #d4c1a7;
            font-size: 1rem;
            margin-bottom: 20px;
            flex-grow: 1; /* Allow paragraph to take available space */
        }

        .download-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%; /* Ensure buttons take full width of card */
        }

        .download-buttons .btn-wow {
            width: 100%;
            padding: 10px 15px;
            font-size: 1rem;
        }

        /* Specific styles for the dropdown buttons */
        .dropdown-download-btn {
            position: relative; /* Keep relative for containing the button */
            display: inline-block;
            width: 100%; /* Make the button take full width */
        }

        .dropdown-download-content {
            /* Removed position: absolute, top, left, z-index to make it part of the flow */
            background-color: #1a1a1a; /* Darker background for dropdown */
            min-width: 100%;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.6);
            border-radius: 6px;
            overflow: hidden; /* Ensures rounded corners apply to children */
            margin-top: 5px; /* Small gap between button and dropdown */
            
            /* Control visibility and transition with max-height */
            max-height: 0; /* Hidden state */
            opacity: 0;
            visibility: hidden;
            transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;

            /* Added for vertical stacking of links */
            display: flex;
            flex-direction: column;
        }

        /* Class to show dropdown with transition */
        .dropdown-download-content.show {
            max-height: 500px; /* Sufficiently large value to show all content */
            opacity: 1;
            visibility: visible;
        }

        /* Style for individual links within the dropdown */
        .dropdown-download-content a {
            color: #d4c1a7; /* Text color for links */
            padding: 10px 15px;
            text-decoration: none;
            display: block; /* Make links take full width and stack */
            text-align: left; /* Align text to the left */
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .dropdown-download-content a:hover {
            background-color: #3a3a3a; /* Darker hover background */
            color: #ffcc00; /* Gold-like color on hover */
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
                    Join the Adventure!
                </p>
            </div>
            <nav class="flex flex-wrap justify-center gap-4">
                <a href="index" class="btn-wow">Home</a>
                <a href="news" class="btn-wow">News</a>
                <!-- <a href="features.php" class="btn-wow">Features</a> -->
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

    <!-- Main Content Area - How to Connect -->
    <main class="container">
        <section id="connect-guide" class="section-bg p-8 mb-8">
            <h2 class="text-4xl font-bold text-yellow-200 mb-8 text-center">How to Connect to Our Server</h2>
            <div class="flex flex-col items-center gap-10">
                <div class="w-full text-gray-300 leading-relaxed connect-guide">
                    <ol class="space-y-6">
                        <li>
                            <h3 class="text-2xl font-semibold text-yellow-200 mb-4">Create or Log In to Your Account:</h3>
                            <p class="mb-4">
                                First, you have to create your <?php echo $wow_name; ?> account. You can do that by clicking the button below. If you already have an account, you can log in.
                            </p>
                            <div class="flex justify-center mb-8">
                                <a href="register" class="btn-wow py-3 px-6 text-xl">Create Account</a>
                            </div>
                        </li>

                        <li>
                            <h3 class="text-2xl font-semibold text-yellow-200 mb-4">Download the Game Client:</h3>
                            <p class="mb-4">
                                Ensure you have the correct World of Warcraft client version for our server. We primarily support the **Mists of Pandaria (MoP) 5.4.8** client. If you don't have it, you can download it directly from the options below. Make sure it's a clean client without modifications from other servers.
                            </p>

                            <!-- Download Client Section -->
                            <div class="flex justify-center mb-8"> <!-- Changed from grid to flex for centering -->
                                <!-- MoP 5.4.8 Client Card -->
                                <div class="download-card max-w-sm w-full"> <!-- Added max-w-sm for smaller size and w-full for responsiveness -->
                                    <img src="images/1108027.jpg" alt="Mists of Pandaria Client">
                                    <h3>Mists of Pandaria (MoP) 5.4.8</h3>
                                    
                                    <div class="dropdown-download-btn" id="mop-download-btn-container">
                                        <button class="btn-wow flex items-center justify-center gap-2 w-full" id="mop-download-button">
                                            Download Options
                                            <svg class="w-4 h-4 ml-1 transform group-hover:rotate-180 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                        <div class="dropdown-download-content" id="mop-dropdown-content">
                                            <?php if (!empty($download_windows)): ?>
                                                <a href="<?php echo $download_windows; ?>">Full Client (Windows)</a>
                                            <?php endif; ?>
                                            <?php if (!empty($download_mac)): ?>
                                                <a href="<?php echo $download_mac; ?>">Full Client (Mac)</a>
                                            <?php endif; ?>
                                            <?php if (!empty($download_torrent)): ?>
                                                <a href="<?php echo $download_torrent; ?>">Torrent Download</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- WotLK 3.3.5a Client Card (Commented out as requested) -->
                                <!--
                                <div class="download-card">
                                    <img src="https://placehold.co/300x180/2a2a2a/ffe066?text=WotLK+3.3.5a+Client" alt="Wrath of the Lich King Client">
                                    <h3>Wrath of the Lich King (WotLK) 3.3.5a</h3>
                                    <p>For those who prefer the frozen wastes of Northrend. (Note: May not be fully supported on all servers).</p>
                                    <div class="dropdown-download-btn">
                                        <button class="btn-wow flex items-center justify-center gap-2 w-full">
                                            Download Options
                                            <svg class="w-4 h-4 ml-1 transform group-hover:rotate-180 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                        <div class="dropdown-download-content">
                                            <a href="#">Full Client (Windows)</a>
                                            <a href="#">Full Client (Mac)</a>
                                            <a href="#">Minimal Client</a>
                                            <a href="#">Torrent Download</a>
                                        </div>
                                    </div>
                                </div>
                                -->
                            </div>
                          
                        </li>

                        <li>
                            <h3 class="text-2xl font-semibold text-yellow-200 mb-4">Locate your `Config.wtf` file:</h3>
                            <p class="mb-4">
                                Navigate to your WoW client's installation directory. Inside, you'll typically find a `WTF` folder, and within that. The `Config.wtf` file is located here.
                            </p>
                            <pre class="bg-gray-900 p-3 rounded mt-2 text-green-400"><code>World of Warcraft 5.4.8 pandaria/WTF/Config.wtf</code></pre>
                        </li>
                        <li>
                            <h3 class="text-2xl font-semibold text-yellow-200 mb-4">Edit the `Config.wtf` file:</h3>
                            <p class="mb-4">
                                Open `Config.wtf` using a plain text editor like Notepad (Windows), TextEdit (Mac), or Notepad++ (recommended). You need to ensure the following lines are present and correctly set to your server's IP. If they don't exist, add them. If they do, modify them:
                            </p>
                            <pre class="bg-gray-900 p-3 rounded mt-2 text-green-400"><code>SET realmList "<?php echo $IP; ?>"</code></pre>
                            <p class="text-yellow-400 font-bold mt-2">
                                This step is vital for MoP clients to correctly receive server information
                            </p>
                        </li>
                        <li>
                            <h3 class="text-2xl font-semibold text-yellow-200 mb-4">Launch the Game:</h3>
                            <p class="mb-4">
                                Once you've saved the changes to `Config.wtf`, close the files. Now, launch the game by running `WoW.exe` (on Windows) or `Wow.app` (on Mac) from your main WoW installation directory. 
                            </p>
                        </li>
                        <li>
                            <h3 class="text-2xl font-semibold text-yellow-200 mb-4">Troubleshooting (Optional):</h3>
                            <p class="mb-4">
                                If you encounter issues, please check:
                            </p>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>Your internet connection.</li>
                                <li>Firewall settings (ensure WoW and its ports are allowed).</li>
                                <li>That your `realmlist.wtf` and `Config.wtf` are saved correctly and point to the right address.</li>
                                <li>Our <a href="<?php echo $discord; ?>" class="text-blue-400 hover:underline">Discord</a> or Forums for common issues and solutions.</li>
                            </ul>
                        </li>
                    </ol>
                </div>
            </div>
            <div class="text-center mt-8">
                <p class="text-gray-400 mt-4">Still having trouble? Visit our <a href="community" class="text-blue-400 hover:underline">Community page</a> for support!</p>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mopButtonContainer = document.getElementById('mop-download-btn-container');
            const mopDropdownContent = document.getElementById('mop-dropdown-content');
            let hideTimeout;

            // Function to show the dropdown
            function showDropdown() {
                clearTimeout(hideTimeout); // Clear any pending hide timeouts
                mopDropdownContent.classList.add('show');
            }

            // Function to hide the dropdown with a delay
            function hideDropdown() {
                hideTimeout = setTimeout(() => {
                    mopDropdownContent.classList.remove('show');
                }, 500); // Adjust delay (in milliseconds) as needed, e.g., 500ms for half a second
            }

            // Event listeners for the button container (which includes the button and dropdown)
            if (mopButtonContainer) {
                mopButtonContainer.addEventListener('mouseenter', showDropdown);
                mopButtonContainer.addEventListener('mouseleave', hideDropdown);

                // This ensures that clicking the button still works as intended
                mopButtonContainer.querySelector('button').addEventListener('click', function(event) {
                    event.stopPropagation(); // Prevent click from propagating to document
                    // Toggle dropdown visibility on click
                    if (mopDropdownContent.classList.contains('show')) {
                        mopDropdownContent.classList.remove('show');
                    } else {
                        showDropdown();
                    }
                });

                // Close dropdown if clicking anywhere else on the document
                document.addEventListener('click', function(event) {
                    if (!mopButtonContainer.contains(event.target)) {
                        mopDropdownContent.classList.remove('show');
                    }
                });
            }
        });
    </script>
</body>
</html>
