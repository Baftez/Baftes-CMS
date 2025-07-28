<?php
// news_editor.php

// This file is intended to be included by dashboard.php.
// It assumes $is_admin is already defined and true.
// If accessed directly or by a non-admin, it should redirect for security.
if (!isset($is_admin) || !$is_admin) {
    header("Location: dashboard.php");
    exit();
}

// Initialize messages for news operations
$news_message = '';
$news_message_type = '';

// --- Handle News Management Actions ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the request specifically came from a news form
    if (isset($_POST['add_news']) || isset($_POST['update_news']) || isset($_POST['delete_news'])) {
        // Ensure the admin_tab is set for redirection after processing
        $redirect_admin_tab = 'manage-news'; // Always redirect back to the news editor tab

        if (isset($_POST['add_news'])) {
            $title = trim($_POST['news_title'] ?? '');
            $content = trim($_POST['news_content'] ?? '');
            $author = trim($_SESSION['username'] ?? 'Admin'); // Use session username as author
            $publication_date = date('Y-m-d H:i:s'); // Current timestamp

            if (empty($title) || empty($content)) {
                $news_message = "News title and content are required.";
                $news_message_type = 'error';
            } else {
                try {
                    $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
                    $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $stmt = $conn_web->prepare("INSERT INTO news (title, content, author, publication_date) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $content, $author, $publication_date]);
                    $news_message = "News post added successfully!";
                    $news_message_type = 'success';
                } catch (PDOException $e) {
                    $news_message = "Database error adding news: " . $e->getMessage();
                    $news_message_type = 'error';
                } finally {
                    $conn_web = null;
                }
            }
        } elseif (isset($_POST['update_news'])) {
            $news_id = $_POST['news_id'];
            // These names are now generic as the form is single for editing
            $title = trim($_POST['news_title'] ?? '');
            $content = trim($_POST['news_content'] ?? '');

            if (empty($title) || empty($content)) {
                $news_message = "News title and content are required for update.";
                $news_message_type = 'error';
            } else {
                try {
                    $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
                    $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $stmt = $conn_web->prepare("UPDATE news SET title = ?, content = ? WHERE id = ?");
                    $stmt->execute([$title, $content, $news_id]);
                    $news_message = "News post updated successfully!";
                    $news_message_type = 'success';
                } catch (PDOException $e) {
                    $news_message = "Database error updating news: " . $e->getMessage();
                    $news_message_type = 'error';
                } finally {
                    $conn_web = null;
                }
            }
        } elseif (isset($_POST['delete_news'])) {
            $news_id = $_POST['news_id'];
            try {
                $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
                $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stmt = $conn_web->prepare("DELETE FROM news WHERE id = ?");
                $stmt->execute([$news_id]);
                $news_message = "News post deleted successfully!";
                $news_message_type = 'success';
            } catch (PDOException $e) {
                $news_message = "Database error deleting news: " . $e->getMessage();
                $news_message_type = 'error';
            } finally {
                $conn_web = null;
            }
        }
        // Store message in session and redirect to dashboard with the news editor tab active
        $_SESSION['flash_message'] = $news_message;
        $_SESSION['flash_message_type'] = $news_message_type;
        header("Location: dashboard.php?admin_tab={$redirect_admin_tab}#admin-panel");
        exit();
    }
}

// --- Fetch existing news posts for display ---
$all_news_posts = [];
try {
    $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
    $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt_news = $conn_web->query("SELECT id, title, content, author, publication_date FROM news ORDER BY publication_date DESC");
    $all_news_posts = $stmt_news->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching news posts in news_editor.php: " . $e->getMessage());
    // If there's an error fetching, ensure the message is displayed via flash message
    // Only set if no other flash message is pending from a POST action
    if (!isset($_SESSION['flash_message']) || empty($_SESSION['flash_message'])) {
        $_SESSION['flash_message'] = "Error loading news posts for editing.";
        $_SESSION['flash_message_type'] = 'error';
    }
} finally {
    $conn_web = null;
}
?>

<!-- HTML for News Editor Section -->
<div id="manage-news" class="admin-sub-section">
    <h4 class="text-2xl font-semibold text-yellow-300 mb-4">News Editor</h4>

    <?php // Messages will be displayed by dashboard.php's main message handling ?>

    <!-- Add New News Post Form (initially visible) -->
    <div id="add-news-form-container" class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-md mb-8">
        <h5 class="text-xl font-semibold text-yellow-200 mb-4">Add New News Post</h5>
        <!-- Toolbar for Add Form -->
        <div class="toolbar" id="toolbar-add">
            <!-- Buttons will be generated by JavaScript -->
        </div>
        <form action="dashboard.php" method="POST" class="space-y-4">
            <input type="hidden" name="add_news" value="1">
            <input type="hidden" name="admin_tab" value="manage-news"> <!-- Keep admin_tab for redirect -->
            <div>
                <label for="news_title" class="block text-gray-300 text-lg font-bold mb-2">Title:</label>
                <input type="text" id="news_title" name="news_title" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500">
            </div>
            <div>
                <label for="news_content_add" class="block text-gray-300 text-lg font-bold mb-2">Content:</label>
                <textarea id="news_content_add" name="news_content" rows="6" required
                          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500"></textarea>
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
        <form action="dashboard.php" method="POST" class="space-y-4">
            <input type="hidden" name="news_id" id="edit_news_id">
            <input type="hidden" name="update_news" value="1">
            <input type="hidden" name="admin_tab" value="manage-news">
            <div>
                <label for="edit_news_title" class="block text-gray-300 text-lg font-bold mb-2">Title:</label>
                <input type="text" id="edit_news_title" name="news_title" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500">
            </div>
            <div>
                <label for="edit_news_content" class="block text-gray-300 text-lg font-bold mb-2">Content:</label>
                <textarea id="edit_news_content" name="news_content" rows="10" required
                          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200 border-gray-600 focus:border-yellow-500"></textarea>
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
                            <td><?php echo nl2br(htmlspecialchars(mb_strimwidth($post['content'], 0, 100, '...'))); ?></td> <!-- Display snippet -->
                            <td><?php echo htmlspecialchars($post['author']); ?></td>
                            <td><?php echo htmlspecialchars($post['publication_date']); ?></td>
                            <td class="flex flex-wrap gap-2">
                                <button type="button" class="btn-wow btn-edit"
                                        onclick="openEditNewsForm(<?php echo $post['id']; ?>, '<?php echo addslashes(htmlspecialchars($post['title'], ENT_QUOTES)); ?>', '<?php echo addslashes(htmlspecialchars($post['content'], ENT_QUOTES)); ?>')">
                                    Edit
                                </button>
                                <button type="button" class="btn-wow btn-delete" onclick="showModal('delete-news-modal', <?php echo $post['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-400">No news posts found.</p>
    <?php endif; ?>
</div>

<!-- Modal for News Deletion Confirmation (defined here to be close to the forms) -->
<div id="delete-news-modal" class="modal">
    <div class="modal-content">
        <h3 class="text-2xl">Confirm News Deletion</h3>
        <p>Are you sure you want to delete this news post? This action cannot be undone.</p>
        <div class="modal-buttons">
            <form action="dashboard.php" method="POST" id="confirm-delete-news-form">
                <input type="hidden" name="news_id" id="delete-news-id">
                <input type="hidden" name="delete_news" value="1">
                <input type="hidden" name="admin_tab" value="manage-news">
                <button type="submit" class="btn-confirm">Delete News</button>
            </form>
            <button type="button" class="btn-cancel" onclick="closeModal('delete-news-modal')">Cancel</button>
        </div>
    </div>
</div>

<script>
    // Global functions for modals (assuming showModal and closeModal are globally available from dashboard.php)

    /**
     * Opens the news editing form and populates it with the selected article's data.
     * @param {number} id - The ID of the news article.
     * @param {string} title - The title of the news article.
     * @param {string} content - The content of the news article.
     */
    function openEditNewsForm(id, title, content) {
        document.getElementById('add-news-form-container').style.display = 'none'; // Hide add form
        const editFormContainer = document.getElementById('edit-news-form-container');
        editFormContainer.style.display = 'block'; // Show edit form

        // Populate the edit form fields
        document.getElementById('edit_news_id').value = id;
        document.getElementById('edit_news_title').value = title;
        document.getElementById('edit_news_content').value = content;

        // Initialize toolbar for the edit textarea
        setupToolbar('edit_news_content');

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
        document.getElementById('edit_news_content').value = '';

        // Re-initialize toolbar for the add textarea if needed (e.g., if it loses focus)
        setupToolbar('news_content_add');
    }

    /**
     * Inserts text/tags at the current caret position in a textarea.
     * @param {HTMLTextAreaElement} textarea - The textarea element.
     * @param {string} startTag - The tag to insert at the beginning of the selection.
     * @param {string} [endTag=''] - The tag to insert at the end of the selection (optional).
     */
    function insertAtCaret(textarea, startTag, endTag = '') {
        const scrollPos = textarea.scrollTop;
        let caretPos = textarea.selectionStart;

        const front = (textarea.value).substring(0, caretPos);
        const back = (textarea.value).substring(textarea.selectionEnd, textarea.value.length);
        const selected = (textarea.value).substring(caretPos, textarea.selectionEnd);

        textarea.value = front + startTag + selected + endTag + back;
        caretPos = caretPos + startTag.length;

        textarea.selectionStart = caretPos;
        textarea.selectionEnd = caretPos + selected.length;
        textarea.focus();
        textarea.scrollTop = scrollPos;
    }

    /**
     * Sets up a formatting toolbar for a given textarea.
     * @param {string} textareaId - The ID of the textarea element.
     */
    function setupToolbar(textareaId) {
        const textarea = document.getElementById(textareaId);
        if (!textarea) return;

        let toolbarContainer;
        if (textareaId === 'news_content_add') {
            toolbarContainer = document.getElementById('toolbar-add');
        } else if (textareaId === 'edit_news_content') {
            toolbarContainer = document.getElementById('toolbar-edit');
        }

        if (!toolbarContainer) return;

        // Clear previous buttons if re-initializing to prevent duplicates
        toolbarContainer.innerHTML = '';

        const buttons = [
            { label: 'B', tag: '**', title: 'Bold Text' },
            { label: 'I', tag: '*', title: 'Italic Text' },
            { label: 'U', tag: '__', title: 'Underline Text' },
            { label: 'Link', tag: '[Text](', endTag: ')', title: 'Insert Link' },
            { label: 'Img', tag: '![Alt Text](', endTag: ')', title: 'Insert Image' },
            { label: 'Code', tag: '`', title: 'Inline Code' },
            { label: 'Blockquote', tag: '> ', title: 'Blockquote' },
            { label: 'List Item', tag: '- ', title: 'Unordered List Item' },
            { label: 'Heading 1', tag: '# ', title: 'Heading Level 1' },
            { label: 'Heading 2', tag: '## ', title: 'Heading Level 2' },
            { label: 'Line Break', tag: '\n\n---\n\n', title: 'Insert Horizontal Rule' },
        ];

        buttons.forEach(btnInfo => {
            const button = document.createElement('button');
            button.type = 'button'; // Important for buttons not to submit forms
            button.textContent = btnInfo.label;
            button.title = btnInfo.title;
            button.className = 'toolbar-btn'; // Custom class for styling
            button.addEventListener('click', () => {
                insertAtCaret(textarea, btnInfo.tag, btnInfo.endTag);
            });
            toolbarContainer.appendChild(button);
        });
    }

    // Run when the DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Initial setup for the add news form toolbar
        setupToolbar('news_content_add');

        // Note: showModal and closeModal functions are expected to be available
        // from the parent dashboard.php file where this script is included.
    });
</script>
<style>
    /* Toolbar styles */
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
        transition: background-color 0.2s ease, color 0.2s ease;
        border: none;
        font-size: 0.9rem;
        white-space: nowrap; /* Prevent text wrapping on buttons */
    }

    .toolbar-btn:hover {
        background-color: #6a5b4d;
        color: #ffe066;
    }

    /* Adjust textarea height for editing */
    #edit_news_content {
        min-height: 250px; /* Make the edit textarea larger */
    }

    /* Adjust table cell for snippet content */
    .admin-table td:nth-child(3) { /* Targeting the content snippet column */
        max-width: 300px; /* Limit width to prevent overly wide columns */
        overflow: hidden;
        text-overflow: ellipsis; /* Add ellipsis for overflow */
        white-space: normal; /* Allow text to wrap within the max-width */
    }
</style>
