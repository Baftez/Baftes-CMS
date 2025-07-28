<?php
// Include the PHP logic for the dashboard.
// This file is assumed to be in a 'logic' subfolder relative to dashboard.php
require_once 'dashboard_logic.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $wow_name; ?> - Dashboard</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/style.css">
     <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    <style>
        /* Style for the details arrow rotation */
        details[open] .details-arrow {
            transform: rotate(180deg);
        }
        /* Ensure GM level select and button align nicely */
        .gm-level-form {
            display: flex;
            align-items: center;
            gap: 0.5rem; /* Space between elements */
        }

        /* General Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place relative to viewport */
            z-index: 1000; /* High z-index for general modals */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0, 0, 0, 0.7); /* Black w/ opacity */
            /* Use flexbox for centering content */
            display: flex; /* Initially hidden, but flex container properties are set */
            justify-content: center;
            align-items: center;
        }

        /* This class makes the modal visible */
        .modal:not(.active) {
            display: none !important; /* Force hide if not active, important to override other rules */
        }
        .modal.active {
            display: flex !important; /* Force show when active, important to override other rules */
        }

        .modal-content {
            background-color: #2a2a2a; /* Dark background for modal */
            margin: auto; /* Auto margins help with centering when display is block/flex */
            padding: 30px;
            border: 1px solid #5a4b3d;
            border-radius: 8px;
            width: 80%; /* Adjust width as needed */
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            text-align: center;
            color: #d4c1a7;
        }

        .modal-content h3 {
            color: #ffcc00; /* Gold-like color for modal title */
            margin-bottom: 15px;
        }

        .modal-content p {
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .btn-confirm {
            background-color: #dc2626; /* Red-600 */
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.2s;
        }

        .btn-confirm:hover {
            background-color: #b91c1c; /* Red-700 */
        }

        .btn-cancel {
            background-color: #4b5563; /* Gray-600 */
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.2s;
        }

        .btn-cancel:hover {
            background-color: #374151; /* Gray-700 */
        }

        /* Toolbar styles for News Editor */
        .toolbar {
            background-color: #3a3a3a;
            border: 1px solid #5a4b3d;
            border-radius: 6px;
            padding: 8px;
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .toolbar-btn {
            background-color: #5a4b3d;
            color: #d4c1a7;
            padding: 6px 10px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease; /* Added box-shadow transition */
            border: none;
            font-size: 0.9rem;
            white-space: nowrap; /* Prevent text wrapping on buttons */
        }

        .toolbar-btn:hover {
            background-color: #6a5b4d;
            color: #ffe066;
        }

        /* Style for active toolbar buttons */
        .toolbar-btn.active {
            background-color: #ffcc00; /* Gold color for active state */
            color: #2a2a2a; /* Dark text for contrast */
            box-shadow: 0 0 8px rgba(255, 204, 0, 0.6); /* Subtle glow */
        }

        /* Styling for contenteditable div */
        .content-editable-div {
            min-height: 250px; /* Make the edit area larger */
            border: 1px solid #5a4b3d;
            border-radius: 4px;
            padding: 10px;
            background-color: #222; /* Darker background for content */
            color: #d4c1a7; /* Text color */
            outline: none; /* Remove default focus outline */
            overflow-y: auto; /* Enable scrolling if content overflows */
            /* New styles for text wrapping */
            white-space: normal; /* Ensures text wraps */
            word-wrap: break-word; /* Breaks long words if necessary */
        }

        /* Adjust table cell for snippet content */
        .admin-table td:nth-child(3) { /* Targeting the content snippet column */
            max-width: 300px; /* Limit width to prevent overly wide columns */
            overflow: hidden;
            text-overflow: ellipsis; /* Add ellipsis for overflow */
            white-space: normal; /* Allow text to wrap within the max-width */
        }

        /* Emoji Picker Modal Styles */
        .emoji-picker-modal {
            z-index: 9999; /* Even higher z-index for the emoji picker */
        }
        /* The .modal:not(.active) and .modal.active rules will handle display */

        .emoji-picker-content {
            background-color: #2a2a2a;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #5a4b3d;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.7);
            width: 95%; /* Take more width on very small screens */
            max-width: 300px; /* Reduced max-width for a more compact box */
            max-height: 70vh; /* Reduced max-height slightly */
            overflow-y: auto;
            text-align: center;
            position: relative;
        }

        .emoji-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(32px, 1fr)); /* Adjusted minmax for better fit */
            gap: 4px; /* Slightly smaller gap */
            margin-top: 15px;
        }

        .emoji-item {
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: background-color 0.1s ease;
        }

        .emoji-item:hover {
            background-color: #5a4b3d;
        }

        .emoji-picker-close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #d4c1a7;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .emoji-picker-close-btn:hover {
            color: #ffcc00;
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
                    Your Account Dashboard
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

    <!-- Main Content Area - Dashboard -->
    <main class="dashboard-main flex"> <!-- Added flex here to ensure sidebar and content are side-by-side -->
        <div class="container flex flex-grow"> <!-- This container already has flex, which is good -->
            <!-- Sidebar -->
            <aside class="sidebar">
                <div>
                    <h3 class="text-2xl font-bold text-yellow-200 mb-6">Dashboard</h3>
                    <nav class="space-y-2">
                        <a data-tab="account-details" class="sidebar-nav-item active">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Account Details
                        </a>
                        <a data-tab="my-characters" class="sidebar-nav-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sword"><path d="M14.5 17.5 3 6 3 3l3 3 11.5 11.5c.88.88 2.22 1.06 3.22.44 1.01-.62 1.2-1.96.32-2.84l-1.5-1.5z"/><path d="m17.5 14.5 2 2"/></svg>
                            My Characters
                        </a>
                        <?php if ($is_admin): ?>
                            <a data-tab="admin-panel" class="sidebar-nav-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-check"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                            Admin Panel
                            </a>
                            <a data-tab="manage-news" class="sidebar-nav-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-newspaper"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h2"/><path d="M10 12h8"/><path d="M10 18h8"/><path d="M10 6h4"/></svg>
                                News Editor
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
                <div class="mt-8">
                    <a href="logout" class="sidebar-nav-item text-red-400 hover:bg-red-700 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        Logout
                    </a>
                </div>
            </aside>

            <!-- Content Area -->
            <div class="content-area section-bg rounded-lg shadow-xl">
                <h2 class="text-4xl font-bold text-yellow-200 mb-6 text-center">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Adventurer'); ?>!</h2>

                <?php if ($message): ?>
                    <div id="flash-message" class="p-4 mb-4 rounded-md <?php echo $messageType === 'success' ? 'bg-green-700 text-green-100' : 'bg-red-700 text-red-100'; ?>"
                        data-message-type="<?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Account Details Section -->
                <section id="account-details" class="content-section active mb-8">
                    <h3 class="text-3xl font-semibold text-yellow-300 mb-4 text-left">Your Account Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- User Info -->
                        <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-md">
                            <h4 class="text-xl font-semibold text-yellow-200 mb-4">Personal Information</h4>
                            <div class="space-y-3">
                                <div class="detail-item bg-gray-700 bg-opacity-50 rounded-md px-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <p class="text-gray-200 flex-1"><strong>Username:</strong> <span class="text-yellow-100"><?php echo htmlspecialchars($_SESSION['username'] ?? 'N/A'); ?></span></p>
                                </div>
                                <div class="detail-item bg-gray-700 bg-opacity-50 rounded-md px-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.93 1.93 0 0 1-2.06 0L2 7"/></svg>
                                    <p class="text-gray-200 flex-1"><strong>Email:</strong> <span class="text-yellow-100"><?php echo htmlspecialchars($_SESSION['email'] ?? 'N/A'); ?></span></p>
                                </div>
                                <div class="detail-item bg-gray-700 bg-opacity-50 rounded-md px-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-fingerprint"><path d="M2 12C2 6.48 6.48 2 12 2s10 4.48 10 10c0 2.24-0.8 4.3-2.16 5.86M12.24 22c-2.24 0-4.3-0.8-5.86-2.16M16 12a4 4 0 0 1-8 0"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="M22 12h-2"/><path d="M4 12H2"/><path d="M18.5 5.5l-1.42 1.42"/><path d="M6.92 17.08L5.5 18.5"/><path d="M5.5 5.5l1.42 1.42"/><path d="M17.08 17.08l1.42 1.42"/></svg>
                                    <p class="text-gray-200 flex-1"><strong>Website User ID:</strong> <span class="text-yellow-100"><?php echo htmlspecialchars($_SESSION['user_id'] ?? 'N/A'); ?></span></p>
                                </div>
                                <div class="detail-item bg-gray-700 bg-opacity-50 rounded-md px-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin"><path d="M12 12v-2"/><path d="M12 2c-3.31 0-6 2.69-6 6c0 4.5 6 14 6 14s6-9.5 6-14c0-3.31-2.69-6-6-6z"/><circle cx="12" cy="8" r="2"/></svg>
                                    <p class="text-gray-200 flex-1"><strong>Last Login IP:</strong> <span class="text-yellow-100"><?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'N/A'); ?></span></p>
                                </div>
                                <div class="detail-item bg-gray-700 bg-opacity-50 rounded-md px-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-clock"><path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7.5"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/><circle cx="18" cy="18" r="4"/><path d="M18 16v2l1 1"/></svg>
                                    <p class="text-gray-200 flex-1"><strong>Last Login Date:</strong> <span class="text-yellow-100"><?php echo date('Y-m-d H:i:s'); ?></span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Change Password Form -->
                        <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-md">
                            <h4 class="text-xl font-semibold text-yellow-200 mb-4">Change Your Password</h4>
                            <form action="dashboard" method="POST" class="space-y-4">
                                <div>
                                    <label for="current_password" class="block text-gray-300 text-lg font-bold mb-2">Current Password:</label>
                                    <input type="password" id="current_password" name="current_password" required
                                           class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500">
                                </div>
                                <div>
                                    <label for="new_password" class="block text-gray-300 text-lg font-bold mb-2">New Password:</label>
                                    <input type="password" id="new_password" name="new_password" required
                                           class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500">
                                </div>
                                <div>
                                    <label for="confirm_new_password" class="block text-gray-300 text-lg font-bold mb-2">Confirm New Password:</label>
                                    <input type="password" id="confirm_new_password" name="confirm_new_password" required
                                           class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500">
                                </div>
                                <button type="submit" name="change_password" class="btn-wow w-full py-3 text-xl">Change Password</button>
                            </form>
                        </div>
                    </div>
                </section>

                <!-- My Characters Section -->
                <section id="my-characters" class="content-section mb-8 mt-12">
                    <h3 class="text-3xl font-semibold text-yellow-300 mb-4 text-left">My Characters</h3>
                    <?php if (!empty($wow_characters)): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($wow_characters as $char):
                                // Convert money from copper to Gold, Silver, Copper
                                $total_copper = $char['money'];
                                $gold = floor($total_copper / 10000);
                                $silver = floor(($total_copper % 10000) / 100);
                                $copper = $total_copper % 100;
                            ?>
                                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-md character-card">
                                    <!-- Character Race Icon - Commented out for now -->
                                    <!-- <div class="character-icon <?php //echo getRaceIconClass($char['race'], $char['gender'], $race_sprite_map); ?>"></div> -->
                                    <div>
                                        <h4 class="text-xl font-semibold text-yellow-200 mb-2"><?php echo htmlspecialchars($char['name']); ?></h4>
                                        <p class="text-gray-300">Level: <span class="text-yellow-100"><?php echo htmlspecialchars($char['level']); ?></span></p>
                                        <p class="text-gray-300">Race: <span class="text-yellow-100"><?php echo htmlspecialchars($race_sprite_map[$char['race']]['name'] ?? 'Unknown'); ?></span></p>
                                        <p class="text-gray-300">Class: <span class="text-yellow-100"><?php echo htmlspecialchars($char['class']); ?></span></p>
                                        <p class="text-gray-300">Gender: <span class="text-yellow-100"><?php echo htmlspecialchars($char['gender'] == 0 ? 'Male' : 'Female'); ?></span></p>
                                        <p class="text-gray-300">Gold: <span class="text-yellow-100"><?php echo $gold; ?>g <?php echo $silver; ?>s <?php echo $copper; ?>c</span></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-400">No characters found for this account. Create one in-game!</p>
                    <?php endif; ?>
                </section>

                <!-- Admin Panel Section (Conditionally Visible) -->
                <?php if ($is_admin): ?>
                    <section id="admin-panel" class="content-section mb-8 mt-12">
                        <h3 class="text-3xl font-semibold text-red-400 mb-4 text-left">Admin Panel</h3>

                        <!-- Admin Sub-navigation -->
                        <div class="flex flex-wrap gap-4 mb-6">
                            <!-- Swapped order: WoW Characters first, then Website Users -->
                            <button data-admin-tab="manage-wow-characters" class="btn-wow btn-admin-sub-nav">Manage WoW Accounts & Characters</button>
                            <button data-admin-tab="manage-website-users" class="btn-wow btn-admin-sub-nav">Manage Website Users</button>
                            <!-- News Editor button moved to main sidebar -->
                        </div>

                        <!-- Manage WoW Accounts & Characters Sub-section (Default active for admin panel) -->
                        <div id="manage-wow-characters" class="admin-sub-section">
                            <h4 class="text-2xl font-semibold text-yellow-300 mb-4">WoW Accounts & Characters</h4>

                            <!-- Search Bar for WoW Accounts (Live Search) -->
                            <div class="mb-6 flex gap-2">
                                <input type="text" id="wowAccountSearchInput" placeholder="Search by account username..."
                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500">
                            </div>

                            <?php if (!empty($all_wow_accounts_with_chars)): ?>
                                <div class="space-y-4" id="wow-accounts-list">
                                    <?php foreach ($all_wow_accounts_with_chars as $account_entry): ?>
                                        <details class="bg-gray-800 rounded-lg border border-gray-700 shadow-md" data-account-username="<?php echo htmlspecialchars(strtolower($account_entry['username'])); ?>" data-account-id="<?php echo htmlspecialchars($account_entry['id']); ?>">
                                            <summary class="p-4 cursor-pointer flex justify-between items-center text-yellow-200 font-bold text-xl">
                                                <div class="flex-grow flex items-center">
                                                    <span>Account: <?php echo htmlspecialchars($account_entry['username']); ?> (ID: <?php echo htmlspecialchars($account_entry['id']); ?>)</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <form id="form-wow-account-gmlevel-<?php echo $account_entry['id']; ?>" action="dashboard" method="POST" class="gm-level-form">
                                                        <input type="hidden" name="account_id" value="<?php echo $account_entry['id']; ?>">
                                                        <input type="hidden" name="admin_tab" value="manage-wow-characters">
                                                        <label for="gm_level_<?php echo $account_entry['id']; ?>" class="text-gray-300 text-base font-normal whitespace-nowrap">GM Level:</label>
                                                        <select name="gm_level_<?php echo $account_entry['id']; ?>" id="gm_level_<?php echo $account_entry['id']; ?>" class="bg-gray-700 border border-gray-600 rounded px-2 py-1 text-sm text-white focus:outline-none focus:border-yellow-500">
                                                            <option value="0" <?php echo ($account_entry['gmlevel'] == 0) ? 'selected' : ''; ?>>Player</option>
                                                            <option value="5" <?php echo ($account_entry['gmlevel'] == 5) ? 'selected' : ''; ?>>GM (5)</option>
                                                            <option value="6" <?php echo ($account_entry['gmlevel'] == 6) ? 'selected' : ''; ?>>GM (6)</option>
                                                            <option value="7" <?php echo ($account_entry['gmlevel'] == 7) ? 'selected' : ''; ?>>GM (7)</option>
                                                            <option value="8" <?php echo ($account_entry['gmlevel'] == 8) ? 'selected' : ''; ?>>GM (8)</option>
                                                            <option value="9" <?php echo ($account_entry['gmlevel'] == 9) ? 'selected' : ''; ?>>Admin (9)</option>
                                                        </select>
                                                        <button type="submit" name="update_wow_account_gmlevel" class="btn-wow btn-edit text-sm px-2 py-1">Update GM</button>
                                                    </form>
                                                    <button type="button" class="btn-wow btn-delete text-sm px-2 py-1" onclick="event.stopPropagation(); showModal('delete-wow-account-modal', <?php echo $account_entry['id']; ?>)">Delete Account</button>
                                                </div>
                                                <svg class="w-6 h-6 transform details-arrow transition-transform duration-200 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </summary>
                                            <div class="p-4 border-t border-gray-700">
                                                <?php if (!empty($account_entry['characters'])): ?>
                                                    <h5 class="text-xl font-semibold text-gray-300 mb-3">Characters:</h5>
                                                    <div class="overflow-x-auto">
                                                        <table class="admin-table w-full">
                                                            <thead>
                                                                <tr>
                                                                    <th>GUID</th>
                                                                    <th>Name</th>
                                                                    <th>Level</th>
                                                                    <th>Gold</th>
                                                                    <th>Race</th>
                                                                    <th>Class</th>
                                                                    <th>Gender</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($account_entry['characters'] as $char):
                                                                    $total_copper = $char['money'];
                                                                    $gold = floor($total_copper / 10000);
                                                                    $silver = floor(($total_copper % 10000) / 100);
                                                                    $copper = $total_copper % 100;
                                                                ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($char['guid']); ?></td>
                                                                        <td><?php echo htmlspecialchars($char['name']); ?></td>
                                                                        <td>
                                                                            <input type="number" name="level_<?php echo $char['guid']; ?>" value="<?php echo htmlspecialchars($char['level']); ?>" min="1" max="90" form="form-wow-char-<?php echo $char['guid']; ?>" class="bg-gray-700 text-gray-200 p-1 rounded text-sm w-20">
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" name="gold_<?php echo htmlspecialchars($char['guid']); ?>" value="<?php echo htmlspecialchars(round($char['money'] / 10000)); ?>" min="0" form="form-wow-char-<?php echo $char['guid']; ?>" class="bg-gray-700 text-gray-200 p-1 rounded text-sm w-24">
                                                                        </td>
                                                                        <td><?php echo htmlspecialchars($race_sprite_map[$char['race']]['name'] ?? 'Unknown'); ?></td>
                                                                        <td><?php echo htmlspecialchars($char['class']); ?></td>
                                                                        <td><?php echo htmlspecialchars($char['gender'] == 0 ? 'Male' : 'Female'); ?></td>
                                                                        <td class="flex flex-wrap gap-2">
                                                                            <form id="form-wow-char-<?php echo $char['guid']; ?>" action="dashboard" method="POST">
                                                                                <input type="hidden" name="guid" value="<?php echo $char['guid']; ?>">
                                                                                <input type="hidden" name="update_wow_character" value="1">
                                                                                <input type="hidden" name="admin_tab" value="manage-wow-characters">
                                                                                <button type="submit" class="btn-wow btn-edit">Update</button>
                                                                                <button type="button" class="btn-wow btn-delete" onclick="event.stopPropagation(); showModal('delete-wow-character-modal', <?php echo $char['guid']; ?>)">Delete</button>
                                                                            </form>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <p class="text-gray-400">No characters found for this account.</p>
                                                <?php endif; ?>
                                            </div>
                                        </details>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-400">No WoW accounts found.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Manage Website Users Sub-section -->
                        <div id="manage-website-users" class="admin-sub-section">
                            <h4 class="text-2xl font-semibold text-yellow-300 mb-4">Website Users</h4>
                            <!-- Search Bar for Website Users (Live Search) -->
                            <div class="mb-6 flex gap-2">
                                <input type="text" id="websiteUserSearchInput" placeholder="Search by username or email..."
                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500">
                            </div>
                            <?php if (!empty($website_users)): ?>
                                <div class="overflow-x-auto">
                                    <table class="admin-table" id="website-users-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Registered</th>
                                                <th>Last Login IP</th>
                                                <th>Last Login Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($website_users as $user): ?>
                                                <tr data-username="<?php echo htmlspecialchars(strtolower($user['username'])); ?>" data-email="<?php echo htmlspecialchars(strtolower($user['email'])); ?>">
                                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                                    <td>
                                                        <input type="text" name="username_<?php echo $user['id']; ?>" value="<?php echo htmlspecialchars($user['username']); ?>" form="form-web-user-<?php echo $user['id']; ?>" class="bg-gray-700 text-gray-200 p-1 rounded text-sm w-32">
                                                    </td>
                                                    <td>
                                                        <input type="email" name="email_<?php echo $user['id']; ?>" value="<?php echo htmlspecialchars($user['email']); ?>" form="form-web-user-<?php echo $user['id']; ?>" class="bg-gray-700 text-gray-200 p-1 rounded text-sm w-48">
                                                    </td>
                                                    <td>
                                                        <select name="role_<?php echo $user['id']; ?>" form="form-web-user-<?php echo $user['id']; ?>" class="bg-gray-700 text-gray-200 p-1 rounded text-sm w-24">
                                                            <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                                                            <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                                        </select>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($user['registration_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['last_login_ip'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($user['last_login_date'] ?? 'N/A'); ?></td>
                                                    <td class="flex flex-wrap gap-2">
                                                        <form id="form-web-user-<?php echo $user['id']; ?>" action="dashboard" method="POST">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                            <input type="hidden" name="admin_tab" value="manage-website-users">
                                                            <button type="submit" name="update_web_user" class="btn-wow btn-edit">Update</button>
                                                            <button type="submit" name="reset_web_password" class="btn-wow btn-reset-pass">Reset Pass</button>
                                                            <button type="button" class="btn-wow btn-delete" onclick="event.stopPropagation(); showModal('delete-web-user-modal', <?php echo $user['id']; ?>)">Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-400">No website users found.</p>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- News Editor Section (Now a top-level section) -->
                    <section id="manage-news" class="content-section mb-8 mt-12">
                        <h3 class="text-3xl font-semibold text-yellow-300 mb-4 text-left">News Editor</h3>

                        <!-- Add New News Post Form (initially visible) -->
                        <div id="add-news-form-container" class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-md mb-8">
                            <h5 class="text-xl font-semibold text-yellow-200 mb-4">Add New News Post</h5>
                            <!-- Toolbar for Add Form -->
                            <div class="toolbar" id="toolbar-add">
                                <!-- Buttons will be generated by JavaScript -->
                            </div>
                            <form action="dashboard" method="POST" class="space-y-4" onsubmit="copyContentToHiddenInput('news_content_add_editor', 'news_content_html_add')">
                                <input type="hidden" name="add_news" value="1">
                                <input type="hidden" name="admin_tab" value="manage-news"> <!-- Keep admin_tab for redirect -->
                                <div>
                                    <label for="news_title" class="block text-gray-300 text-lg font-bold mb-2">Title:</label>
                                    <input type="text" id="news_title" name="news_title" required
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500">
                                </div>
                                <div>
                                    <label for="news_content_add_editor" class="block text-gray-300 text-lg font-bold mb-2">Content:</label>
                                    <!-- Content editable div for rich text editing -->
                                    <div id="news_content_add_editor" contenteditable="true" class="content-editable-div"></div>
                                    <!-- Hidden input to store the HTML content for submission -->
                                    <input type="hidden" name="news_content_html" id="news_content_html_add">
                                </div>
                                <button type="submit" class="btn-wow w-full py-2">Add News Post</button>
                            </form>
                        </div>

                        <!-- Edit News Post Form (initially hidden) -->
                        <div id="edit-news-form-container" class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-md mb-8" style="display: none;">
                            <h5 class="text-xl font-semibold text-yellow-200 mb-4">Edit News Post</h5>
                            <!-- Toolbar for Edit Form -->
                            <div class="toolbar" id="toolbar-edit">
                                <!-- Buttons will be generated by JavaScript -->
                            </div>
                            <form action="dashboard" method="POST" class="space-y-4" onsubmit="copyContentToHiddenInput('edit_news_content_editor', 'news_content_html_edit')">
                                <input type="hidden" name="news_id" id="edit_news_id">
                                <input type="hidden" name="update_news" value="1">
                                <input type="hidden" name="admin_tab" value="manage-news">
                                <div>
                                    <label for="edit_news_title" class="block text-gray-300 text-lg font-bold mb-2">Title:</label>
                                    <input type="text" id="edit_news_title" name="news_title" required
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500">
                                </div>
                                <div>
                                    <label for="edit_news_content_editor" class="block text-gray-300 text-lg font-bold mb-2">Content:</label>
                                    <!-- Content editable div for rich text editing -->
                                    <div id="edit_news_content_editor" contenteditable="true" class="content-editable-div"></div>
                                    <!-- Hidden input to store the HTML content for submission -->
                                    <input type="hidden" name="news_content_html" id="news_content_html_edit">
                                </div>
                                <div class="flex gap-4">
                                    <button type="submit" class="btn-wow w-full py-2 btn-edit">Update News Post</button>
                                    <button type="button" class="btn-wow w-full py-2 btn-cancel" onclick="cancelEditNews()">Cancel Edit</button>
                                </div>
                            </form>
                        </div>

                        <!-- List Existing News Posts -->
                        <h5 class="text-xl font-semibold text-yellow-200 mb-4">Existing News Posts</h5>
                        <?php if (!empty($all_news_posts)): ?>
                            <div class="overflow-x-auto">
                                <table class="admin-table w-full">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Content Snippet</th>
                                            <th>Author</th>
                                            <th>Date</th>
                                            <th style="min-width: 150px;">Actions</th> <!-- Added min-width to ensure space for buttons -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_news_posts as $post): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($post['id']); ?></td>
                                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                                <!-- Display the HTML content directly for rich text -->
                                                <td><?php echo mb_strimwidth($post['content'], 0, 100, '...'); ?></td> <!-- Display snippet, HTML will be rendered -->
                                                <td><?php echo htmlspecialchars($post['author']); ?></td>
                                                <td><?php echo htmlspecialchars($post['publication_date']); ?></td>
                                                <td class="flex flex-wrap gap-2">
                                                    <button type="button" class="btn-wow btn-edit"
                                                            onclick="openEditNewsForm(<?php echo $post['id']; ?>, '<?php echo addslashes(htmlspecialchars($post['title'], ENT_QUOTES)); ?>', '<?php echo addslashes(str_replace(["\r", "\n"], '', htmlspecialchars($post['content'], ENT_QUOTES))); ?>')">
                                                        Edit
                                                    </button>
                                                    <button type="button" class="btn-wow btn-delete" onclick="event.stopPropagation(); showModal('delete-news-modal', <?php echo $post['id']; ?>)">Delete</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-400">No news posts found.</p>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Emoji Picker Modal -->
    <div id="emoji-picker-modal" class="modal emoji-picker-modal">
        <div class="modal-content emoji-picker-content">
            <button type="button" class="emoji-picker-close-btn" onclick="closeEmojiPicker()">&times;</button>
            <h3 class="text-2xl mb-4">Select an Emoji</h3>
            <div class="emoji-grid" id="emoji-grid">
                <!-- Emojis will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Modals for Confirmation -->
    <div id="delete-web-user-modal" class="modal">
        <div class="modal-content">
            <h3 class="text-2xl">Confirm User Deletion</h3>
            <p>Are you sure you want to delete this website user? This action cannot be undone.</p>
            <div class="modal-buttons">
                <form action="dashboard" method="POST" id="confirm-delete-web-user-form">
                    <input type="hidden" name="user_id" id="delete-web-user-id">
                    <input type="hidden" name="delete_web_user" value="1">
                    <input type="hidden" name="admin_tab" value="manage-website-users">
                    <button type="submit" class="btn-confirm">Delete User</button>
                </form>
                <button type="button" class="btn-cancel" onclick="closeModal('delete-web-user-modal')">Cancel</button>
            </div>
        </div>
    </div>

    <div id="delete-wow-character-modal" class="modal">
        <div class="modal-content">
            <h3 class="text-2xl">Confirm Character Deletion</h3>
            <p>Are you sure you want to delete this WoW character? This action cannot be undone.</p>
            <div class="modal-buttons">
                <form action="dashboard" method="POST" id="confirm-delete-wow-character-form">
                    <input type="hidden" name="guid" id="delete-wow-character-guid">
                    <input type="hidden" name="delete_wow_character" value="1">
                    <input type="hidden" name="admin_tab" value="manage-wow-characters">
                    <button type="submit" class="btn-confirm">Delete Character</button>
                </form>
                <button type="button" class="btn-cancel" onclick="closeModal('delete-wow-character-modal')">Cancel</button>
            </div>
        </div>
    </div>

    <div id="delete-wow-account-modal" class="modal">
        <div class="modal-content">
            <h3 class="text-2xl">Confirm WoW Account Deletion</h3>
            <p>Are you sure you want to delete this WoW account and <strong>ALL</strong> its associated characters and data? This action cannot be undone.</p>
            <div class="modal-buttons">
                <form action="dashboard" method="POST" id="confirm-delete-wow-account-form">
                    <input type="hidden" name="account_id" id="delete-wow-account-id">
                    <input type="hidden" name="delete_wow_account" value="1">
                    <input type="hidden" name="admin_tab" value="manage-wow-characters">
                    <button type="submit" class="btn-confirm">Delete WoW Account</button>
                </form>
                <button type="button" class="btn-cancel" onclick="closeModal('delete-wow-account-modal')">Cancel</button>
            </div>
        </div>
    </div>

    <div id="delete-news-modal" class="modal">
        <div class="modal-content">
            <h3 class="text-2xl">Confirm News Deletion</h3>
            <p>Are you sure you want to delete this news post? This action cannot be undone.</p>
            <div class="modal-buttons">
                <form action="dashboard" method="POST" id="confirm-delete-news-form">
                    <input type="hidden" name="news_id" id="delete-news-id">
                    <input type="hidden" name="delete_news" value="1">
                    <input type="hidden" name="admin_tab" value="manage-news">
                    <button type="submit" class="btn-confirm">Delete News</button>
                </form>
                <button type="button" class="btn-cancel" onclick="closeModal('delete-news-modal')">Cancel</button>
            </div>
        </div>
    </div>


    <!-- Footer Section -->
    <footer class="header-bg py-6 mt-8 rounded-t-lg text-center text-gray-400 text-sm">
        <div class="container">
            <p>&copy; 2025 <?php echo $wow_name; ?>. All rights reserved. World of Warcraft is a registered trademark of Blizzard Entertainment.</p>
        </div>
    </footer>
    <script>
        // Global variable to keep track of the currently active contenteditable editor
        let activeContentEditor = null;
        let currentRange = null; // To store the last selection range for emoji insertion

        // Modal functions (global scope)
        function showModal(modalId, idToPass) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                // Set the ID for the hidden input in the form
                if (modalId === 'delete-web-user-modal') {
                    document.getElementById('delete-web-user-id').value = idToPass;
                } else if (modalId === 'delete-wow-character-modal') {
                    document.getElementById('delete-wow-character-guid').value = idToPass;
                } else if (modalId === 'delete-wow-account-modal') {
                    document.getElementById('delete-wow-account-id').value = idToPass;
                } else if (modalId === 'delete-news-modal') { // Added for news deletion
                    document.getElementById('delete-news-id').value = idToPass;
                }
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
            }
        }

        // Close modal if clicked outside (optional, but good UX)
        window.addEventListener('click', function(event) {
            // Get all currently active modals
            const activeModals = document.querySelectorAll('.modal.active');

            activeModals.forEach(modal => {
                const modalContent = modal.querySelector('.modal-content');
                
                // Check if the click occurred outside the modal's content area
                // This covers clicks on the semi-transparent background as well as anywhere else on the page
                if (modalContent && !modalContent.contains(event.target)) {
                    // Add a specific check to prevent closing if the click was on the "Emoji" button itself.
                    // This is a failsafe in case stopPropagation isn't fully effective in all scenarios,
                    // though stopPropagation on the button is the primary fix.
                    let isEmojiButton = false;
                    let tempTarget = event.target;
                    while (tempTarget && tempTarget !== document.body) {
                        if (tempTarget.classList.contains('toolbar-btn') && tempTarget.textContent === 'Emoji') {
                            isEmojiButton = true;
                            break;
                        }
                        tempTarget = tempTarget.parentNode;
                    }

                    if (!isEmojiButton) {
                        closeModal(modal.id);
                        console.log(`${modal.id} closed by outside click (target: ${event.target.id || event.target.tagName}).`);
                    } else {
                        console.log(`Click on Emoji button, preventing immediate close of ${modal.id}.`);
                    }
                }
            });
        });

        // --- Rich Text Editor Functions ---

        /**
         * Executes a document.execCommand for rich text formatting.
         * @param {string} command - The command to execute (e.g., 'bold', 'italic', 'underline').
         * @param {string} [value=null] - The value for the command (e.g., URL for 'createLink').
         */
        function formatDoc(command, value = null) {
            if (activeContentEditor) {
                activeContentEditor.focus(); // Ensure the active editor is focused
                document.execCommand(command, false, value);
                updateToolbarState(activeContentEditor.id); // Update state after command
            }
        }

        /**
         * Inserts an image into the contenteditable div.
         * Prompts the user for an image URL.
         */
        function insertImage() {
            const url = prompt('Enter the image URL:');
            if (url) {
                formatDoc('insertImage', url);
            }
        }

        /**
         * Inserts a link into the contenteditable div.
         * Prompts the user for a URL.
         */
        function insertLink() {
            const url = prompt('Enter the URL:');
            if (url) {
                formatDoc('createLink', url);
            }
        }

        /**
         * Clears all formatting from the selected text or entire content.
         * This uses 'removeFormat' which clears most inline styles and tags.
         */
        function clearFormatting() {
            if (activeContentEditor) {
                activeContentEditor.focus();
                document.execCommand('removeFormat', false, null); // Remove all inline formatting
                document.execCommand('formatBlock', false, 'P'); // Ensure it's a paragraph block
                updateToolbarState(activeContentEditor.id);
            }
        }

        /**
         * Copies the HTML content from a contenteditable div to a hidden input before form submission.
         * @param {string} editorId - The ID of the contenteditable div.
         * @param {string} hiddenInputId - The ID of the hidden input field.
         */
        function copyContentToHiddenInput(editorId, hiddenInputId) {
            const editorDiv = document.getElementById(editorId);
            const hiddenInput = document.getElementById(hiddenInputId);
            if (editorDiv && hiddenInput) {
                hiddenInput.value = editorDiv.innerHTML;
            }
        }

        /**
         * Opens the news editing form and populates it with the selected article's data.
         * @param {number} id - The ID of the news article.
         * @param {string} title - The title of the news article.
         * @param {string} content - The HTML content of the news article.
         */
        function openEditNewsForm(id, title, content) {
            document.getElementById('add-news-form-container').style.display = 'none'; // Hide add form
            const editFormContainer = document.getElementById('edit-news-form-container');
            editFormContainer.style.display = 'block'; // Show edit form

            // Populate the edit form fields
            document.getElementById('edit_news_id').value = id;
            document.getElementById('edit_news_title').value = title;
            document.getElementById('edit_news_content_editor').innerHTML = content; // Set innerHTML for rich text

            // Setup toolbar for the edit editor
            setupToolbar('edit_news_content_editor');

            // Scroll to the edit form for better UX
            editFormContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        /**
         * Hides the news editing form and shows the add news form.
         * Clears the edit form fields.
         */
        function cancelEditNews() {
            document.getElementById('edit-news-form-container').style.display = 'none'; // Hide edit form
            document.getElementById('add-news-form-container').style.display = 'block'; // Show add form

            // Clear edit form fields
            document.getElementById('edit_news_id').value = '';
            document.getElementById('edit_news_title').value = '';
            document.getElementById('edit_news_content_editor').innerHTML = ''; // Clear rich text content

            // Re-initialize toolbar for the add editor
            setupToolbar('news_content_add_editor');
        }

        /**
         * Updates the active state of toolbar buttons based on the current selection's formatting.
         * @param {string} editorId - The ID of the contenteditable div.
         */
        function updateToolbarState(editorId) {
            const toolbarContainer = document.getElementById(editorId.replace('_editor', '').replace('news_content_add', 'toolbar-add').replace('edit_news_content', 'toolbar-edit'));
            if (!toolbarContainer) return;

            const buttons = toolbarContainer.querySelectorAll('.toolbar-btn');

            buttons.forEach(button => {
                const command = button.dataset.command;
                const value = button.dataset.value;

                // Reset active state for all buttons initially
                button.classList.remove('active');

                // Skip buttons that don't have a state to check (e.g., Link, Img, Emoji, Clear)
                if (!command || ['createLink', 'insertImage', 'removeFormat'].includes(command)) {
                    return;
                }

                // For formatBlock commands (H1, H2, P, Code, Blockquote)
                if (command === 'formatBlock' && value) {
                    const selection = document.getSelection();
                    if (selection.rangeCount > 0) {
                        let currentNode = selection.getRangeAt(0).commonAncestorContainer;
                        // Traverse up the DOM tree to find block-level parent
                        while (currentNode && currentNode !== activeContentEditor && currentNode.nodeType === Node.ELEMENT_NODE) {
                            if (currentNode.nodeName.toLowerCase() === value) {
                                button.classList.add('active');
                                return; // Found active block, no need to check further for this button
                            }
                            currentNode = currentNode.parentNode;
                        }
                    }
                }
                // For other commands (bold, italic, underline, lists)
                else {
                    try {
                        if (document.queryCommandState(command)) {
                            button.classList.add('active');
                        }
                    } catch (e) {
                        // Ignore errors for commands that might not support queryCommandState
                    }
                }
            });
        }


        /**
         * Sets up a formatting toolbar for a given contenteditable div.
         * @param {string} editorId - The ID of the contenteditable div.
         */
        function setupToolbar(editorId) {
            const toolbarContainer = document.getElementById(editorId.replace('_editor', '').replace('news_content_add', 'toolbar-add').replace('edit_news_content', 'toolbar-edit'));
            const contentEditableDiv = document.getElementById(editorId);
            if (!toolbarContainer || !contentEditableDiv) return;

            // Set the currently active editor globally
            activeContentEditor = contentEditableDiv;

            // Clear previous buttons if re-initializing to prevent duplicates
            toolbarContainer.innerHTML = '';

            const buttons = [
                { label: 'B', command: 'bold', title: 'Bold Text' },
                { label: 'I', command: 'italic', title: 'Italic Text' },
                { label: 'U', command: 'underline', title: 'Underline Text' },
                { label: 'Link', command: 'createLink', action: insertLink, title: 'Insert Link' },
                { label: 'Img', command: 'insertImage', action: insertImage, title: 'Insert Image' },
                { label: 'Emoji', action: toggleEmojiPicker, title: 'Insert Emoji' },
                { label: 'Code', command: 'formatBlock', value: 'pre', title: 'Code Block' },
                { label: 'Blockquote', command: 'formatBlock', value: 'blockquote', title: 'Blockquote' },
                { label: 'UL', command: 'insertUnorderedList', title: 'Unordered List' },
                { label: 'OL', command: 'insertOrderedList', title: 'Ordered List' },
                { label: 'H1', command: 'formatBlock', value: 'h1', title: 'Heading Level 1' },
                { label: 'H2', command: 'formatBlock', value: 'h2', title: 'Heading Level 2' },
                { label: 'P', command: 'formatBlock', value: 'p', title: 'Paragraph' },
                { label: 'Clear', action: clearFormatting, title: 'Remove Formatting' },
            ];

            buttons.forEach(btnInfo => {
                const button = document.createElement('button');
                button.type = 'button'; // Important for buttons not to submit forms
                button.textContent = btnInfo.label;
                button.title = btnInfo.title;
                button.className = 'toolbar-btn'; // Custom class for styling
                
                // Store command and value in dataset for updateToolbarState
                if (btnInfo.command) {
                    button.dataset.command = btnInfo.command;
                }
                if (btnInfo.value) {
                    button.dataset.value = btnInfo.value;
                }

                // Attach event listener
                button.addEventListener('click', (event) => {
                    event.preventDefault(); // Prevent default button behavior (e.g., form submission)

                    // Stop propagation for the Emoji button specifically to prevent immediate closing
                    if (btnInfo.label === 'Emoji') {
                        event.stopPropagation();
                    }

                    // Save current selection range before executing command or opening modal
                    const selection = window.getSelection();
                    if (selection.rangeCount > 0) {
                        currentRange = selection.getRangeAt(0);
                    }

                    if (btnInfo.action) {
                        btnInfo.action(); // Call custom action for link/image/emoji/clear
                    } else {
                        formatDoc(btnInfo.command, btnInfo.value);
                    }
                    // updateToolbarState will be called by the contentEditableDiv's listeners
                });
                toolbarContainer.appendChild(button);
            });

            // Event listeners for the contenteditable div to update toolbar button states
            // These ensure the toolbar highlights correctly as the user types or moves the cursor
            contentEditableDiv.addEventListener('input', () => updateToolbarState(editorId));
            contentEditableDiv.addEventListener('keyup', () => updateToolbarState(editorId));
            contentEditableDiv.addEventListener('mouseup', () => updateToolbarState(editorId));
            contentEditableDiv.addEventListener('selectionchange', () => updateToolbarState(editorId)); // Most important for selection changes
            contentEditableDiv.addEventListener('focus', () => {
                activeContentEditor = contentEditableDiv; // Set active editor on focus
                updateToolbarState(editorId);
            });

            // Initial state update when toolbar is set up and editor gets focus
            contentEditableDiv.focus(); // Focus the editor to trigger initial state update
            updateToolbarState(editorId);
        }

        // --- Emoji Picker Functions ---
        const emojis = [
            '😀', '😃', '😄', '😁', '�', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😙',
            '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥',
            '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧', '🥵', '🥶', '🥴', '😵', '🤯', '🤠', '🥳', '😎', '🤓',
            '🧐', '😕', '😟', '🙁', '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨', '😩', '😫', '😤', '😠', '😡', '🤬', '😈', '👿',
            '💀', '👻', '👽', '🤖', '💩', '😺', '😸', '😹', '😻', '😼', '😽', '🙀', '😿', '😾', '👋', '🤚', '🖐️', '✋', '🖖', '👌',
            '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐',
            '🤲', '🙏', '✍️', '💅', '🤳', '💪', '🦾', '🦵', '🦶', '👂', '👃', '🧠', '🫀', '🫁', '🦷', '🦴', '👀', '👁️', '👅', '👄',
            '👶', '🧒', '👦', '👧', '🧑', '👨', '👩', '🧓', '👴', '👵', '🙍‍♂️', '🙍‍♀️', '🙎‍♂️', '🙎‍♀️', '🙅‍♂️', '🙅‍♀️', '🙆‍♂️', '🙆‍♀️', '🤷‍♂️', '🤷‍♀️',
            '🤦‍♂️', '🤦‍♀️', '🧏‍♂️', '🧏‍♀️', '🙇‍♂️', '🙇‍♀️', '🧍‍♂️', '🧍‍♀️', '🚶‍♂️', '🚶‍♀️', '🏃‍♂️', '🏃‍♀️', '💃', '🕺', '👯‍♂️', '👯‍♀️', '🧖‍♂️', '🧖‍♀️', '🧗‍♂️', '🧗‍♀️',
            '🏇', '⛷️', '🏂', '🏌️‍♂️', '🏌️‍♀️', '🏄‍♂️', '🏄‍♀️', '🚣‍♂️', '🚣‍♀️', '🏊‍♂️', '🏊‍♀️', '⛹️‍♂️', '⛹️‍♀️', '🏋️‍♂️', '🏋️‍♀️', '🚴‍♂️', '🚴‍♀️', '🚵‍♂️', '🚵‍♀️', '🤸‍♂️', '🤸‍♀️',
            '🤼‍♂️', '🤼‍♀️', '🤽‍♂️', '🤽‍♀️', '🤾‍♂️', '🤾‍♀️', '🤹‍♂️', '🤹‍♀️', '🧘‍♂️', '🧘‍♀️', '🛀', '🛌', '🗣️', '👤', '👥', '🫂', '👪', '👨‍👩‍👧', '👨‍👩‍👧‍👦', '👨‍👩‍👦‍👦',
            '👨‍👩‍👧‍👧', '👨‍👨‍👦', '👨‍👨‍👧', '👨‍👨‍👧‍👦', '👨‍👨‍👦‍👦', '👨‍👨‍👧‍👧', '👩‍👩‍👦', '👩‍👩‍👧', '👩‍👩‍👧‍👦', '👩‍👩‍👦‍👦', '👩‍👩‍👧‍👧', '👨‍👦', '👨‍👦‍👦', '👨‍👧', '👨‍👧‍👦', '👨‍👧‍👧', '👩‍👦', '👩‍👦‍👦', '👩‍👧', '👩‍👧‍👦', '👩‍👧‍👧',
            '👚', '👕', '👖', '👔', '👗', '👙', '👘', '🥻', '🩱', '🩲', '🩳', '🧦', '🧤', '🧣', '🧥', '🥼', '🦺', '🎩', '🧢', '👒', '🎓', '⛑️', '👑', '👛', '👜', '👝', '🎒', '🧳', '👓', '🕶️', '🥽', '🥼', '🦺', '🥾', '🥿', '👠', '👡', '👢', '👞', '👟', '🩰', '🥾', '🧦', '🧤', '🧣', '🧥', '🎩', '🧢', '👒', '🎓', '⛑️', '👑', '💍', '💎', '💄', '💋', '👂', '👃', '👣', '👁️', '👅', '👄', '🦻', '🦿', '🧎', '🧏', '🧍', '🧎‍♂️', '🧎‍♀️', '🧏‍♂️', '🧏‍♀️', '🧍‍♂️', '🧍‍♀️',
            '🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐨', '🐯', '🦁', '🐮', '🐷', '🐸', '🐵', '🐔', '🐧', '🐦', '🐥', '🦆',
            '🦅', '🦉', '🦇', '🐺', '🐗', '🐴', '🦄', '🐝', '🐛', '🦋', '🐌', '🐞', '🐜', '🕷️', '🕸️', '🦂', '🦟', '🦠', '🐢', '🐍',
            '🦎', '🦖', '🦕', '🐙', '🦑', '🦐', '🦀', '🐡', '🐠', '🐟', '🐬', '🐳', '🐋', '🦈', '🐊', '🐅', '🐆', '🦓', '🦍', '🦧',
            '🐘', '🦛', '🦏', '🐪', '🐫', '🦒', '🦘', '🐃', '🐂', '🐄', '🐎', '🐖', '🐏', '🐑', '🐐', '🦌', '🐕', '🐩', '🐈', '🐓',
            '🦃', '🕊️', '🐇', '🐁', '🐀', '🐿️', '🦔', '🐾', '🐉', '🐲', '🌵', '🎄', '🌲', '🌳', '🌴', '🌱', '🌿', '☘️', '🍀', '🎍',
            '🎋', '🍂', '🍁', '🍄', '🌰', '🌾', '🌷', '🌻', '🌹', '🌺', '🌸', '🌼', '🌼', '💐', '🌶️', '🌽', '🥕', '🥔', '🧅',
            '🍎', '🍏', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🍈', '🍒', '🍑', '🥭', '🍍', '🥝', '🍅', '🍆', '🥑', '🥦', '🥬',
            '🥒', '🌶️', '🫑', '🌽', '🥕', '🥔', '🧅', '🍄', '🥜', '🌰', '🍞', '🥐', '🥖', '🥨', '🥯', '🥞', '🧇', '🧀', '🍖', '🍗',
            '🥩', '🥓', '🍔', '🍟', '🍕', '🌭', '🥪', '🌮', '🌯', '🥙', '🧆', '🥚', '🍳', '🥘', '🍲', '🥣', '🥗', '🍿', '🧈', '🧂',
            '🥫', '🍱', '🍘', '🍙', '🍚', '🍛', '🍜', '🍝', '🍠', '🍢', '🍣', '🍤', '🍥', '🥮', '🍡', '🥟', '🥠', '🥡', '🍦', '🍧',
            '🍨', '🍩', '🍪', '🎂', '🍰', '🧁', '🥧', '🍫', '🍬', '🍭', '🍮', '🍯', '🍼', '🥛', '☕', '🍵', '🍶', '🍾', '🍷', '🍸',
            '🍹', '🍺', '🍻', '🥂', '🥃', '🥤', '🧋', '🧃', '🧉', '🧊', '🥢', '🍽️', '🍴', '🥄', '🔪', '🏺', '🌍', '🌎', '🌏', '🌐',
            '🗺️', '🧭', '🏔️', '⛰️', '🌋', '🗻', '🏕️', '🏖️', '🏜️', '🏝️', '🏞️', '🏟️', '🏛️', '🏗️', '🏘️', '🏠', '🏡', '🏢', '🏣', '🏤',
            '🏥', '🏦', '🏨', '🏩', '🏪', '🏫', '🏬', '🏭', '🏯', '🏰', '💒', '🗼', '🗽', '⛪', '🕌', '🛕', '🕍', '⛩️', '🕋', '⛲',
            '⛺', '🛺', '🚂', '🚃', '🚄', '🚅', '🚆', '🚇', '🚈', '🚉', '🚊', '🚋', '🚌', '🚍', '🚎', '🚐', '🚑', '🚒', '🚓', '🚔',
            '🚕', '🚖', '🚗', '🚙', '🚚', '🚛', '🚜', '🏎️', '🏍️', '🛵', '🛻', '🛼', '🛷', '🛹', '🛺', '🚲', '🛴', '🚏', '🛣️', '🛤️',
            '⛽', '🚨', '🚥', '🚧', '⚓', '⛵', '🚤', '🛳️', '⛴️', '🛥️', '🚢', '✈️', '🛩️', '🛫', '🛬', '🪂', '💺', '🚁', '🚟', '🚠',
            '🚡', '🛰️', '🚀', '🛸', '🛎️', '🧳', '⌚', '📱', '📲', '💻', '⌨️', '🖥️', '🖨️', '🖱️', '🖲️', '🕹️', '🗜️', '💽', '💾', '💿',
            '📀', '📼', '📷', '📸', '📹', '🎥', '🎬', '📺', '📻', '🎤', '🎧', '🎼', '🎹', '🥁', '🎷', '🎺', '🎸', '🎻', '🪕', '🪘',
            '📱', '☎️', '📞', '📟', '📠', '🔋', '🔌', '💻', '🖥️', '🖨️', '⌨️', '🖱️', '🖲️', '💾', '💿', '📀', '🧮', '🧰', '🔧', '🔨',
            '⚒️', '🔩', '⚙️', '⛓️', '🪓', '⛏️', '🪚', '🪜', '🧱', '🪨', '🪵', '🛖', '🪑', '🛋️', '🛏️', '🚪', '🪞', '🪟', '🪠', '🚽',
            '🚿', '🛁', '🧼', '🪥', '🪒', '🧴', '🧷', '🧹', '🧺', '🧻', '🪣', '🧽', '🧯', '🛒', '🚬', '⚰️', '🪦', '⚱️', '🪧', '🏳️', '🏴',
            '🏁', '🚩', '🎌', '🏴‍☠️', '🇦🇨', '🇦🇩', '🇦🇪', '🇦🇫', '🇦🇬', '🇦🇮', '🇦🇱', '🇦🇲', '🇦🇴', '🇦🇶', '🇦🇷', '🇦🇸', '🇦🇹', '🇦🇺', '🇦🇼', '🇦🇽',
            '🇦🇿', '🇧🇦', '🇧🇧', '🇧🇩', '🇧🇪', '🇧🇫', '🇧🇬', '🇧🇭', '🇧🇮', '🇧🇯', '🇧🇱', '🇧🇲', '🇧🇳', '🇧🇴', '🇧🇶', '🇧🇷', '🇧🇸', '🇧🇹', '🇧🇻',
            '🇧🇼', '🇧🇾', '🇧🇿', '🇨🇦', '🇨🇨', '🇨🇩', '🇨🇫', '🇨🇬', '🇨🇭', '🇨🇮', '🇨🇰', '🇨🇱', '🇨🇲', '🇨🇳', '🇨🇴', '🇨🇵', '🇨🇷', '🇨🇺', '🇨🇻', '🇨🇼',
            '🇨🇽', '🇨🇾', '🇨🇿', '🇩🇪', '🇩🇬', '🇩🇯', '🇩🇰', '🇩🇲', '🇩🇴', '🇩🇿', '🇪🇦', '🇪🇨', '🇪🇪', '🇪🇬', '🇪🇭', '🇪🇷', '🇪🇸', '🇪🇹', '🇪🇺', '🇫🇮',
            '🇫🇯', '🇫🇰', '🇫🇲', '🇫🇴', '🇫🇷', '🇬🇦', '🇬🇧', '🇬🇩', '🇬🇪', '🇬🇫', '🇬🇬', '🇬🇭', '🇬🇮', '🇬🇱', '🇬🇲', '🇬🇳', '🇬🇵', '🇬🇶', '🇬🇷', '🇬🇸',
            '🇬🇹', '🇬🇺', '🇬🇼', '🇬🇾', '🇭🇰', '🇭🇲', '🇭🇳', '🇭🇷', '🇭🇹', '🇭🇺', '🇮🇨', '🇮🇩', '🇮🇪', '🇮🇱', '🇮🇲', '🇮🇳', '🇮🇴', '🇮🇶', '🇮🇷', '🇮🇸',
            '🇮🇹', '🇯🇪', '🇯🇲', '🇯🇴', '🇯🇵', '🇰🇪', '🇰🇬', '🇰🇭', '🇰🇮', '🇰🇲', '🇰🇳', '🇰🇵', '🇰🇷', '🇰🇼', '🇰🇾', '🇰🇿', '🇱🇦', '🇱🇧', '🇱🇨', '🇱🇮',
            '🇱🇰', '🇱🇷', '🇱🇸', '🇱🇹', '🇱🇺', '🇱🇻', '🇱🇾', '🇲🇦', '🇲🇨', '🇲🇩', '🇲🇪', '🇲🇫', '🇲🇬', '🇲🇭', '🇲🇰', '🇲🇱', '🇲🇲', '🇲🇳', '🇲🇴', '🇲🇵',
            '🇲🇶', '🇲🇷', '🇲🇸', '🇲🇹', '🇲🇺', '🇲🇻', '🇲🇼', '🇲🇽', '🇲🇾', '🇲🇿', '🇳🇦', '🇳🇨', '🇳🇪', '🇳🇫', '🇳🇬', '🇳🇮', '🇳🇱', '🇳🇴', '🇳🇵', '🇳🇷',
            '🇳🇺', '🇳🇿', '🇴🇲', '🇵🇦', '🇵🇪', '🇵🇫', '🇵🇬', '🇵🇭', '🇵🇰', '🇵🇱', '🇵🇲', '🇵🇳', '🇵🇷', '🇵🇸', '🇵🇹', '🇵🇼', '🇵🇾', '🇶🇦', '🇷🇪', '🇷🇴',
            '🇷🇸', '🇷🇺', '🇷🇼', '🇸🇦', '🇸🇧', '🇸🇨', '🇸🇩', '🇸🇪', '🇸🇬', '🇸🇭', '🇸🇮', '🇸🇯', '🇸🇰', '🇸🇱', '🇸🇲', '🇸🇳', '🇸🇴', '🇸🇷', '🇸🇸', '🇸🇹',
            '🇸🇻', '🇸🇽', '🇸🇾', '🇸🇿', '🇹🇦', '🇹🇨', '🇹🇩', '🇹🇫', '🇹🇬', '🇹🇭', '🇹🇯', '🇹🇰', '🇹🇱', '🇹🇲', '🇹🇳', '🇹🇴', '🇹🇷', '🇹🇹', '🇹🇻', '🇹🇼',
            '🇹🇿', '🇺🇦', '🇺🇬', '🇺🇲', '🇺🇳', '🇺🇸', '🇺🇾', '🇺🇿', '🇻🇦', '🇻🇨', '🇻🇪', '🇻🇬', '🇻🇮', '🇻🇳', '🇻🇺', '🇼🇫', '🇼🇸', '🇽🇰', '🇾🇪', '🇾🇹',
            '🇿🇦', '🇿🇲', '🇿🇼'
        ];

        function populateEmojiPicker() {
            console.log("Populating emoji picker...");
            const emojiGrid = document.getElementById('emoji-grid');
            if (emojiGrid) {
                emojiGrid.innerHTML = ''; // Clear existing emojis
                emojis.forEach(emoji => {
                    const span = document.createElement('span');
                    span.className = 'emoji-item';
                    span.textContent = emoji;
                    span.onclick = () => selectEmoji(emoji);
                    emojiGrid.appendChild(span);
                });
                console.log("Emojis populated.");
            } else {
                console.error("Emoji grid element not found!");
            }
        }

        function toggleEmojiPicker() {
            console.log("Toggling emoji picker...");
            const emojiModal = document.getElementById('emoji-picker-modal');
            if (emojiModal) {
                if (emojiModal.classList.contains('active')) {
                    console.log("Emoji modal is active, closing.");
                    closeEmojiPicker();
                } else {
                    console.log("Emoji modal is not active, opening.");
                    populateEmojiPicker(); // Populate every time it opens to ensure fresh state
                    emojiModal.classList.add('active');
                }
            } else {
                console.error("Emoji picker modal element not found!");
            }
        }

        function selectEmoji(emoji) {
            console.log("Selected emoji:", emoji);
            if (activeContentEditor && currentRange) {
                activeContentEditor.focus();
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(currentRange); // Restore the last saved range

                // Insert emoji at current cursor position
                document.execCommand('insertText', false, emoji);
                console.log("Emoji inserted.");
            } else {
                console.warn("Could not insert emoji: activeContentEditor or currentRange is null.");
            }
            closeEmojiPicker();
        }

        function closeEmojiPicker() {
            console.log("Closing emoji picker...");
            const emojiModal = document.getElementById('emoji-picker-modal');
            if (emojiModal) {
                emojiModal.classList.remove('active');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const sidebarNavItems = document.querySelectorAll('.sidebar-nav-item');
            const contentSections = document.querySelectorAll('.content-section');
            const adminSubNavButtons = document.querySelectorAll('.btn-admin-sub-nav');
            const adminSubSections = document.querySelectorAll('.admin-sub-section');
            const flashMessage = document.getElementById('flash-message');

            // Function to activate a tab
            function activateTab(tabId) {
                sidebarNavItems.forEach(item => item.classList.remove('active'));
                contentSections.forEach(section => section.classList.remove('active'));

                const activeNavItem = document.querySelector(`.sidebar-nav-item[data-tab="${tabId}"]`);
                const activeContentSection = document.getElementById(tabId);

                if (activeNavItem) activeNavItem.classList.add('active');
                if (activeContentSection) activeContentSection.classList.add('active');

                // Special handling for News Editor to initialize its toolbar
                if (tabId === 'manage-news') {
                    const addFormContainer = document.getElementById('add-news-form-container');
                    const editFormContainer = document.getElementById('edit-news-form-container');
                    
                    if (editFormContainer && editFormContainer.style.display === 'block') {
                        setupToolbar('edit_news_content_editor');
                    } else if (addFormContainer) {
                        addFormContainer.style.display = 'block';
                        if (editFormContainer) editFormContainer.style.display = 'none';
                        setupToolbar('news_content_add_editor');
                    }
                }

                // If it's the admin panel, activate the default sub-tab
                if (tabId === 'admin-panel') {
                    // Check if an admin_tab param exists in the URL
                    const urlParams = new URLSearchParams(window.location.search);
                    const adminTabParam = urlParams.get('admin_tab');
                    if (adminTabParam && adminTabParam !== 'manage-news') { // Exclude 'manage-news' from default admin panel sub-tab
                        activateAdminSubTab(adminTabParam);
                    } else {
                        activateAdminSubTab('manage-wow-characters'); // Default to WoW Characters
                    }
                } else {
                    // If navigating away from admin panel, hide all admin sub-sections
                    adminSubSections.forEach(section => section.classList.remove('active'));
                    adminSubNavButtons.forEach(button => button.classList.remove('active'));
                }
            }

            // Function to activate an admin sub-tab
            function activateAdminSubTab(subTabId) {
                adminSubNavButtons.forEach(button => button.classList.remove('active'));
                adminSubSections.forEach(section => section.classList.remove('active'));

                const activeSubNavButton = document.querySelector(`.btn-admin-sub-nav[data-admin-tab="${subTabId}"]`);
                const activeSubSection = document.getElementById(subTabId);

                if (activeSubNavButton) activeSubNavButton.classList.add('active');
                if (activeSubSection) activeSubSection.classList.add('active');
            }

            // Initial tab activation based on URL hash or default
            const urlParams = new URLSearchParams(window.location.search);
            const adminTabParam = urlParams.get('admin_tab');
            const hash = window.location.hash.substring(1); // Remove '#'

            // Corrected logic for initial tab activation
            if (adminTabParam === 'manage-news') {
                activateTab('manage-news'); // Directly activate News Editor if it's the target
            } else if (adminTabParam) {
                // If it's another admin sub-tab (like manage-wow-characters or manage-website-users)
                activateTab('admin-panel'); // Activate the main admin panel tab
                activateAdminSubTab(adminTabParam); // Then activate the specific sub-tab
            } else if (hash) {
                activateTab(hash);
            } else {
                activateTab('account-details'); // Default tab
            }

            // Event listeners for sidebar navigation
            sidebarNavItems.forEach(item => {
                item.addEventListener('click', function() {
                    const tabId = this.dataset.tab;
                    activateTab(tabId);
                    // Update URL without reloading
                    if (tabId === 'admin-panel') {
                        // For admin panel, retain the admin_tab parameter if present, or set default
                        const currentAdminTab = document.querySelector('.admin-sub-section.active')?.id || 'manage-wow-characters';
                        history.pushState(null, '', `dashboard?admin_tab=${currentAdminTab}`); // Removed #admin-panel
                    } else if (tabId === 'manage-news') {
                        // For news editor, just update with admin_tab parameter
                        history.pushState(null, '', `dashboard?admin_tab=${tabId}`);
                    }
                    else {
                        history.pushState(null, '', `dashboard#${tabId}`);
                    }
                });
            });

            // Event listeners for admin sub-navigation
            adminSubNavButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const subTabId = this.dataset.adminTab;
                    activateAdminSubTab(subTabId);
                    // Update URL without reloading
                    history.pushState(null, '', `dashboard?admin_tab=${subTabId}`); // Removed #admin-panel
                });
            });

            // Live Search for WoW Accounts
            const wowAccountSearchInput = document.getElementById('wowAccountSearchInput');
            const wowAccountsList = document.getElementById('wow-accounts-list');

            if (wowAccountSearchInput && wowAccountsList) {
                wowAccountSearchInput.addEventListener('input', function() {
                    const searchValue = this.value.toLowerCase();
                    const accountDetails = wowAccountsList.querySelectorAll('details');

                    accountDetails.forEach(detail => {
                        const username = detail.dataset.accountUsername; // Get the stored lowercase username
                        if (username.includes(searchValue)) {
                            detail.style.display = ''; // Show the element
                        } else {
                            detail.style.display = 'none'; // Hide the element
                        }
                    });
                });
            }

            // Live Search for Website Users
            const websiteUserSearchInput = document.getElementById('websiteUserSearchInput');
            const websiteUsersTable = document.getElementById('website-users-table');

            if (websiteUserSearchInput && websiteUsersTable) {
                websiteUserSearchInput.addEventListener('input', function() {
                    const searchValue = this.value.toLowerCase();
                    const userRows = websiteUsersTable.querySelectorAll('tbody tr');

                    userRows.forEach(row => {
                        const username = row.dataset.username;
                        const email = row.dataset.email;
                        if (username.includes(searchValue) || email.includes(searchValue)) {
                            row.style.display = ''; // Show the row
                        } else {
                            row.style.display = 'none'; // Hide the row
                        }
                    });
                });
            }

            // Flash message fading logic
            if (flashMessage) {
                const messageType = flashMessage.dataset.messageType;
                if (messageType === 'success') {
                    setTimeout(() => {
                        flashMessage.style.display = 'none';
                    }, 2000); // 2000 milliseconds = 2 seconds
                }
            }

            // Initial setup for the news editor toolbar
            // Check if the news editor is the active tab on page load
            const newsEditorSection = document.getElementById('manage-news');
            if (newsEditorSection && newsEditorSection.classList.contains('active')) {
                // Determine which form (add or edit) is visible and set up its toolbar
                const addFormContainer = document.getElementById('add-news-form-container');
                const editFormContainer = document.getElementById('edit-news-form-container');

                if (editFormContainer && editFormContainer.style.display === 'block') {
                    setupToolbar('edit_news_content_editor');
                } else {
                    setupToolbar('news_content_add_editor');
                }
            }
        });
    </script>

         <!-- Credits -->
<!-- Baftes for website template -->