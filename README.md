WoW Private Server Community Website
This repository contains the source code for a custom World of Warcraft private server community website. Designed with a classic WoW aesthetic, this platform provides essential features for players to manage their accounts and characters, alongside powerful administration tools for server operators to manage users and news content.

The website is built using PHP for server-side logic, MySQL for database management, and a combination of Tailwind CSS and custom CSS for a responsive and themed user interface.

✨ Key Features & Enhancements
We've implemented a range of features to provide a robust and engaging experience:

User Dashboard:

Account Details: Users can view their personal information, including username, email, and last login details.

Change Password: Secure form for users to update their account password.

My Characters: Displays a list of World of Warcraft characters associated with the user's account, showing details like level, race, class, gender, and in-game gold.

Admin Panel (Role-Based Access):

Manage WoW Accounts & Characters: Administrators can view and manage all WoW game accounts and their associated characters. Features include:

Live search functionality to quickly find accounts by username.

Ability to update WoW account GM (Game Master) levels.

Option to update character levels and gold.

Confirmation modals for deleting WoW accounts (which also deletes associated characters) and individual characters.

Manage Website Users: Administrators can manage website user accounts. Features include:

Live search functionality to find users by username or email.

Ability to update user roles (e.g., user to admin).

Option to reset website user passwords.

Confirmation modals for deleting website users.

News Editor (Admin Only):

A dedicated section within the admin panel for creating, editing, and deleting news posts for the community.

Rich Text Editor: A custom-built WYSIWYG (What You See Is What You Get) editor for news content, featuring:

Basic formatting options (Bold, Italic, Underline).

Ability to insert links and images.

Emoji Picker: A custom modal emoji picker to easily insert emojis into news content.

Support for blockquotes, unordered lists, ordered lists, and heading levels (H1, H2, Paragraph).

"Clear Formatting" option.

The editor preserves HTML formatting upon submission and retrieval.

User Experience & Design:

WoW-Themed UI: Custom CSS (style.css) provides a consistent World of Warcraft-inspired visual design across all pages.

Responsive Design: Utilizes Tailwind CSS for a mobile-first, responsive layout that adapts to various screen sizes.

Flash Messages: Dynamic success/error messages displayed after form submissions, with automatic fading for success messages.

Custom Modals: User-friendly confirmation modals for sensitive actions (e.g., deletion), replacing intrusive browser alert() and confirm() prompts.

Consistent Navigation: All pages, including the 404 error page, now feature the site's main navigation bar with dynamic login status display.

Clean URLs (SEO Friendly):

Implemented server-side URL rewriting (via .htaccess for Apache) to remove .php extensions from URLs (e.g., yourdomain.com/dashboard instead of yourdomain.com/dashboard.php).

Includes 301 redirects to ensure old .php URLs are permanently redirected to their clean counterparts, preserving SEO value.

Custom 404 Error Page:

A themed 404 "Page Not Found" page that integrates seamlessly with the website's design, including the main navigation bar and background.

Provides a friendly message and a clear call-to-action to return to the homepage.

🚀 Getting Started
Follow these steps to set up the WoW Private Server Community Website on your local machine or server.

Prerequisites
Web Server: Apache (recommended for .htaccess support)

PHP: Version 7.4 or higher (with pdo_mysql extension enabled)

MySQL Database: Or MariaDB

WoW Private Server Database: You'll need access to your WoW private server's auth and characters databases.

Installation Steps
Clone the Repository:

git clone https://github.com/your-username/your-repo-name.git
cd your-repo-name

(Replace your-username/your-repo-name with your actual GitHub repository path).

Web Server Configuration:

Place all the website files (including index.php, dashboard.php, 404.php, css/, etc.) into your web server's document root (e.g., htdocs for Apache, wwwroot for IIS).

Apache Users: Ensure your Apache configuration allows .htaccess overrides (AllowOverride All for your document root directory). The provided .htaccess file handles clean URLs and custom 404s.

Database Setup:

Create Website Database: Create a new MySQL database for the website (e.g., website_db).

Import SQL Schema:

You'll need a web_users table for website user accounts. A basic schema might look like this (you'll need to create this manually or from an existing project):

CREATE TABLE `web_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `registration_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `last_login_ip` VARCHAR(45),
    `last_login_date` DATETIME
);

You will need to manually create this table or use an existing one.

WoW Database Access: Ensure your web server can connect to your WoW server's auth and characters databases.

Configuration File (config.php):

You'll need a config.php file (not included in this repository for security reasons) in your project's root directory. This file should contain your database connection details and other global settings.

Example config.php structure:

<?php
// Database configuration for Website Users
define('DB_WEB_HOST', 'localhost');
define('DB_WEB_PORT', '3306');
define('DB_WEB_NAME', 'website_db'); // Your website database name
define('DB_WEB_USER', 'your_web_db_user');
define('DB_WEB_PASS', 'your_web_db_password');

// Database configuration for WoW Auth (accounts)
define('DB_AUTH_HOST', 'localhost');
define('DB_AUTH_PORT', '3306');
define('DB_AUTH_NAME', 'auth'); // Your WoW auth database name
define('DB_AUTH_USER', 'your_wow_db_user');
define('DB_AUTH_PASS', 'your_wow_db_password');

// Database configuration for WoW Characters
define('DB_CHAR_HOST', 'localhost');
define('DB_CHAR_PORT', '3306');
define('DB_CHAR_NAME', 'characters'); // Your WoW characters database name
define('DB_CHAR_USER', 'your_wow_db_user');
define('DB_CHAR_PASS', 'your_wow_db_password');

// Site Name
$wow_name = "My Awesome WoW Server";

// Secret key for session management (CHANGE THIS!)
define('SECRET_KEY', 'your_very_strong_secret_key_here_for_sessions');

// Other configurations as needed
?>

Security: Never commit your config.php file directly to a public GitHub repository. Add it to your .gitignore.

Favicon:

Place your favicon.ico file in the root directory of your website.

🛠️ How to Use
Navigation
Use the navigation links in the header to move between Home, News, How to Play, and Community.

The "Account" dropdown will show "Login" and "Register" if not logged in, or your username and "Dashboard" / "Logout" if logged in.

Dashboard
Log in to access your personal dashboard.

Update your password in the "Change Your Password" section.

View your WoW characters under "My Characters".

Admin Panel (Requires Admin Role)
Log in as an administrator to access the "Admin Panel" and "News Editor" links in the sidebar.

Manage WoW Accounts & Characters:

Use the search bar to filter accounts.

Expand account details to see characters.

Use "Update GM" to change a WoW account's GM level.

Edit character level and gold directly in the table and click "Update".

Use "Delete Account" or "Delete Character" buttons to remove entries (requires confirmation).

Manage Website Users:

Use the search bar to filter website users.

Update username, email, or role directly in the table and click "Update".

"Reset Pass" will set a new random password for the user (you'll need to communicate this securely).

"Delete" will remove the website user (requires confirmation).

News Editor (Requires Admin Role)
Add New News Post:

Enter a title.

Use the rich text editor to compose your news content.

Click "Emoji" to open the emoji picker and insert emojis at your cursor position.

Click "Add News Post" to publish.

Edit Existing News Post:

Find the news post in the "Existing News Posts" table.

Click "Edit" to load the content into the editor.

Make your changes and click "Update News Post".

Click "Cancel Edit" to discard changes and return to the add form.

Delete News Post:

Click "Delete" next to a news post (requires confirmation).

🤝 Contributing
Feel free to fork this repository, suggest improvements, or submit pull requests.

📄 License
This project is open-source and available under the MIT License.

Developed with ❤️ by Baftes and Gemini
