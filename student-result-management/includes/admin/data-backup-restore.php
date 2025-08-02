<?php
if (!defined('ABSPATH')) exit;

require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
$license_manager = new SRM_License_Manager();

$current_user_id = get_current_user_id();
$plugin_owner = get_option('srm_plugin_owner');
$is_owner = ($current_user_id == $plugin_owner);
$has_premium = $license_manager->has_premium_access();

$message = '';
$error = '';

// Handle backup creation
if ($_POST && isset($_POST['create_backup']) && $has_premium) {
    if (!wp_verify_nonce($_POST['srm_backup_nonce'], 'srm_backup_action')) {
        $error = __('Security check failed.', 'student-result-management');
    } else {
        $backup_result = srm_create_backup();
        if ($backup_result['success']) {
            $message = __('Backup created successfully!', 'student-result-management');
        } else {
            $error = $backup_result['message'];
        }
    }
}

// Handle backup restoration
if ($_POST && isset($_POST['restore_backup']) && $has_premium) {
    if (!wp_verify_nonce($_POST['srm_backup_nonce'], 'srm_backup_action')) {
        $error = __('Security check failed.', 'student-result-management');
    } else {
        $restore_result = srm_restore_backup($_FILES['backup_file']);
        if ($restore_result['success']) {
            $message = __('Backup restored successfully!', 'student-result-management');
        } else {
            $error = $restore_result['message'];
        }
    }
}

// Handle backup deletion
if ($_POST && isset($_POST['delete_backup']) && $has_premium) {
    if (!wp_verify_nonce($_POST['srm_backup_nonce'], 'srm_backup_action')) {
        $error = __('Security check failed.', 'student-result-management');
    } else {
        $backup_file = sanitize_text_field($_POST['backup_file']);
        $delete_result = srm_delete_backup($backup_file);
        if ($delete_result['success']) {
            $message = __('Backup deleted successfully!', 'student-result-management');
        } else {
            $error = $delete_result['message'];
        }
    }
}

// Get backup files
$backup_dir = WP_CONTENT_DIR . '/srm-backups/';
$backup_files = array();
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
            $file_path = $backup_dir . $file;
            $backup_files[] = array(
                'name' => $file,
                'size' => filesize($file_path),
                'date' => filemtime($file_path)
            );
        }
    }
    // Sort by date (newest first)
    usort($backup_files, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

function srm_create_backup() {
    global $wpdb;
    
    try {
        // Create backup directory if it doesn't exist
        $backup_dir = WP_CONTENT_DIR . '/srm-backups/';
        if (!is_dir($backup_dir)) {
            wp_mkdir_p($backup_dir);
        }
        
        // Get all plugin data
        $backup_data = array(
            'version' => '1.0.0',
            'created_at' => current_time('mysql'),
            'students' => $wpdb->get_results("SELECT * FROM {$wpdb->prefix}srm_students"),
            'results' => $wpdb->get_results("SELECT * FROM {$wpdb->prefix}srm_results"),
            'settings' => $wpdb->get_results("SELECT * FROM {$wpdb->prefix}srm_settings"),
            'payments' => $wpdb->get_results("SELECT * FROM {$wpdb->prefix}srm_payments"),
            'notifications' => $wpdb->get_results("SELECT * FROM {$wpdb->prefix}srm_notifications"),
            'options' => array(
                'srm_plugin_owner' => get_option('srm_plugin_owner'),
                'srm_license_status' => get_option('srm_license_status'),
                'srm_license_key' => get_option('srm_license_key'),
                'srm_email_notifications_enabled' => get_option('srm_email_notifications_enabled'),
                'srm_result_notification_enabled' => get_option('srm_result_notification_enabled'),
                'srm_reminder_notification_enabled' => get_option('srm_reminder_notification_enabled'),
                'srm_admin_email' => get_option('srm_admin_email'),
                'srm_email_template' => get_option('srm_email_template'),
                'srm_reminder_days' => get_option('srm_reminder_days')
            )
        );
        
        // Generate backup filename
        $backup_filename = 'srm_backup_' . date('Y-m-d_H-i-s') . '.json';
        $backup_file = $backup_dir . $backup_filename;
        
        // Write backup file
        $backup_json = json_encode($backup_data, JSON_PRETTY_PRINT);
        if (file_put_contents($backup_file, $backup_json) === false) {
            return array('success' => false, 'message' => __('Failed to write backup file.', 'student-result-management'));
        }
        
        return array('success' => true, 'message' => __('Backup created successfully.', 'student-result-management'));
        
    } catch (Exception $e) {
        return array('success' => false, 'message' => $e->getMessage());
    }
}

function srm_restore_backup($uploaded_file) {
    global $wpdb;
    
    try {
        if ($uploaded_file['error'] !== UPLOAD_ERR_OK) {
            return array('success' => false, 'message' => __('File upload failed.', 'student-result-management'));
        }
        
        $file_content = file_get_contents($uploaded_file['tmp_name']);
        $backup_data = json_decode($file_content, true);
        
        if (!$backup_data || !isset($backup_data['version'])) {
            return array('success' => false, 'message' => __('Invalid backup file format.', 'student-result-management'));
        }
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Clear existing data
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}srm_students");
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}srm_results");
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}srm_settings");
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}srm_payments");
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}srm_notifications");
            
            // Restore students
            if (isset($backup_data['students']) && is_array($backup_data['students'])) {
                foreach ($backup_data['students'] as $student) {
                    $wpdb->insert("{$wpdb->prefix}srm_students", $student);
                }
            }
            
            // Restore results
            if (isset($backup_data['results']) && is_array($backup_data['results'])) {
                foreach ($backup_data['results'] as $result) {
                    $wpdb->insert("{$wpdb->prefix}srm_results", $result);
                }
            }
            
            // Restore settings
            if (isset($backup_data['settings']) && is_array($backup_data['settings'])) {
                foreach ($backup_data['settings'] as $setting) {
                    $wpdb->insert("{$wpdb->prefix}srm_settings", $setting);
                }
            }
            
            // Restore payments
            if (isset($backup_data['payments']) && is_array($backup_data['payments'])) {
                foreach ($backup_data['payments'] as $payment) {
                    $wpdb->insert("{$wpdb->prefix}srm_payments", $payment);
                }
            }
            
            // Restore notifications
            if (isset($backup_data['notifications']) && is_array($backup_data['notifications'])) {
                foreach ($backup_data['notifications'] as $notification) {
                    $wpdb->insert("{$wpdb->prefix}srm_notifications", $notification);
                }
            }
            
            // Restore options
            if (isset($backup_data['options']) && is_array($backup_data['options'])) {
                foreach ($backup_data['options'] as $option_name => $option_value) {
                    update_option($option_name, $option_value);
                }
            }
            
            $wpdb->query('COMMIT');
            return array('success' => true, 'message' => __('Backup restored successfully.', 'student-result-management'));
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return array('success' => false, 'message' => $e->getMessage());
        }
        
    } catch (Exception $e) {
        return array('success' => false, 'message' => $e->getMessage());
    }
}

function srm_delete_backup($filename) {
    $backup_dir = WP_CONTENT_DIR . '/srm-backups/';
    $file_path = $backup_dir . sanitize_file_name($filename);
    
    if (file_exists($file_path) && unlink($file_path)) {
        return array('success' => true, 'message' => __('Backup deleted successfully.', 'student-result-management'));
    } else {
        return array('success' => false, 'message' => __('Failed to delete backup file.', 'student-result-management'));
    }
}

function srm_format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>

<div class="wrap srm-data-backup-restore">
    <h1><?php _e('Data Backup & Restore', 'student-result-management'); ?></h1>
    
    <?php if (!$has_premium): ?>
        <div class="notice notice-warning">
            <h3><?php _e('Premium Feature', 'student-result-management'); ?></h3>
            <p><?php _e('Data Backup & Restore is a premium feature. Please upgrade to access backup and restore functionality.', 'student-result-management'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=srm-premium'); ?>" class="button button-primary">
                <?php _e('Upgrade to Premium', 'student-result-management'); ?>
            </a>
        </div>
    <?php else: ?>
        
        <?php if ($message): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo esc_html($error); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="srm-backup-sections">
            <!-- Create Backup -->
            <div class="srm-backup-section">
                <h2><?php _e('Create Backup', 'student-result-management'); ?></h2>
                <p><?php _e('Create a complete backup of all your student data, results, settings, and configurations.', 'student-result-management'); ?></p>
                
                <form method="post" id="srm-create-backup-form">
                    <?php wp_nonce_field('srm_backup_action', 'srm_backup_nonce'); ?>
                    
                    <div class="srm-backup-info">
                        <h3><?php _e('Backup Includes:', 'student-result-management'); ?></h3>
                        <ul>
                            <li><?php _e('All student records', 'student-result-management'); ?></li>
                            <li><?php _e('All result records', 'student-result-management'); ?></li>
                            <li><?php _e('Plugin settings and configurations', 'student-result-management'); ?></li>
                            <li><?php _e('Payment records', 'student-result-management'); ?></li>
                            <li><?php _e('Notification history', 'student-result-management'); ?></li>
                            <li><?php _e('License information', 'student-result-management'); ?></li>
                        </ul>
                    </div>
                    
                    <p class="submit">
                        <button type="submit" name="create_backup" class="button button-primary">
                            <?php _e('Create Backup', 'student-result-management'); ?>
                        </button>
                    </p>
                </form>
            </div>
            
            <!-- Restore Backup -->
            <div class="srm-backup-section">
                <h2><?php _e('Restore Backup', 'student-result-management'); ?></h2>
                <p><?php _e('Restore your data from a previously created backup file. This will replace all current data.', 'student-result-management'); ?></p>
                
                <div class="srm-warning">
                    <strong><?php _e('⚠️ Warning:', 'student-result-management'); ?></strong>
                    <?php _e('Restoring a backup will replace all current data. Make sure to create a backup of your current data before proceeding.', 'student-result-management'); ?>
                </div>
                
                <form method="post" enctype="multipart/form-data" id="srm-restore-backup-form">
                    <?php wp_nonce_field('srm_backup_action', 'srm_backup_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="backup_file"><?php _e('Backup File', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <input type="file" id="backup_file" name="backup_file" accept=".json" required>
                                <p class="description"><?php _e('Select a backup file (.json format)', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" name="restore_backup" class="button button-secondary" onclick="return confirm('<?php _e('Are you sure you want to restore this backup? This will replace all current data.', 'student-result-management'); ?>')">
                            <?php _e('Restore Backup', 'student-result-management'); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>
        
        <!-- Backup History -->
        <div class="srm-backup-section">
            <h2><?php _e('Backup History', 'student-result-management'); ?></h2>
            
            <?php if (!empty($backup_files)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Backup File', 'student-result-management'); ?></th>
                            <th><?php _e('Size', 'student-result-management'); ?></th>
                            <th><?php _e('Created Date', 'student-result-management'); ?></th>
                            <th><?php _e('Actions', 'student-result-management'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backup_files as $backup): ?>
                            <tr>
                                <td><?php echo esc_html($backup['name']); ?></td>
                                <td><?php echo srm_format_file_size($backup['size']); ?></td>
                                <td><?php echo esc_html(date('M j, Y H:i', $backup['date'])); ?></td>
                                <td>
                                    <a href="<?php echo content_url('srm-backups/' . $backup['name']); ?>" class="button button-small" download>
                                        <?php _e('Download', 'student-result-management'); ?>
                                    </a>
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field('srm_backup_action', 'srm_backup_nonce'); ?>
                                        <input type="hidden" name="backup_file" value="<?php echo esc_attr($backup['name']); ?>">
                                        <button type="submit" name="delete_backup" class="button button-small button-link-delete" onclick="return confirm('<?php _e('Are you sure you want to delete this backup?', 'student-result-management'); ?>')">
                                            <?php _e('Delete', 'student-result-management'); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No backup files found.', 'student-result-management'); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Auto Backup Settings -->
        <div class="srm-backup-section">
            <h2><?php _e('Auto Backup Settings', 'student-result-management'); ?></h2>
            <p><?php _e('Configure automatic backup settings to ensure your data is regularly backed up.', 'student-result-management'); ?></p>
            
            <form method="post" id="srm-auto-backup-form">
                <?php wp_nonce_field('srm_backup_action', 'srm_backup_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="auto_backup_enabled"><?php _e('Enable Auto Backup', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="auto_backup_enabled" name="auto_backup_enabled" value="1" <?php checked(get_option('srm_auto_backup_enabled', 0), 1); ?>>
                            <p class="description"><?php _e('Automatically create backups at scheduled intervals', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="auto_backup_frequency"><?php _e('Backup Frequency', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <select id="auto_backup_frequency" name="auto_backup_frequency">
                                <option value="daily" <?php selected(get_option('srm_auto_backup_frequency', 'weekly'), 'daily'); ?>><?php _e('Daily', 'student-result-management'); ?></option>
                                <option value="weekly" <?php selected(get_option('srm_auto_backup_frequency', 'weekly'), 'weekly'); ?>><?php _e('Weekly', 'student-result-management'); ?></option>
                                <option value="monthly" <?php selected(get_option('srm_auto_backup_frequency', 'weekly'), 'monthly'); ?>><?php _e('Monthly', 'student-result-management'); ?></option>
                            </select>
                            <p class="description"><?php _e('How often to create automatic backups', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="auto_backup_retention"><?php _e('Retention Period', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="auto_backup_retention" name="auto_backup_retention" value="<?php echo esc_attr(get_option('srm_auto_backup_retention', 30)); ?>" min="1" max="365" class="small-text">
                            <p class="description"><?php _e('Number of days to keep automatic backups (1-365)', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="save_auto_backup_settings" class="button button-primary">
                        <?php _e('Save Settings', 'student-result-management'); ?>
                    </button>
                </p>
            </form>
        </div>
        
        <style>
        .srm-backup-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .srm-backup-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        
        .srm-backup-info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        
        .srm-backup-info ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        
        .srm-backup-info li {
            margin-bottom: 5px;
        }
        
        .srm-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        
        @media (max-width: 768px) {
            .srm-backup-sections {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Confirm restore action
            $('#srm-restore-backup-form').on('submit', function(e) {
                if (!confirm('Are you sure you want to restore this backup? This will replace all current data.')) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Auto backup settings
            $('#srm-auto-backup-form').on('submit', function(e) {
                var retention = $('#auto_backup_retention').val();
                if (retention < 1 || retention > 365) {
                    alert('Retention period must be between 1 and 365 days.');
                    e.preventDefault();
                    return false;
                }
            });
        });
        </script>
        
    <?php endif; ?>
</div>