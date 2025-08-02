// Global references for elements, initialized once on DOMContentLoaded
let sidebarItems;
let contentSections;
let adminSubNavButtons;
let adminSubSections;
let adminPanelDropdown;

// Function to activate a main tab (e.g., Account Details, My Characters, Admin Panel)
function activateTab(tabId) {
    console.log('Activating main tab:', tabId);

    // Deactivate all sidebar items
    sidebarItems.forEach(item => item.classList.remove('active'));
    
    // Hide all content sections
    contentSections.forEach(section => {
        section.classList.remove('active'); // Remove active class
        section.style.display = 'none'; // Explicitly hide
    });

    // Activate the corresponding sidebar item
    const activeNavItem = document.querySelector(`.sidebar-nav-item[data-tab="${tabId}"]`);
    if (activeNavItem) {
        activeNavItem.classList.add('active');
    }

    // Show the target content section
    const targetSection = document.getElementById(tabId);
    if (targetSection) {
        targetSection.classList.add('active'); // Add active class
        targetSection.style.display = 'block'; // Show the active main content section
    }

    // Handle admin panel specific logic
    if (tabId === 'admin-panel') {
        if (adminPanelDropdown) {
            adminPanelDropdown.open = true; // Open the details dropdown
            console.log('Admin panel dropdown opened.');
        }
        // Sub-tab activation is handled by initializeTabState or direct calls
    } else {
        // If navigating away from admin panel, close its dropdown and hide sub-sections
        if (adminPanelDropdown) {
            adminPanelDropdown.open = false; // Close the details dropdown
        }
        adminSubSections.forEach(section => {
            section.classList.remove('active');
            section.style.display = 'none';
        });
        adminSubNavButtons.forEach(button => button.classList.remove('active'));
    }
}

// Function to activate an admin sub-tab (e.g., Manage WoW Characters, Manage Website Users, News Editor)
function activateAdminSubTab(subTabId) {
    console.log('Activating admin sub-tab:', subTabId);

    // Deactivate all admin sub-nav buttons
    adminSubNavButtons.forEach(button => button.classList.remove('active'));
    
    // Hide all admin sub-sections
    adminSubSections.forEach(section => {
        section.classList.remove('active'); // Remove active class
        section.style.display = 'none'; // Explicitly hide
    });

    // Activate the corresponding admin sub-nav button
    const activeSubNavButton = document.querySelector(`.btn-admin-sub-nav[data-admin-tab="${subTabId}"]`);
    if (activeSubNavButton) {
        activeSubNavButton.classList.add('active');
    }

    // Show the target admin sub-section
    const activeSubSection = document.getElementById(subTabId);
    if (activeSubSection) {
        activeSubSection.classList.add('active'); // Add active class
        activeSubSection.style.display = 'block'; // Show the active admin sub-section
        
        // Special handling for News Editor to initialize its toolbar
        if (subTabId === 'manage-news') {
            // Ensure the correct form is shown/hidden and toolbar is set up
            const addFormContainer = document.getElementById('add-news-form-container');
            const editFormContainer = document.getElementById('edit-news-form-container');
            
            if (editFormContainer && editFormContainer.style.display === 'block') {
                // If edit form is currently visible, re-setup its toolbar
                setupToolbar('edit_news_content');
            } else if (addFormContainer) {
                // Otherwise, ensure add form is visible and setup its toolbar
                addFormContainer.style.display = 'block';
                if (editFormContainer) editFormContainer.style.display = 'none';
                setupToolbar('news_content_add');
            }
        }
    }
}

// Unified function for initial tab activation on page load
function initializeTabState() {
    console.log('initializeTabState called.');
    const urlParams = new URLSearchParams(window.location.search);
    const adminTabParam = urlParams.get('admin_tab');
    const hash = window.location.hash.substring(1);

    let targetMainTab = 'account-details'; // Default to account-details
    if (adminTabParam) {
        targetMainTab = 'admin-panel'; // If admin_tab is present, always go to admin-panel
    } else if (hash) {
        targetMainTab = hash; // If a hash is present, use it (unless admin_tab overrides)
    }
    
    console.log('Initial tab state determined: Main Tab:', targetMainTab, 'Admin Sub-Tab:', adminTabParam);

    // Call activateTab for the main tab
    activateTab(targetMainTab);

    // If admin panel is the target, activate the correct sub-tab with a small delay
    if (targetMainTab === 'admin-panel') {
        setTimeout(() => {
            if (adminTabParam) {
                activateAdminSubTab(adminTabParam);
            } else {
                // Default to 'manage-wow-characters' if no specific admin_tab is provided
                activateAdminSubTab('manage-wow-characters');
            }
            // Ensure the details element is open if it's the admin panel
            if (adminPanelDropdown) {
                adminPanelDropdown.open = true;
            }
        }, 50); // Small delay to allow main panel to render
    }
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded fired. Setting up event listeners and initializing state.');

    // Initialize global element references
    sidebarItems = document.querySelectorAll('.sidebar-nav-item');
    contentSections = document.querySelectorAll('.content-section');
    adminSubNavButtons = document.querySelectorAll('.btn-admin-sub-nav');
    adminSubSections = document.querySelectorAll('.admin-sub-section');
    adminPanelDropdown = document.getElementById('admin-panel-dropdown');

    // Set up event listeners for main sidebar navigation
    sidebarItems.forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default link behavior
            const tabId = this.dataset.tab;
            activateTab(tabId);
            // Update URL without reloading
            if (tabId === 'admin-panel') {
                // For admin panel, retain the admin_tab parameter if present, or set default
                const currentAdminTab = document.querySelector('.admin-sub-section.active')?.id || 'manage-wow-characters';
                history.pushState(null, '', `dashboard.php?admin_tab=${currentAdminTab}#admin-panel`);
            } else {
                // Clear admin_tab parameter if navigating away from admin panel
                history.pushState(null, '', `dashboard.php#${tabId}`);
            }
        });
    });

    // Set up event listeners for admin sub-navigation buttons
    adminSubNavButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default button behavior
            const subTabId = this.dataset.adminTab;
            activateAdminSubTab(subTabId);
            // Update URL without reloading
            history.pushState(null, '', `dashboard.php?admin_tab=${subTabId}#admin-panel`);
        });
    });

    // Handle browser back/forward buttons (popstate event)
    window.addEventListener('popstate', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const adminTabFromUrl = urlParams.get('admin_tab');
        const hash = window.location.hash.substring(1); // Remove '#'

        if (hash) {
            activateTab(hash); // Activate the main tab based on hash
            if (hash === 'admin-panel' && adminTabFromUrl) {
                // Use a small timeout here too for popstate, just in case
                setTimeout(() => {
                    activateAdminSubTab(adminTabFromUrl);
                    if (adminPanelDropdown) {
                        adminPanelDropdown.open = true;
                    }
                }, 50);
            }
        } else {
            activateTab('account-details'); // Default if no hash
        }
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

    // Initial call to set up the tab state
    initializeTabState();
});

// Add a window.onload listener as a fallback/additional guarantee for initialization
window.addEventListener('load', () => {
    console.log('window.onload fired. Re-checking/re-activating tabs for robustness.');
    // Call the unified initialization function again
    // This ensures elements are correctly displayed even if some resources load later.
    initializeTabState(); 
});


// Modal functions - Made global for accessibility from inline HTML onclick
function showModal(modalId, itemId) {
    const modal = document.getElementById(modalId);
    if (!modal) return; // Exit if modal not found

    // This is the crucial part: Find the correct hidden input and set its value
    if (modalId === 'delete-web-user-modal') {
        const userIdInput = document.getElementById('delete-web-user-id');
        if (userIdInput) {
            userIdInput.value = itemId;
        }
    } else if (modalId === 'delete-wow-character-modal') {
        const characterGuidInput = document.getElementById('delete-wow-character-guid');
        if (characterGuidInput) {
            characterGuidInput.value = itemId;
        }
    } else if (modalId === 'delete-wow-account-modal') {
        const accountIdInput = document.getElementById('delete-wow-account-id');
        if (accountIdInput) {
            accountIdInput.value = itemId;
        }
    } else if (modalId === 'delete-news-modal') { // Added for news deletion
        const newsIdInput = document.getElementById('delete-news-id');
        if (newsIdInput) {
            newsIdInput.value = itemId;
        }
    }

    modal.classList.add('active'); // Use class to show/hide modal
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// Close modal if clicked outside content
window.addEventListener('click', function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.classList.remove('active');
        }
    });
});

// Functions for News Editor (moved from news_editor.php's inline script)
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
