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

// --- Fetch News Articles from Database ---
$news_articles = [];
$news_fetch_message = '';
$news_fetch_message_type = '';
$single_article = null; // To store a single article if requested

try {
    // Connect to the web database
    $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
    $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if a specific news article is requested via URL parameter
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $article_id = $_GET['id'];
        $stmt_single_news = $conn_web->prepare("SELECT id, title, content, author, publication_date FROM news WHERE id = ?");
        $stmt_single_news->execute([$article_id]);
        $single_article = $stmt_single_news->fetch(PDO::FETCH_ASSOC);

        if (!$single_article) {
            $news_fetch_message = "News article not found.";
            $news_fetch_message_type = 'error';
        }
    } else {
        // Fetch all news posts, ordered by publication_date descending (most recent first)
        $stmt_news = $conn_web->query("SELECT id, title, content, author, publication_date FROM news ORDER BY publication_date DESC");
        $news_articles = $stmt_news->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    // Log the error for debugging purposes
    error_log("Database error fetching news: " . $e->getMessage());
    $news_fetch_message = "Could not load news articles at this time. Please try again later.";
    $news_fetch_message_type = 'error';
} finally {
    // Close the database connection
    $conn_web = null;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $wow_name; ?> - News</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/style.css">
     <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    <style>
        /* Basic styling for news articles */
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

        /* Styling for the content of the news article */
        .news-article .content-display {
            color: #d4c1a7; /* Off-white for content */
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 15px;
            word-wrap: break-word; /* Ensure long words break */
            word-break: break-word; /* More aggressive breaking */
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

        /* Styles for truncated content */
        .truncated-content {
            max-height: 150px; /* Adjust this value to control initial height */
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

        .back-to-news-btn {
            background-color: #5a4b3d; /* Match WoW button style */
            color: #ffe066;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: background-color 0.2s ease, transform 0.1s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none; /* Remove underline for link button */
            margin-bottom: 20px; /* Space below the button */
        }

        .back-to-news-btn:hover {
            background-color: #7a6b5d;
            transform: translateY(-2px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.4);
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
                    Latest News & Updates
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

    <!-- Main Content Area - News Articles -->
    <main class="container">
        <section id="news-list" class="section-bg p-8 mb-8">
            <h2 class="text-4xl font-bold text-yellow-200 mb-8 text-center">Server News Archive</h2>

            <?php if ($news_fetch_message): ?>
                <div class="p-4 mb-4 rounded-md <?php echo $news_fetch_message_type === 'error' ? 'bg-red-700 text-red-100' : 'bg-green-700 text-green-100'; ?>">
                    <?php echo $news_fetch_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($single_article): // Display single article ?>
                <div class="text-center mb-6">
                    <a href="news" class="back-to-news-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left mr-2"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                        Back to All News
                    </a>
                </div>
                <div class="news-article">
                    <h3><?php echo htmlspecialchars($single_article['title']); ?></h3>
                    <p class="meta">Posted on <?php echo date('F j, Y', strtotime($single_article['publication_date'])); ?> by <?php echo htmlspecialchars($single_article['author']); ?></p>
                    <div class="content-display">
                        <?php echo $single_article['content']; ?>
                    </div>
                </div>
            <?php elseif (!empty($news_articles)): // Display list of articles ?>
                <?php foreach ($news_articles as $article): ?>
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
                <p class="text-gray-400 text-center">No news articles found at this time. Please check back later!</p>
            <?php endif; ?>

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
