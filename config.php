<?php
define('ROOT_PATH', dirname(__DIR__));

/* World Of Warcraft */
$site_url = ""; //  Url to your Wow web page
$wow_name = ""; // Name of your wow server
$key_words = "wow, server"; 
$discord = "https://discord.gg/9mKuPhwTFf"; // Your discord channel
$IP = ""; // Realm ip or url
$description = "Best world of warcraft server in the world"; 
$port_wow = "8085"; // Enter your worlds port here typically 8085 this lets to page know if your server is online or not
$realm_IP = ""; // Enter your Realm IP / url here

// This is for the "How to play" page, leave the ones blank you dont want showing on the page
$download_windows = "Enter download link here"; // Enter the download link for the wow client here
$download_mac = ""; // Enter the download link for the wow client here
$download_torrent = ""; // Enter the download link for the wow client here

//This ones for the community page, leave the ones blank you dont want showing on the page
$Official_Forums = "";  // If you want to add forums 
$Vote_for_Us = ""; // Link your Vote for us website
$Support_the_Server = ""; // For donations

// WoW Server Database (mop_auth)
define('DB_WOW_HOST', '127.0.0.1'); // Or your WoW server's IP
define('DB_WOW_PORT', '3307');
define('DB_WOW_NAME', 'mop_auth');
define('DB_WOW_USER', 'root'); // Change this to a dedicated user with minimal privileges if possible
define('DB_WOW_PASS', 'ascent'); //Enter wow server password here, Default is ascent

// Database configuration for WoW Characters DB
define('DB_CHAR_NAME', 'mop_characters'); // <--- Don't touch

// Website Database (wow_website) (if you want your website on the same mysql version as your server just change the port/password to the mop_auth one
define('DB_WEB_HOST', '127.0.0.1'); // Or your MySQL 8.0 server's IP
define('DB_WEB_PORT', '3306'); // port you use for MySQL 
define('DB_WEB_NAME', 'wow_website'); // put whatever you name the db here or name it wow_website
define('DB_WEB_USER', 'root'); // Change this to a dedicated user with minimal privileges if possible
define('DB_WEB_PASS', 'password'); // Password for mysql

?>
