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

// Handle form submissions
if ($_POST && isset($_POST['save_email_settings']) && $has_premium) {
    if (!wp_verify_nonce($_POST['srm_email_nonce'], 'srm_email_settings_action')) {
        $error = __('Security check failed.', 'student-result-management');
    } else {
        // Save email settings
        update_option('srm_email_notifications_enabled', isset($_POST['email_notifications_enabled']) ? 1 : 0);
        update_option('srm_result_notification_enabled', isset($_POST['result_notification_enabled']) ? 1 : 0);
        update_option('srm_reminder_notification_enabled', isset($_POST['reminder_notification_enabled']) ? 1 : 0);
        update_option('srm_admin_email', sanitize_email($_POST['admin_email']));
        update_option('srm_email_template', sanitize_textarea_field($_POST['email_template']));
        update_option('srm_reminder_days', intval($_POST['reminder_days']));
        
        $message = __('Email settings saved successfully!', 'student-result-management');
    }
}

// Handle test email
if ($_POST && isset($_POST['send_test_email']) && $has_premium) {
    if (!wp_verify_nonce($_POST['srm_email_nonce'], 'srm_email_settings_action')) {
        $error = __('Security check failed.', 'student-result-management');
    } else {
        $test_email = sanitize_email($_POST['test_email']);
        if (is_email($test_email)) {
            $sent = srm_send_test_email($test_email);
            if ($sent) {
                $message = __('Test email sent successfully!', 'student-result-management');
            } else {
                $error = __('Failed to send test email. Please check your email configuration.', 'student-result-management');
            }
        } else {
            $error = __('Please enter a valid email address.', 'student-result-management');
        }
    }
}

// Get current settings
$email_notifications_enabled = get_option('srm_email_notifications_enabled', 0);
$result_notification_enabled = get_option('srm_result_notification_enabled', 0);
$reminder_notification_enabled = get_option('srm_reminder_notification_enabled', 0);
$admin_email = get_option('srm_admin_email', get_option('admin_email'));
$email_template = get_option('srm_email_template', 'Dear {student_name},

Your result for {exam_name} has been published.

Roll Number: {roll_number}
Class: {class}
Section: {section}
Total Marks: {total_marks}
Obtained Marks: {obtained_marks}
Percentage: {percentage}%
Grade: {grade}
Status: {status}

You can view your complete result at: {result_url}

Best regards,
{site_name}');
$reminder_days = get_option('srm_reminder_days', 7);

// Get notification history
global $wpdb;
$notification_history = $wpdb->get_results("
    SELECT * FROM {$wpdb->prefix}srm_notifications 
    ORDER BY created_at DESC 
    LIMIT 20
");

function srm_send_test_email($email) {
    $subject = 'Test Email - Student Result Management System';
    $message = 'This is a test email from your Student Result Management System. If you received this email, your email notifications are working correctly.';
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    return wp_mail($email, $subject, $message, $headers);
}
?>

<div class="wrap srm-email-notifications">
    <h1><?php _e('Email Notifications', 'student-result-management'); ?></h1>
    
    <?php if (!$has_premium): ?>
        <div class="notice notice-warning">
            <h3><?php _e('Premium Feature', 'student-result-management'); ?></h3>
            <p><?php _e('Email Notifications is a premium feature. Please upgrade to access automated email notifications.', 'student-result-management'); ?></p>
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
        
        <div class="srm-email-sections">
            <!-- Email Settings -->
            <div class="srm-email-section">
                <h2><?php _e('Email Settings', 'student-result-management'); ?></h2>
                <form method="post" id="srm-email-settings-form">
                    <?php wp_nonce_field('srm_email_settings_action', 'srm_email_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="email_notifications_enabled"><?php _e('Enable Email Notifications', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="email_notifications_enabled" name="email_notifications_enabled" value="1" <?php checked($email_notifications_enabled, 1); ?>>
                                <p class="description"><?php _e('Enable or disable all email notifications', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="result_notification_enabled"><?php _e('Result Notifications', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="result_notification_enabled" name="result_notification_enabled" value="1" <?php checked($result_notification_enabled, 1); ?>>
                                <p class="description"><?php _e('Send email notifications when new results are added', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="reminder_notification_enabled"><?php _e('Reminder Notifications', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="reminder_notification_enabled" name="reminder_notification_enabled" value="1" <?php checked($reminder_notification_enabled, 1); ?>>
                                <p class="description"><?php _e('Send reminder emails for upcoming exams', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="admin_email"><?php _e('Admin Email', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <input type="email" id="admin_email" name="admin_email" value="<?php echo esc_attr($admin_email); ?>" class="regular-text">
                                <p class="description"><?php _e('Email address for admin notifications', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="reminder_days"><?php _e('Reminder Days', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="reminder_days" name="reminder_days" value="<?php echo esc_attr($reminder_days); ?>" min="1" max="30" class="small-text">
                                <p class="description"><?php _e('Days before exam to send reminder emails', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" name="save_email_settings" class="button button-primary">
                            <?php _e('Save Settings', 'student-result-management'); ?>
                        </button>
                    </p>
                </form>
            </div>
            
            <!-- Email Template -->
            <div class="srm-email-section">
                <h2><?php _e('Email Template', 'student-result-management'); ?></h2>
                <p><?php _e('Customize the email template for result notifications. Use the following placeholders:', 'student-result-management'); ?></p>
                
                <div class="srm-placeholders">
                    <strong><?php _e('Available Placeholders:', 'student-result-management'); ?></strong>
                    <ul>
                        <li><code>{student_name}</code> - Student's full name</li>
                        <li><code>{roll_number}</code> - Student's roll number</li>
                        <li><code>{class}</code> - Student's class</li>
                        <li><code>{section}</code> - Student's section</li>
                        <li><code>{exam_name}</code> - Name of the exam</li>
                        <li><code>{exam_date}</code> - Exam date</li>
                        <li><code>{total_marks}</code> - Total marks</li>
                        <li><code>{obtained_marks}</code> - Obtained marks</li>
                        <li><code>{percentage}</code> - Percentage</li>
                        <li><code>{grade}</code> - Grade</li>
                        <li><code>{status}</code> - Status (Pass/Fail)</li>
                        <li><code>{result_url}</code> - URL to view result</li>
                        <li><code>{site_name}</code> - Site name</li>
                    </ul>
                </div>
                
                <form method="post" id="srm-email-template-form">
                    <?php wp_nonce_field('srm_email_settings_action', 'srm_email_nonce'); ?>
                    
                    <textarea id="email_template" name="email_template" rows="15" cols="80" class="large-text code"><?php echo esc_textarea($email_template); ?></textarea>
                    
                    <p class="submit">
                        <button type="submit" name="save_email_settings" class="button button-primary">
                            <?php _e('Save Template', 'student-result-management'); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>
        
        <!-- Test Email -->
        <div class="srm-email-section">
            <h2><?php _e('Test Email', 'student-result-management'); ?></h2>
            <p><?php _e('Send a test email to verify your email configuration is working correctly.', 'student-result-management'); ?></p>
            
            <form method="post" id="srm-test-email-form">
                <?php wp_nonce_field('srm_email_settings_action', 'srm_email_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="test_email"><?php _e('Test Email Address', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="test_email" name="test_email" value="" class="regular-text" required>
                            <p class="description"><?php _e('Enter an email address to send a test email', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="send_test_email" class="button button-secondary">
                        <?php _e('Send Test Email', 'student-result-management'); ?>
                    </button>
                </p>
            </form>
        </div>
        
        <!-- Notification History -->
        <div class="srm-email-section">
            <h2><?php _e('Notification History', 'student-result-management'); ?></h2>
            
            <?php if (!empty($notification_history)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Student', 'student-result-management'); ?></th>
                            <th><?php _e('Type', 'student-result-management'); ?></th>
                            <th><?php _e('Email', 'student-result-management'); ?></th>
                            <th><?php _e('Status', 'student-result-management'); ?></th>
                            <th><?php _e('Date', 'student-result-management'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notification_history as $notification): ?>
                            <tr>
                                <td><?php echo esc_html($notification->student_name); ?></td>
                                <td><?php echo esc_html(ucfirst($notification->notification_type)); ?></td>
                                <td><?php echo esc_html($notification->email); ?></td>
                                <td>
                                    <span class="srm-status srm-status-<?php echo $notification->status; ?>">
                                        <?php echo esc_html(ucfirst($notification->status)); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(date('M j, Y H:i', strtotime($notification->created_at))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No notification history found.', 'student-result-management'); ?></p>
            <?php endif; ?>
        </div>
        
        <style>
        .srm-email-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .srm-email-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        
        .srm-placeholders {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .srm-placeholders ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        
        .srm-placeholders li {
            margin-bottom: 5px;
        }
        
        .srm-placeholders code {
            background: #e9ecef;
            padding: 2px 4px;
            border-radius: 3px;
        }
        
        .srm-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        
        .srm-status-sent { background: #d4edda; color: #155724; }
        .srm-status-failed { background: #f8d7da; color: #721c24; }
        .srm-status-pending { background: #fff3cd; color: #856404; }
        
        @media (max-width: 768px) {
            .srm-email-sections {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Auto-save email template
            var templateTimeout;
            $('#email_template').on('input', function() {
                clearTimeout(templateTimeout);
                templateTimeout = setTimeout(function() {
                    $('#srm-email-template-form').submit();
                }, 2000);
            });
            
            // Test email form
            $('#srm-test-email-form').on('submit', function(e) {
                var email = $('#test_email').val();
                if (!email) {
                    alert('Please enter a valid email address.');
                    e.preventDefault();
                    return false;
                }
            });
        });
        </script>
        
    <?php endif; ?>
</div>