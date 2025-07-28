<?php
// Include the configuration file from the parent directory
include_once 'config.php';

// Start session
session_start();

// Check for and display flash messages from previous redirects
$message = '';
$messageType = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $messageType = $_SESSION['flash_message_type'];
    // Clear the flash messages after displaying them
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_message_type']);
}

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// --- Security Enhancement: Re-verify user's role from database on every dashboard load ---
$current_db_role = null;
if (isset($_SESSION['user_id'])) {
    try {
        $conn_web_check_role = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
        $conn_web_check_role->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt_check_role = $conn_web_check_role->prepare("SELECT role FROM web_users WHERE id = ?");
        $stmt_check_role->execute([$_SESSION['user_id']]);
        $current_db_role = $stmt_check_role->fetchColumn();
    } catch (PDOException $e) {
        // Log the error for debugging, but don't expose sensitive info to user
        error_log("Database error checking user role: " . $e->getMessage());
        // If there's a database issue, it's safer to assume no admin privileges
        $current_db_role = 'user'; // Fallback to 'user' role on database error
    } finally {
        $conn_web_check_role = null;
    }
}

// If session role was admin but DB role is not, force logout
// This handles the scenario where an admin's privileges are revoked by another admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && $current_db_role !== 'admin') {
    // Role has been revoked, log them out
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    $_SESSION['flash_message'] = "Your administrator privileges have been revoked. You have been logged out.";
    $_SESSION['flash_message_type'] = 'error';
    header("Location: login.php");
    exit();
}

// Update session role with the latest from DB.
// This ensures that if a user is promoted to admin, their session reflects it.
if ($current_db_role !== null && isset($_SESSION['role']) && $_SESSION['role'] !== $current_db_role) {
    $_SESSION['role'] = $current_db_role;
}
// End Security Enhancement


// Function to generate SHA1 hash for WoW passwords
// WoW clients use SHA1(UPPERCASE(USERNAME):UPPERCASE(PASSWORD))
function generateWoWHash($username, $password) {
    $s = strtoupper($username) . ":" . strtoupper($password);
    return sha1($s);
}

// --- Admin Panel Backend Logic ---
$is_admin = false;
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $is_admin = true;

    // Handle Admin Actions (only if admin)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Determine the redirect target tab
        $redirect_tab = '';
        if (isset($_POST['update_web_user']) || isset($_POST['reset_web_password']) || isset($_POST['delete_web_user'])) {
            $redirect_tab = 'manage-website-users';
        } elseif (isset($_POST['update_wow_character']) || isset($_POST['delete_wow_character']) || isset($_POST['update_wow_account_gmlevel']) || isset($_POST['delete_wow_account'])) {
            $redirect_tab = 'manage-wow-characters';
        } elseif (isset($_POST['add_news']) || isset($_POST['update_news']) || isset($_POST['delete_news'])) {
            $redirect_tab = 'manage-news'; // Redirect to news editor tab
        }
        // Fallback if no specific tab is determined
        if (empty($redirect_tab)) {
            $redirect_tab = 'manage-wow-characters'; // Default admin tab
        }

        // Handle Website User Management Actions
        if (isset($_POST['update_web_user'])) {
            $user_id = $_POST['user_id'];
            // Correctly access dynamic input names
            $username = trim($_POST['username_' . $user_id] ?? '');
            $email = trim($_POST['email_' . $user_id] ?? '');
            $role = trim($_POST['role_' . $user_id] ?? '');

            if (empty($username) || empty($email) || empty($role)) {
                $message = "All user fields are required.";
                $messageType = 'error';
            } else {
                try {
                    $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
                    $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $stmt = $conn_web->prepare("UPDATE web_users SET username = ?, email = ?, role = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $role, $user_id]);
                    $message = "Website user updated successfully!";
                    $messageType = 'success';
                } catch (PDOException $e) {
                    $message = "Database error updating user: " . $e->getMessage();
                    $messageType = 'error';
                } finally {
                    $conn_web = null;
                }
            }
            // Store message in session and redirect
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_message_type'] = $messageType;
            header("Location: dashboard.php?admin_tab={$redirect_tab}"); // Removed #admin-panel
            exit();
        } elseif (isset($_POST['reset_web_password'])) {
            $user_id = $_POST['user_id'];
            $new_password = bin2hex(random_bytes(8)); // Generate a random 16-character hex string
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            try {
                $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
                $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Get username for WoW hash
                $stmt_get_username = $conn_web->prepare("SELECT username FROM web_users WHERE id = ?");
                $stmt_get_username->execute([$user_id]);
                $user_data = $stmt_get_username->fetch(PDO::FETCH_ASSOC);
                $username_for_wow = $user_data['username'];

                $conn_web->beginTransaction();
                $conn_wow_auth = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_WOW_NAME, DB_WOW_USER, DB_WOW_PASS);
                $conn_wow_auth->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn_wow_auth->beginTransaction();

                // Update web_users password
                $stmt_web = $conn_web->prepare("UPDATE web_users SET password_hash = ? WHERE id = ?");
                $stmt_web->execute([$hashed_password, $user_id]);

                // Update WoW account password
                $wow_sha_hash = generateWoWHash($username_for_wow, $new_password);
                $stmt_wow = $conn_wow_auth->prepare("UPDATE account SET sha_pass_hash = ? WHERE username = ?" );
                $stmt_wow->execute([$wow_sha_hash, strtoupper($username_for_wow)]);

                $conn_web->commit();
                $conn_wow_auth->commit();

                $message = "Password reset successfully for user ID {$user_id}. New password: <strong>{$new_password}</strong> (Please provide this to the user securely).";
                $messageType = 'success';
            } catch (PDOException $e) {
                if ($conn_web && $conn_web->inTransaction()) $conn_web->rollBack();
                if ($conn_wow_auth && $conn_wow_auth->inTransaction()) $conn_wow_auth->rollBack();
                $message = "Database error resetting password: " . $e->getMessage();
                $messageType = 'error';
            } finally {
                $conn_web = null;
                $conn_wow_auth = null;
            }
            // Store message in session and redirect
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_message_type'] = $messageType;
            header("Location: dashboard.php?admin_tab={$redirect_tab}"); // Removed #admin-panel
            exit();
        } elseif (isset($_POST['delete_web_user'])) {
            $user_id = $_POST['user_id'];
            $conn_web = null;
            $conn_wow_auth = null;
            $conn_wow_chars = null;

            try {
                // Establish connections
                $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
                $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $conn_wow_auth = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_WOW_NAME, DB_WOW_USER, DB_WOW_PASS);
                $conn_wow_auth->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $conn_wow_chars = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_CHAR_NAME, DB_WOW_USER, DB_WOW_PASS);
                $conn_wow_chars->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Start transactions
                $conn_web->beginTransaction();
                $conn_wow_auth->beginTransaction();
                $conn_wow_chars->beginTransaction();

                // 1. Get username from web_users to find WoW account
                $stmt_get_username = $conn_web->prepare("SELECT username FROM web_users WHERE id = ?");
                $stmt_get_username->execute([$user_id]);
                $web_user_data = $stmt_get_username->fetch(PDO::FETCH_ASSOC);

                if ($web_user_data) {
                    $username_to_delete = strtoupper($web_user_data['username']);

                    // 2. Get WoW account ID from auth DB using username
                    $stmt_get_wow_account_id = $conn_wow_auth->prepare("SELECT id FROM account WHERE username = ?");
                    $stmt_get_wow_account_id->execute([$username_to_delete]);
                    $wow_account_id = $stmt_get_wow_account_id->fetchColumn();

                    if ($wow_account_id) {
                        // Define maps for tables and their respective account/guid column names
                        // Tables in mop_characters that link directly to the account ID
                        $tables_account_columns_map = [
                            'account_achievement' => 'account',
                            'account_achievement_progress' => 'account',
                            'account_battle_pet' => 'accountId',
                            'account_battle_pet_slots' => 'accountId',
                            'account_data' => 'accountId', // Confirmed 'accountId' based on previous error resolution
                            'account_instance_times' => 'accountId',
                            'account_spell' => 'account', // Corrected based on latest user error
                            'account_tutorial' => 'accountId'
                        ];

                        // Get all character GUIDs for this account from mop_characters
                        $stmt_get_guids = $conn_wow_chars->prepare("SELECT guid FROM characters WHERE account = ?");
                        $stmt_get_guids->execute([$wow_account_id]);
                        $character_guids = $stmt_get_guids->fetchAll(PDO::FETCH_COLUMN);

                        // Delete from mop_characters tables using account ID first
                        foreach ($tables_account_columns_map as $table => $column_name) {
                            try {
                                $stmt = $conn_wow_chars->prepare("DELETE FROM {$table} WHERE {$column_name} = ?");
                                $stmt->execute([$wow_account_id]);
                            } catch (PDOException $e) {
                                // Log the error and re-throw to ensure transaction rolls back and error is reported
                                throw new PDOException("Failed to delete from mop_characters.{$table} for account ID {$wow_account_id} using column '{$column_name}'. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                            }
                        }

                        // --- Delete from mop_characters tables using character GUIDs ---
                        if (!empty($character_guids)) {
                            $guids_placeholder = implode(',', array_fill(0, count($character_guids), '?'));

                            // Define a map for character GUID related tables in mop_characters and their respective GUID column names.
                            // Removed 'guild_bank_eventlog', 'guild_bank_item', and 'lag_report' from this list due to persistent errors
                            // and user feedback that these tables may not have a direct player GUID or are empty/misnamed.
                            $tables_guid_columns_map = [
                                'armory_character_stats' => 'guid',
                                'character_achievement' => 'guid',
                                'character_achievement_progress' => 'guid',
                                'character_action' => 'guid',
                                'character_arena_stats' => 'guid',
                                'character_aura' => 'guid',
                                'character_aura_effect' => 'guid',
                                'character_banned' => 'guid',
                                'character_battleground_data' => 'guid',
                                'character_battleground_random' => 'guid',
                                'character_battleground_stats' => 'guid',
                                'character_battleground_weekend' => 'guid',
                                'character_bonus_roll' => 'guid',
                                'character_completed_challenges' => 'guid',
                                'character_cuf_profiles' => 'guid',
                                'character_currency' => 'guid',
                                'character_declinedname' => 'guid',
                                'character_deserter' => 'guid',
                                'character_equipmentsets' => 'guid',
                                'character_gifts' => 'guid',
                                'character_glyphs' => 'guid',
                                'character_homebind' => 'guid',
                                'character_instance' => 'guid',
                                'character_inventory' => 'guid',
                                'character_queststatus' => 'guid',
                                'character_reputation' => 'guid',
                                'character_skills' => 'guid',
                                'character_social' => 'guid',
                                'character_spell' => 'guid',
                                'character_spell_cooldown' => 'guid',
                                'character_stats' => 'guid',
                                'character_talent' => 'guid',
                                'group_member' => 'memberGuid',
                                'guild_member' => 'guid',
                                'guild_eventlog' => 'PlayerGuid1',
                                'guild_member_withdraw' => 'guid',
                                'item_instance' => 'owner_guid',
                                'petition' => 'ownerguid',
                                'petition_sign' => 'playerguid',
                                'ticket_bug' => 'playerGuid', // Corrected to playerGuid based on screenshot
                            ];

                            foreach ($tables_guid_columns_map as $table => $column_name) {
                                try {
                                    if ($table === 'guild_eventlog') {
                                        // Special handling for guild_eventlog due to PlayerGuid1 and PlayerGuid2
                                        $stmt = $conn_wow_chars->prepare("DELETE FROM {$table} WHERE PlayerGuid1 IN ({$guids_placeholder}) OR PlayerGuid2 IN ({$guids_placeholder})");
                                        $stmt->execute(array_merge($character_guids, $character_guids));
                                    } else {
                                        $stmt = $conn_wow_chars->prepare("DELETE FROM {$table} WHERE {$column_name} IN ({$guids_placeholder})");
                                        $stmt->execute($character_guids);
                                    }
                                } catch (PDOException $e) {
                                    // Log the error and re-throw for character deletion errors
                                    throw new PDOException("Failed to delete from mop_characters.{$table} for GUIDs using column '{$column_name}'. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                                }
                            }

                            // Handle mail and mail_items: Delete mail_items first, then mail
                            try {
                                $stmt_get_mail_ids = $conn_wow_chars->prepare("SELECT id FROM mail WHERE receiver IN ({$guids_placeholder}) OR sender IN ({$guids_placeholder})");
                                // Fix: Pass character_guids array twice for the two placeholders
                                $stmt_get_mail_ids->execute(array_merge($character_guids, $character_guids));
                                $mail_ids = $stmt_get_mail_ids->fetchAll(PDO::FETCH_COLUMN);

                                if (!empty($mail_ids)) {
                                    $mail_ids_placeholder = implode(',', array_fill(0, count($mail_ids), '?'));
                                    $stmt = $conn_wow_chars->prepare("DELETE FROM mail_items WHERE mail_id IN ({$mail_ids_placeholder})");
                                    $stmt->execute($mail_ids);
                                }
                                $stmt = $conn_wow_chars->prepare("DELETE FROM mail WHERE receiver IN ({$guids_placeholder}) OR sender IN ({$guids_placeholder})");
                                // Fix: Pass character_guids array twice for the two placeholders
                                $stmt->execute(array_merge($character_guids, $character_guids));
                            } catch (PDOException $e) {
                                throw new PDOException("Failed to delete mail/mail_items for character GUIDs. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                            }

                            // Handle character_pet and its linked tables (pet_aura, pet_spell, pet_spell_cooldown)
                            try {
                                $stmt_get_pet_guids = $conn_wow_chars->prepare("SELECT id FROM character_pet WHERE owner IN ({$guids_placeholder})");
                                $stmt_get_pet_guids->execute($character_guids);
                                $pet_guids = $stmt_get_pet_guids->fetchAll(PDO::FETCH_COLUMN);

                                if (!empty($pet_guids)) {
                                    $pet_guids_placeholder = implode(',', array_fill(0, count($pet_guids), '?'));
                                    $stmt = $conn_wow_chars->prepare("DELETE FROM pet_aura WHERE guid IN ({$pet_guids_placeholder})");
                                    $stmt->execute($pet_guids);
                                    $stmt = $conn_wow_chars->prepare("DELETE FROM pet_spell WHERE guid IN ({$pet_guids_placeholder})");
                                    $stmt->execute($pet_guids);
                                    $stmt = $conn_wow_chars->prepare("DELETE FROM pet_spell_cooldown WHERE guid IN ({$pet_guids_placeholder})");
                                    $stmt->execute($pet_guids);
                                }
                                $stmt = $conn_wow_chars->prepare("DELETE FROM character_pet WHERE owner IN ({$guids_placeholder})");
                                $stmt->execute($character_guids);
                            } catch (PDOException $e) {
                                throw new PDOException("Failed to delete character pets and related data for character GUIDs. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                            }

                            // Finally, delete the characters themselves from the main characters table
                            try {
                                $stmt_delete_chars = $conn_wow_chars->prepare("DELETE FROM characters WHERE guid IN ({$guids_placeholder})");
                                $stmt_delete_chars->execute($character_guids);
                            } catch (PDOException $e) {
                                throw new PDOException("Failed to delete characters from main table. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                            }
                        }

                        // Delete the WoW account from auth DB (account_access first, then account)
                        try {
                            $stmt_delete_account_access = $conn_wow_auth->prepare("DELETE FROM account_access WHERE id = ?");
                            $stmt_delete_account_access->execute([$wow_account_id]);
                        } catch (PDOException $e) {
                            throw new PDOException("Failed to delete from account_access. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                        }

                        try {
                            $stmt_delete_wow_account_auth = $conn_wow_auth->prepare("DELETE FROM account WHERE id = ?");
                            $stmt_delete_wow_account_auth->execute([$wow_account_id]);
                        } catch (PDOException $e) {
                            throw new PDOException("Failed to delete from account table in auth DB. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                        }
                    }
                }

                // Delete the web user
                $stmt_delete_web_user = $conn_web->prepare("DELETE FROM web_users WHERE id = ?");
                $stmt_delete_web_user->execute([$user_id]);

                // Commit all transactions
                $conn_web->commit();
                $conn_wow_auth->commit();
                $conn_wow_chars->commit();

                $message = "Website user, associated WoW account, and all related data (characters, achievements, pets, settings, etc.) deleted successfully!";
                $messageType = 'success';

            } catch (PDOException $e) {
                // Rollback all transactions if any error occurs
                if ($conn_web && $conn_web->inTransaction()) $conn_web->rollBack();
                if ($conn_wow_auth && $conn_wow_auth->inTransaction()) $conn_wow_auth->rollBack();
                if ($conn_wow_chars && $conn_wow_chars->inTransaction()) $conn_wow_chars->rollBack();
                $message = "Database error deleting user and related data: " . $e->getMessage();
                $messageType = 'error';
            } finally {
                // Close connections
                $conn_web = null;
                $conn_wow_auth = null;
                $conn_wow_chars = null;
            }
            // Store message in session and redirect
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_message_type'] = $messageType;
            header("Location: dashboard.php?admin_tab={$redirect_tab}"); // Removed #admin-panel
            exit();
        }
        // Handle WoW Character Update Action
        elseif (isset($_POST['update_wow_character'])) {
            $guid = $_POST['guid'];
            $level = $_POST['level_' . $guid] ?? null; // Get level from dynamic input name
            $gold = $_POST['gold_' . $guid] ?? null;   // Get gold from dynamic input name

            // Convert gold to copper (1 gold = 10000 copper)
            $money_in_copper = null;
            if ($gold !== null) {
                $money_in_copper = (int)($gold * 10000);
            }

            try {
                $conn_wow_chars = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_CHAR_NAME, DB_WOW_USER, DB_WOW_PASS);
                $conn_wow_chars->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $update_fields = [];
                $update_values = [];

                if ($level !== null) {
                    $update_fields[] = 'level = ?';
                    $update_values[] = $level;
                }
                if ($money_in_copper !== null) {
                    $update_fields[] = 'money = ?';
                    $update_values[] = $money_in_copper;
                }

                if (!empty($update_fields)) {
                    $sql = "UPDATE characters SET " . implode(', ', $update_fields) . " WHERE guid = ?";
                    $update_values[] = $guid; // Add guid to the end of values for WHERE clause

                    $stmt = $conn_wow_chars->prepare($sql);
                    $stmt->execute($update_values);
                    $message = "WoW character updated successfully!";
                    $messageType = 'success';
                } else {
                    $message = "No fields to update for character.";
                    $messageType = 'info';
                }
            } catch (PDOException $e) {
                $message = "Database error updating character: " . $e->getMessage();
                $messageType = 'error';
            } finally {
                $conn_wow_chars = null;
            }
            // Store message in session and redirect
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_message_type'] = $messageType;
            header("Location: dashboard.php?admin_tab={$redirect_tab}");
            exit();
        }
        // Handle WoW Character Deletion Action
        elseif (isset($_POST['delete_wow_character'])) {
            $guid = $_POST['guid'];

            try {
                $conn_wow_chars = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_CHAR_NAME, DB_WOW_USER, DB_WOW_PASS);
                $conn_wow_chars->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Start transaction
                $conn_wow_chars->beginTransaction();

                // Define a map for character GUID related tables in mop_characters and their respective GUID column names.
                // Removed 'guild_bank_eventlog', 'guild_bank_item', and 'lag_report' from this list due to persistent errors
                // and user feedback that these tables may not have a direct player GUID or are empty/misnamed.
                $tables_guid_columns_map = [
                    'armory_character_stats' => 'guid',
                    'character_achievement' => 'guid',
                    'character_achievement_progress' => 'guid',
                    'character_action' => 'guid',
                    'character_arena_stats' => 'guid',
                    'character_aura' => 'guid',
                    'character_aura_effect' => 'guid',
                    'character_banned' => 'guid',
                    'character_battleground_data' => 'guid',
                    'character_battleground_random' => 'guid',
                    'character_battleground_stats' => 'guid',
                    'character_battleground_weekend' => 'guid',
                    'character_bonus_roll' => 'guid',
                    'character_completed_challenges' => 'guid',
                    'character_cuf_profiles' => 'guid',
                    'character_currency' => 'guid',
                    'character_declinedname' => 'guid',
                    'character_deserter' => 'guid',
                    'character_equipmentsets' => 'guid',
                    'character_gifts' => 'guid',
                    'character_glyphs' => 'guid',
                    'character_homebind' => 'guid',
                    'character_instance' => 'guid',
                    'character_inventory' => 'guid',
                    'character_queststatus' => 'guid',
                    'character_reputation' => 'guid',
                    'character_skills' => 'guid',
                    'character_social' => 'guid',
                    'character_spell' => 'guid',
                    'character_spell_cooldown' => 'guid',
                    'character_stats' => 'guid',
                    'character_talent' => 'guid',
                    'group_member' => 'memberGuid',
                    'guild_member' => 'guid',
                    'guild_eventlog' => 'PlayerGuid1',
                    'guild_member_withdraw' => 'guid',
                    'item_instance' => 'owner_guid',
                    'petition' => 'ownerguid',
                    'petition_sign' => 'playerguid',
                    'ticket_bug' => 'playerGuid', // Corrected to playerGuid based on screenshot
                ];

                foreach ($tables_guid_columns_map as $table => $column_name) {
                    try {
                        if ($table === 'guild_eventlog') {
                            // Special handling for guild_eventlog due to PlayerGuid1 and PlayerGuid2
                            $stmt = $conn_wow_chars->prepare("DELETE FROM {$table} WHERE PlayerGuid1 = ? OR PlayerGuid2 = ?");
                            $stmt->execute([$guid, $guid]);
                        } else {
                            $stmt = $conn_wow_chars->prepare("DELETE FROM {$table} WHERE {$column_name} = ?");
                            $stmt->execute([$guid]);
                        }
                    } catch (PDOException $e) {
                        throw new PDOException("Failed to delete from mop_characters.{$table} for GUID {$guid} using column '{$column_name}'. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                    }
                }

                // Handle mail and mail_items: Delete mail_items first, then mail
                try {
                    $stmt_get_mail_ids = $conn_wow_chars->prepare("SELECT id FROM mail WHERE receiver = ? OR sender = ?");
                    $stmt_get_mail_ids->execute([$guid, $guid]);
                    $mail_ids = $stmt_get_mail_ids->fetchAll(PDO::FETCH_COLUMN);

                    if (!empty($mail_ids)) {
                        $mail_ids_placeholder = implode(',', array_fill(0, count($mail_ids), '?'));
                        $stmt = $conn_wow_chars->prepare("DELETE FROM mail_items WHERE mail_id IN ({$mail_ids_placeholder})");
                        $stmt->execute($mail_ids);
                    }
                    $stmt = $conn_wow_chars->prepare("DELETE FROM mail WHERE receiver = ? OR sender = ?");
                    $stmt->execute([$guid, $guid]);
                } catch (PDOException $e) {
                    throw new PDOException("Failed to delete mail/mail_items for character GUID {$guid}. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                }


                // Handle character_pet and its linked tables (pet_aura, pet_spell, pet_spell_cooldown)
                try {
                    $stmt_get_pet_guids = $conn_wow_chars->prepare("SELECT id FROM character_pet WHERE owner = ?");
                    $stmt_get_pet_guids->execute([$guid]);
                    $pet_guids = $stmt_get_pet_guids->fetchAll(PDO::FETCH_COLUMN);

                    if (!empty($pet_guids)) {
                        $pet_guids_placeholder = implode(',', array_fill(0, count($pet_guids), '?'));
                        $stmt = $conn_wow_chars->prepare("DELETE FROM pet_aura WHERE guid IN ({$pet_guids_placeholder})");
                        $stmt->execute($pet_guids);
                        $stmt = $conn_wow_chars->prepare("DELETE FROM pet_spell WHERE guid IN ({$pet_guids_placeholder})");
                        $stmt->execute($pet_guids);
                        $stmt = $conn_wow_chars->prepare("DELETE FROM pet_spell_cooldown WHERE guid IN ({$pet_guids_placeholder})");
                        $stmt->execute($pet_guids);
                    }
                    $stmt = $conn_wow_chars->prepare("DELETE FROM character_pet WHERE owner = ?");
                    $stmt->execute([$guid]);
                } catch (PDOException $e) {
                    throw new PDOException("Failed to delete character pets and related data for character GUID {$guid}. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                }

                // Finally, delete the character itself
                try {
                    $stmt = $conn_wow_chars->prepare("DELETE FROM characters WHERE guid = ?");
                    $stmt->execute([$guid]);
                } catch (PDOException $e) {
                    throw new PDOException("Failed to delete character with GUID {$guid} from main table. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                }

                // Commit transaction
                $conn_wow_chars->commit();

                $message = "WoW character and all related data deleted successfully!";
                $messageType = 'success';
            } catch (PDOException $e) {
                if ($conn_wow_chars && $conn_wow_chars->inTransaction()) $conn_wow_chars->rollBack();
                $message = "Database error deleting character: " . $e->getMessage();
                $messageType = 'error';
            } finally {
                $conn_wow_chars = null;
            }
            // Store message in session and redirect
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_message_type'] = $messageType;
            header("Location: dashboard.php?admin_tab={$redirect_tab}"); // Removed #admin-panel
            exit();
        }
        // Handle WoW Account GM Level Update Action
        elseif (isset($_POST['update_wow_account_gmlevel'])) {
            $account_id = $_POST['account_id'];
            // Dynamically get the gm_level value based on the account_id
            $new_gm_level = $_POST['gm_level_' . $account_id] ?? 0; // Default to 0 if not set

            try {
                $conn_wow_auth = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_WOW_NAME, DB_WOW_USER, DB_WOW_PASS);
                $conn_wow_auth->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $conn_wow_auth->beginTransaction();

                // Always update gmlevel in the 'account' table
                $stmt_update_account = $conn_wow_auth->prepare("UPDATE account SET gmlevel = ? WHERE id = ?");
                $stmt_update_account->execute([$new_gm_level, $account_id]);

                // Handle 'account_access' table based on new GM level
                if ($new_gm_level == 0) {
                    // If GM level is 0, remove the entry from account_access
                    $stmt_delete_access = $conn_wow_auth->prepare("DELETE FROM account_access WHERE id = ? AND RealmID = -1");
                    $stmt_delete_access->execute([$account_id]);
                    $message = "GM level for account ID {$account_id} set to Player (0) and access removed!";
                } else {
                    // If GM level is greater than 0, update or insert into account_access
                    $stmt_check_access = $conn_wow_auth->prepare("SELECT COUNT(*) FROM account_access WHERE id = ? AND RealmID = -1");
                    $stmt_check_access->execute([$account_id]);
                    $exists = $stmt_check_access->fetchColumn();

                    if ($exists) {
                        // Update existing entry
                        $stmt_update_access = $conn_wow_auth->prepare("UPDATE account_access SET gmlevel = ? WHERE id = ? AND RealmID = -1");
                        $stmt_update_access->execute([$new_gm_level, $account_id]);
                    } else {
                        // Insert new entry
                        $stmt_insert_access = $conn_wow_auth->prepare("INSERT INTO account_access (id, gmlevel, RealmID) VALUES (?, ?, -1)");
                        $stmt_insert_access->execute([$account_id, $new_gm_level]);
                    }
                    $message = "GM level for account ID {$account_id} updated successfully to {$new_gm_level}!";
                }

                $conn_wow_auth->commit();
                $messageType = 'success';

            } catch (PDOException $e) {
                if ($conn_wow_auth && $conn_wow_auth->inTransaction()) $conn_wow_auth->rollBack();
                $message = "Database error updating GM level: " . $e->getMessage();
                $messageType = 'error';
            } finally {
                $conn_wow_auth = null;
            }
            // Store message in session and redirect
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_message_type'] = $messageType;
            header("Location: dashboard.php?admin_tab={$redirect_tab}"); // Removed #admin-panel
            exit();
        }
        // Handle WoW Account Deletion Action
        elseif (isset($_POST['delete_wow_account'])) {
            $account_id = $_POST['account_id'];
            $conn_wow_auth = null;
            $conn_wow_chars = null;
            $conn_web = null; // Add web connection for conditional deletion

            try {
                $conn_wow_auth = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_WOW_NAME, DB_WOW_USER, DB_WOW_PASS);
                $conn_wow_auth->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $conn_wow_chars = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_CHAR_NAME, DB_WOW_USER, DB_WOW_PASS);
                $conn_wow_chars->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
                $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $conn_wow_auth->beginTransaction();
                $conn_wow_chars->beginTransaction();
                $conn_web->beginTransaction(); // Start transaction for web DB as well

                // 1. Get WoW account username to check for linked web user
                $stmt_get_wow_username = $conn_wow_auth->prepare("SELECT username FROM account WHERE id = ?");
                $stmt_get_wow_username->execute([$account_id]);
                $wow_account_username = $stmt_get_wow_username->fetchColumn();

                // 2. Get all character GUIDs for this account from mop_characters
                $stmt_get_guids = $conn_wow_chars->prepare("SELECT guid FROM characters WHERE account = ?");
                $stmt_get_guids->execute([$account_id]);
                $character_guids = $stmt_get_guids->fetchAll(PDO::FETCH_COLUMN);

                // 3. Delete from mop_characters tables using account ID
                $tables_account_columns_map = [
                    'account_achievement' => 'account',
                    'account_achievement_progress' => 'account',
                    'account_battle_pet' => 'accountId',
                    'account_battle_pet_slots' => 'accountId',
                    'account_data' => 'accountId', // Confirmed 'accountId' based on previous error resolution
                    'account_instance_times' => 'accountId',
                    'account_spell' => 'account', // Corrected based on latest user error
                    'account_tutorial' => 'accountId'
                ];

                foreach ($tables_account_columns_map as $table => $column_name) {
                    try {
                        $stmt = $conn_wow_chars->prepare("DELETE FROM {$table} WHERE {$column_name} = ?");
                        $stmt->execute([$account_id]);
                    } catch (PDOException $e) {
                        throw new PDOException("Failed to delete from mop_characters.{$table} for account ID {$account_id} using column '{$column_name}'. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                    }
                }

                // 4. Delete from mop_characters tables using character GUIDs
                if (!empty($character_guids)) {
                    $guids_placeholder = implode(',', array_fill(0, count($character_guids), '?'));

                    // Define a map for character GUID related tables in mop_characters and their respective GUID column names.
                    // Removed 'guild_bank_eventlog', 'guild_bank_item', and 'lag_report' from this list due to persistent errors
                    // and user feedback that these tables may not have a direct player GUID or are empty/misnamed.
                    $tables_guid_columns_map = [
                        'armory_character_stats' => 'guid',
                        'character_achievement' => 'guid',
                        'character_achievement_progress' => 'guid',
                        'character_action' => 'guid',
                        'character_arena_stats' => 'guid',
                        'character_aura' => 'guid',
                        'character_aura_effect' => 'guid',
                        'character_banned' => 'guid',
                        'character_battleground_data' => 'guid',
                        'character_battleground_random' => 'guid',
                        'character_battleground_stats' => 'guid',
                        'character_battleground_weekend' => 'guid',
                        'character_bonus_roll' => 'guid',
                        'character_completed_challenges' => 'guid',
                        'character_cuf_profiles' => 'guid',
                        'character_currency' => 'guid',
                        'character_declinedname' => 'guid',
                        'character_deserter' => 'guid',
                        'character_equipmentsets' => 'guid',
                        'character_gifts' => 'guid',
                        'character_glyphs' => 'guid',
                        'character_homebind' => 'guid',
                        'character_instance' => 'guid',
                        'character_inventory' => 'guid',
                        'character_queststatus' => 'guid',
                        'character_reputation' => 'guid',
                        'character_skills' => 'guid',
                        'character_social' => 'guid',
                        'character_spell' => 'guid',
                        'character_spell_cooldown' => 'guid',
                        'character_stats' => 'guid',
                        'character_talent' => 'guid',
                        'group_member' => 'memberGuid',
                        'guild_member' => 'guid',
                        'guild_eventlog' => 'PlayerGuid1',
                        'guild_member_withdraw' => 'guid',
                        'item_instance' => 'owner_guid',
                        'petition' => 'ownerguid',
                        'petition_sign' => 'playerguid',
                        'ticket_bug' => 'playerGuid', // Corrected to playerGuid based on screenshot
                    ];

                    foreach ($tables_guid_columns_map as $table => $column_name) {
                        try {
                            if ($table === 'guild_eventlog') {
                                // Special handling for guild_eventlog due to PlayerGuid1 and PlayerGuid2
                                $stmt = $conn_wow_chars->prepare("DELETE FROM {$table} WHERE PlayerGuid1 IN ({$guids_placeholder}) OR PlayerGuid2 IN ({$guids_placeholder})");
                                $stmt->execute(array_merge($character_guids, $character_guids));
                            } else {
                                $stmt = $conn_wow_chars->prepare("DELETE FROM {$table} WHERE {$column_name} IN ({$guids_placeholder})");
                                $stmt->execute($character_guids);
                            }
                        } catch (PDOException $e) {
                            throw new PDOException("Failed to delete from mop_characters.{$table} for GUIDs using column '{$column_name}'. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                        }
                    }

                    // Handle mail and mail_items: Delete mail_items first, then mail
                    try {
                        $stmt_get_mail_ids = $conn_wow_chars->prepare("SELECT id FROM mail WHERE receiver IN ({$guids_placeholder}) OR sender IN ({$guids_placeholder})");
                        // Fix: Pass character_guids array twice for the two placeholders
                        $stmt_get_mail_ids->execute(array_merge($character_guids, $character_guids));
                        $mail_ids = $stmt_get_mail_ids->fetchAll(PDO::FETCH_COLUMN);

                        if (!empty($mail_ids)) {
                            $mail_ids_placeholder = implode(',', array_fill(0, count($mail_ids), '?'));
                            $stmt = $conn_wow_chars->prepare("DELETE FROM mail_items WHERE mail_id IN ({$mail_ids_placeholder})");
                            $stmt->execute($mail_ids);
                        }
                        $stmt = $conn_wow_chars->prepare("DELETE FROM mail WHERE receiver IN ({$guids_placeholder}) OR sender IN ({$guids_placeholder})");
                        // Fix: Pass character_guids array twice for the two placeholders
                        $stmt->execute(array_merge($character_guids, $character_guids));
                    } catch (PDOException $e) {
                        throw new PDOException("Failed to delete mail/mail_items for character GUIDs. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                    }

                    // Handle character_pet and its linked tables
                    try {
                        $stmt_get_pet_guids = $conn_wow_chars->prepare("SELECT id FROM character_pet WHERE owner IN ({$guids_placeholder})");
                        $stmt_get_pet_guids->execute($character_guids);
                        $pet_guids = $stmt_get_pet_guids->fetchAll(PDO::FETCH_COLUMN);

                        if (!empty($pet_guids)) {
                            $pet_guids_placeholder = implode(',', array_fill(0, count($pet_guids), '?'));
                            $stmt = $conn_wow_chars->prepare("DELETE FROM pet_aura WHERE guid IN ({$pet_guids_placeholder})");
                            $stmt->execute($pet_guids);
                            $stmt = $conn_wow_chars->prepare("DELETE FROM pet_spell WHERE guid IN ({$pet_guids_placeholder})");
                            $stmt->execute($pet_guids);
                            $stmt = $conn_wow_chars->prepare("DELETE FROM pet_spell_cooldown WHERE guid IN ({$pet_guids_placeholder})");
                            $stmt->execute($pet_guids);
                        }
                        $stmt = $conn_wow_chars->prepare("DELETE FROM character_pet WHERE owner IN ({$guids_placeholder})");
                        $stmt->execute($character_guids);
                    } catch (PDOException $e) {
                        throw new PDOException("Failed to delete character pets and related data. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                    }

                    // Finally, delete the characters themselves
                    try {
                        $stmt_delete_chars = $conn_wow_chars->prepare("DELETE FROM characters WHERE guid IN ({$guids_placeholder})");
                        $stmt_delete_chars->execute($character_guids);
                    } catch (PDOException $e) {
                        throw new PDOException("Failed to delete characters. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                    }
                }

                // 5. Delete the WoW account from auth DB
                try {
                    $stmt_delete_account_access = $conn_wow_auth->prepare("DELETE FROM account_access WHERE id = ?");
                    $stmt_delete_account_access->execute([$account_id]);
                } catch (PDOException $e) {
                    throw new PDOException("Failed to delete from account_access for account ID {$account_id}. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                }

                try {
                    $stmt_delete_wow_account_auth = $conn_wow_auth->prepare("DELETE FROM account WHERE id = ?");
                    $stmt_delete_wow_account_auth->execute([$account_id]);
                } catch (PDOException $e) {
                    throw new PDOException("Failed to delete WoW account from auth DB for account ID {$account_id}. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                }

                // 6. Conditionally delete linked website user
                if ($wow_account_username) {
                    $stmt_get_web_user_id = $conn_web->prepare("SELECT id FROM web_users WHERE username = ?");
                    $stmt_get_web_user_id->execute([strtolower($wow_account_username)]); // Website usernames are lowercase
                    $linked_web_user_id = $stmt_get_web_user_id->fetchColumn();

                    if ($linked_web_user_id) {
                        try {
                            $stmt_delete_web_user = $conn_web->prepare("DELETE FROM web_users WHERE id = ?");
                            $stmt_delete_web_user->execute([$linked_web_user_id]);
                        } catch (PDOException $e) {
                            throw new PDOException("Failed to delete linked website user with ID {$linked_web_user_id}. Error: " . $e->getMessage(), (int)$e->getCode(), $e);
                        }
                    }
                }

                $conn_wow_auth->commit();
                $conn_wow_chars->commit();
                $conn_web->commit(); // Commit web transaction

                $message = "WoW account and all associated characters/data deleted successfully!" .
                           ($wow_account_username && $linked_web_user_id ? " Linked website user also deleted." : " No linked website user found or deleted.");
                $messageType = 'success';

            } catch (PDOException $e) {
                if ($conn_wow_auth && $conn_wow_auth->inTransaction()) $conn_wow_auth->rollBack();
                if ($conn_wow_chars && $conn_wow_chars->inTransaction()) $conn_wow_chars->rollBack();
                if ($conn_web && $conn_web->inTransaction()) $conn_web->rollBack(); // Rollback web transaction
                $message = "Database error deleting WoW account and related data: " . $e->getMessage();
                $messageType = 'error';
            } finally {
                $conn_wow_auth = null;
                $conn_wow_chars = null;
                $conn_web = null;
            }
            // Store message in session and redirect
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_message_type'] = $messageType;
            header("Location: dashboard.php?admin_tab={$redirect_tab}"); // Removed #admin-panel
            exit();
        }
        // Handle News Management
        if (isset($_POST['add_news'])) {
            $title = trim($_POST['news_title'] ?? '');
            // Content is now HTML from the rich text editor
            $content = $_POST['news_content_html'] ?? ''; // Get content from hidden input
            $author = trim($_SESSION['username'] ?? 'Admin'); // Use session username as author
            $publication_date = date('Y-m-d H:i:s'); // Current timestamp

            if (empty($title) || empty($content)) {
                $message = "News title and content are required.";
                $messageType = 'error';
            } else {
                try {
                    $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
                    $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $stmt = $conn_web->prepare("INSERT INTO news (title, content, author, publication_date) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $content, $author, $publication_date]);
                    $message = "News post added successfully!";
                    $messageType = 'success';
                } catch (PDOException $e) {
                    $message = "Database error adding news: " . $e->getMessage();
                    $messageType = 'error';
                } finally {
                    $conn_web = null;
                }
            }
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_message_type'] = $messageType;
            // Removed #admin-panel from news redirects
            header("Location: dashboard.php?admin_tab={$redirect_tab}");
            exit();
        } elseif (isset($_POST['update_news'])) {
            $news_id = $_POST['news_id'];
            $title = trim($_POST['news_title'] ?? '');
            // Content is now HTML from the rich text editor
            $content = $_POST['news_content_html'] ?? ''; // Get content from hidden input

            if (empty($title) || empty($content)) {
                $message = "News title and content are required for update.";
                $messageType = 'error';
            } else {
                try {
                    $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
                    $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $stmt = $conn_web->prepare("UPDATE news SET title = ?, content = ? WHERE id = ?");
                    $stmt->execute([$title, $content, $news_id]);
                    $message = "News post updated successfully!";
                    $messageType = 'success';
                } catch (PDOException $e) {
                    $message = "Database error updating news: " . $e->getMessage();
                    $messageType = 'error';
                } finally {
                    $conn_web = null;
                }
            }
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_message_type'] = $messageType;
            // Removed #admin-panel from news redirects
            header("Location: dashboard.php?admin_tab={$redirect_tab}");
            exit();
        } elseif (isset($_POST['delete_news'])) {
            $news_id = $_POST['news_id'];
            try {
                $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
                $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stmt = $conn_web->prepare("DELETE FROM news WHERE id = ?");
                $stmt->execute([$news_id]);
                $message = "News post deleted successfully!";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = "Database error deleting news: " . $e->getMessage();
                $messageType = 'error';
            } finally {
                $conn_web = null;
            }
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_message_type'] = $messageType;
            // Removed #admin-panel from news redirects
            header("Location: dashboard.php?admin_tab={$redirect_tab}");
            exit();
        }
    }
}


// Handle password change form submission (existing logic)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    // Input validation
    if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
        $message = "All password fields are required.";
        $messageType = 'error';
    } elseif ($new_password !== $confirm_new_password) {
        $message = "New passwords do not match.";
        $messageType = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = "New password must be at least 6 characters long.";
        $messageType = 'error';
    } else {
        $conn_wow = null;
        $conn_web = null;

        try {
            // Connect to Website DB (wow_website)
            $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
            $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Fetch current password hash from web_users
            $stmt_fetch_web_pass = $conn_web->prepare("SELECT password_hash FROM web_users WHERE id = ?");
            $stmt_fetch_web_pass->execute([$_SESSION['user_id']]);
            $user_web_data = $stmt_fetch_web_pass->fetch(PDO::FETCH_ASSOC);

            if (!$user_web_data || !password_verify($current_password, $user_web_data['password_hash'])) {
                $message = "Current password is incorrect.";
                $messageType = 'error';
            } else {
                // Connect to WoW Auth DB (mop_auth)
                $conn_wow = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_WOW_NAME, DB_WOW_USER, DB_WOW_PASS);
                $conn_wow->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Start a transaction for both databases
                $conn_web->beginTransaction();
                $conn_wow->beginTransaction();

                // Generate new hashes
                $new_web_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                // WoW uses SHA1(UPPERCASE(USERNAME):UPPERCASE(PASSWORD))
                $new_wow_sha_hash = generateWoWHash($_SESSION['username'], $new_password);

                // Update password in Website DB
                $stmt_update_web_pass = $conn_web->prepare(
                    "UPDATE web_users SET password_hash = ? WHERE id = ?"
                );
                $stmt_update_web_pass->execute([$new_web_password_hash, $_SESSION['user_id']]);

                // Update password in WoW Auth DB
                $stmt_update_wow_pass = $conn_wow->prepare(
                    "UPDATE account SET sha_pass_hash = ? WHERE username = ?"
                );
                $stmt_update_wow_pass->execute([$new_wow_sha_hash, strtoupper($_SESSION['username'])]);

                // Commit both transactions
                $conn_web->commit();
                $conn_wow->commit();

                $message = "Password changed successfully for both website and WoW accounts!";
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            // Rollback transactions if any error occurs
            if ($conn_web && $conn_web->inTransaction()) {
                $conn_web->rollBack();
            }
            if ($conn_wow && $conn_wow->inTransaction()) {
                $conn_wow->rollBack();
            }
            $message = "Database error: " . $e->getMessage();
            $messageType = 'error';
        } finally {
            // Close connections
            $conn_web = null;
            $conn_wow = null;
        }
    }
}

// Set default values for wow_name, IP, and discord if not defined in config.php
if (!isset($wow_name)) {
    $wow_name = "WoW Private Server";
}
if (!isset($IP)) {
    $IP = "pal.baftes.com";
}
if (!isset($discord)) {
    $discord = "https://discord.com/";
}

// --- Fetch WoW Character Data for My Characters Tab ---
$wow_characters = [];
try {
    // Connect to WoW Auth DB (mop_auth) to get the WoW account ID
    $conn_wow_auth = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_WOW_NAME, DB_WOW_USER, DB_WOW_PASS);
    $conn_wow_auth->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt_get_wow_account_id = $conn_wow_auth->prepare("SELECT id FROM account WHERE username = ?");
    $stmt_get_wow_account_id->execute([strtoupper($_SESSION['username'])]);
    $wow_account_id = $stmt_get_wow_account_id->fetchColumn();

    if ($wow_account_id) {
        // Connect to WoW Characters DB (mop_characters)
        $conn_wow_chars = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_CHAR_NAME, DB_WOW_USER, DB_WOW_PASS);
        $conn_wow_chars->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt_get_chars = $conn_wow_chars->prepare("SELECT guid, name, level, race, class, gender, money FROM characters WHERE account = ?");
        $stmt_get_chars->execute([$wow_account_id]);
        $wow_characters = $stmt_get_chars->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error fetching WoW characters: " . $e->getMessage());
    // Optionally set a user-friendly message for character fetch error
} finally {
    $conn_wow_auth = null;
    $conn_wow_chars = null;
}

// --- Admin Panel Data Fetching (only if admin) ---
$website_users = [];
$all_wow_accounts_with_chars = []; // New structure for admin panel
$all_news_posts = []; // For news editor

if ($is_admin) {
    try {
        // Fetch all website users
        $conn_web = new PDO("mysql:host=" . DB_WEB_HOST . ";port=" . DB_WEB_PORT . ";dbname=" . DB_WEB_NAME, DB_WEB_USER, DB_WEB_PASS);
        $conn_web->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt_all_web_users = $conn_web->query("SELECT id, username, email, role, registration_date, last_login_ip, last_login_date FROM web_users ORDER BY username ASC");
        $website_users = $stmt_all_web_users->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all news posts for the editor
        $stmt_news = $conn_web->query("SELECT id, title, content, author, publication_date FROM news ORDER BY publication_date DESC");
        $all_news_posts = $stmt_news->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error fetching all website users or news posts: " . $e->getMessage());
        $message = "Error loading admin data.";
        $messageType = 'error';
    } finally {
        $conn_web = null;
    }

    try {
        $conn_wow_auth = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_WOW_NAME, DB_WOW_USER, DB_WOW_PASS);
        $conn_wow_auth->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $conn_wow_chars = new PDO("mysql:host=" . DB_WOW_HOST . ";port=" . DB_WOW_PORT . ";dbname=" . DB_CHAR_NAME, DB_WOW_USER, DB_WOW_PASS);
        $conn_wow_chars->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch all WoW accounts (no filtering here, filtering is done client-side)
        $sql_accounts = "SELECT id, username, gmlevel FROM account ORDER BY username ASC";
        $stmt_all_accounts = $conn_wow_auth->query($sql_accounts);
        $all_wow_accounts_raw = $stmt_all_accounts->fetchAll(PDO::FETCH_ASSOC);

        foreach ($all_wow_accounts_raw as $account) {
            $current_account_id = $account['id'];
            $account_data = [
                'id' => $account['id'],
                'username' => $account['username'],
                'gmlevel' => $account['gmlevel'], // Default to gmlevel from 'account' table
                'characters' => []
            ];

            // Fetch gmlevel from 'account_access' table if it exists
            $stmt_get_account_access_gmlevel = $conn_wow_auth->prepare("SELECT gmlevel FROM account_access WHERE id = ? AND RealmID = -1");
            $stmt_get_account_access_gmlevel->execute([$current_account_id]);
            $account_access_gmlevel = $stmt_get_account_access_gmlevel->fetchColumn();

            // If an entry exists in account_access, use its gmlevel for display
            if ($account_access_gmlevel !== false) {
                $account_data['gmlevel'] = $account_access_gmlevel;
            }

            // Fetch characters for this specific account
            $stmt_chars_for_account = $conn_wow_chars->prepare("SELECT guid, name, level, race, class, gender, money FROM characters WHERE account = ? ORDER BY name ASC");
            $stmt_chars_for_account->execute([$current_account_id]);
            $account_data['characters'] = $stmt_chars_for_account->fetchAll(PDO::FETCH_ASSOC);

            $all_wow_accounts_with_chars[] = $account_data;
        }

    } catch (PDOException $e) {
        error_log("Error fetching all WoW accounts and characters for admin: " . $e->getMessage());
        $message = "Error loading WoW accounts and characters for admin panel.";
        $messageType = 'error';
    } finally {
        $conn_wow_chars = null;
        $conn_wow_auth = null;
    }
}


// --- Race ID to Sprite Position Mapping ---
// This map assumes a horizontal layout for the icons in 125805.webp,
// with each icon being 68px wide and 66px high.
// You might need to adjust the 'x' and 'y' values based on the exact layout of your sprite sheet.

$race_sprite_map = [
    // Alliance Races (assuming male icons in first row, female in second, or just sequential)
    1 => ['name' => 'Human', 'x' => 0, 'y' => 0], // Human Male
    3 => ['name' => 'Dwarf', 'x' => 68, 'y' => 0], // Dwarf Male
    4 => ['name' => 'Night Elf', 'x' => 136, 'y' => 0], // Night Elf Male
    7 => ['name' => 'Gnome', 'x' => 204, 'y' => 0], // Gnome Male
    11 => ['name' => 'Draenei', 'x' => 272, 'y' => 0], // Draenei Male
    22 => ['name' => 'Worgen', 'x' => 340, 'y' => 0], // Worgen Male
    25 => ['name' => 'Pandaren (A)', 'x' => 408, 'y' => 0], // Pandaren Alliance Male

    // Horde Races (assuming these start on the second row of the sprite sheet)
    2 => ['name' => 'Orc', 'x' => 0, 'y' => 66], // Orc Male
    5 => ['name' => 'Undead', 'x' => 68, 'y' => 66], // Undead Male
    6 => ['name' => 'Tauren', 'x' => 136, 'y' => 66], // Tauren Male
    8 => ['name' => 'Troll', 'x' => 204, 'y' => 66], // Troll Male
    10 => ['name' => 'Blood Elf', 'x' => 272, 'y' => 66], // Blood Elf Male
    9 => ['name' => 'Goblin', 'x' => 340, 'y' => 66], // Goblin Male
    24 => ['name' => 'Pandaren (N)', 'x' => 408, 'y' => 66], // Pandaren Neutral Male
    26 => ['name' => 'Pandaren (H)', 'x' => 476, 'y' => 66], // Pandaren Horde Male

    // Add more mappings as needed for other races and genders if they are on the sprite sheet
    // Example for a different row for female (if applicable in your sprite sheet):
    // 101 => ['name' => 'Human Female', 'x' => 0, 'y' => 132],
];


// Function to get the CSS class for a race icon
function getRaceIconClass($raceId, $genderId, $race_sprite_map) {
    // For simplicity, we're currently only mapping based on race ID.
    // If your sprite sheet has gender-specific icons, you'd need more complex logic here
    // to combine raceId and genderId to get the correct sprite position.
    $key = $raceId;
    // If you have separate entries for male/female pandaren, you might use:
    // if ($raceId == 24) { $key = ($genderId == 0) ? 24 : 25; } // Example for Pandaren male/female if they have unique sprite positions
    // Or if your sprite sheet uses a different ID for female versions:
    // if ($genderId == 1 && isset($race_sprite_map[$raceId + 100])) { $key = $raceId + 100; } // Example: Human (1) -> Human Female (101)

    /*
    if (isset($race_sprite_map[$key])) {
        $x = $race_sprite_map[$key]['x'];
        $y = $race_sprite_map[$key]['y'];
        // Return a custom class name that will be defined in CSS
        return "race-icon-{$raceId}-{$genderId} bg-position-x{$x}-y{$y}";
    }
    */
    // Fallback for unknown races or if no specific icon is found
    return ""; // Return empty string to not apply any specific icon class
}

?>