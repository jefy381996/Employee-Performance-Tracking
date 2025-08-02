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

// Handle testing mode activation
if ($_POST && isset($_POST['activate_testing_mode']) && $is_owner) {
    if (!wp_verify_nonce($_POST['srm_testing_nonce'], 'srm_testing_action')) {
        $error = __('Security check failed.', 'student-result-management');
    } else {
        $test_mode = sanitize_text_field($_POST['test_mode']);
        update_option('srm_testing_mode', $test_mode);
        update_option('srm_testing_user_id', $current_user_id);
        
        $message = __('Testing mode activated successfully! You can now test ' . $test_mode . ' features.', 'student-result-management');
    }
}

// Handle testing mode deactivation
if ($_POST && isset($_POST['deactivate_testing_mode']) && $is_owner) {
    if (!wp_verify_nonce($_POST['srm_testing_nonce'], 'srm_testing_action')) {
        $error = __('Security check failed.', 'student-result-management');
    } else {
        delete_option('srm_testing_mode');
        delete_option('srm_testing_user_id');
        
        $message = __('Testing mode deactivated. You are back to owner mode with full access.', 'student-result-management');
    }
}

$current_test_mode = get_option('srm_testing_mode', '');
$testing_user_id = get_option('srm_testing_user_id', 0);
$is_testing = ($testing_user_id == $current_user_id && !empty($current_test_mode));
?>

<div class="wrap srm-testing-mode">
    <h1><?php _e('Testing Mode', 'student-result-management'); ?></h1>
    
    <?php if (!$is_owner): ?>
        <div class="notice notice-warning">
            <h3><?php _e('Access Restricted', 'student-result-management'); ?></h3>
            <p><?php _e('Only the plugin owner can access testing mode.', 'student-result-management'); ?></p>
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
        
        <div class="srm-testing-overview">
            <div class="srm-testing-card">
                <h2><?php _e('Current Status', 'student-result-management'); ?></h2>
                <div class="srm-status-info">
                    <p><strong><?php _e('Role:', 'student-result-management'); ?></strong> 
                        <span class="srm-role srm-role-owner"><?php _e('Plugin Owner', 'student-result-management'); ?></span>
                    </p>
                    <p><strong><?php _e('Testing Mode:', 'student-result-management'); ?></strong> 
                        <?php if ($is_testing): ?>
                            <span class="srm-testing-active"><?php echo esc_html(ucfirst($current_test_mode)); ?></span>
                        <?php else: ?>
                            <span class="srm-testing-inactive"><?php _e('Inactive', 'student-result-management'); ?></span>
                        <?php endif; ?>
                    </p>
                    <p><strong><?php _e('Premium Access:', 'student-result-management'); ?></strong> 
                        <?php if ($has_premium): ?>
                            <span class="srm-status srm-status-premium"><?php _e('Active', 'student-result-management'); ?></span>
                        <?php else: ?>
                            <span class="srm-status srm-status-free"><?php _e('Inactive', 'student-result-management'); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
        
        <?php if ($is_testing): ?>
            <!-- Testing Mode Active -->
            <div class="srm-testing-section">
                <h2><?php _e('Testing Mode Active', 'student-result-management'); ?></h2>
                <p><?php _e('You are currently testing the plugin as a ' . $current_test_mode . ' user. This allows you to see exactly what other users will experience.', 'student-result-management'); ?></p>
                
                <div class="srm-testing-features">
                    <h3><?php _e('What You Can Test:', 'student-result-management'); ?></h3>
                    <ul>
                        <li><?php _e('Free vs Premium feature access', 'student-result-management'); ?></li>
                        <li><?php _e('Upgrade prompts and restrictions', 'student-result-management'); ?></li>
                        <li><?php _e('Payment flow and license activation', 'student-result-management'); ?></li>
                        <li><?php _e('User experience for different user types', 'student-result-management'); ?></li>
                    </ul>
                </div>
                
                <form method="post" id="srm-deactivate-testing-form">
                    <?php wp_nonce_field('srm_testing_action', 'srm_testing_nonce'); ?>
                    <p class="submit">
                        <button type="submit" name="deactivate_testing_mode" class="button button-primary">
                            <?php _e('Deactivate Testing Mode', 'student-result-management'); ?>
                        </button>
                    </p>
                </form>
            </div>
        <?php else: ?>
            <!-- Testing Mode Setup -->
            <div class="srm-testing-section">
                <h2><?php _e('Activate Testing Mode', 'student-result-management'); ?></h2>
                <p><?php _e('Testing mode allows you to temporarily switch to a different user role to test how the plugin behaves for different types of users.', 'student-result-management'); ?></p>
                
                <form method="post" id="srm-activate-testing-form">
                    <?php wp_nonce_field('srm_testing_action', 'srm_testing_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="test_mode"><?php _e('Test Mode', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <select id="test_mode" name="test_mode" required>
                                    <option value=""><?php _e('Select Test Mode', 'student-result-management'); ?></option>
                                    <option value="free"><?php _e('Free User', 'student-result-management'); ?></option>
                                    <option value="premium"><?php _e('Premium User', 'student-result-management'); ?></option>
                                    <option value="expired"><?php _e('Expired License User', 'student-result-management'); ?></option>
                                </select>
                                <p class="description"><?php _e('Choose the user type you want to test as', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" name="activate_testing_mode" class="button button-primary">
                            <?php _e('Activate Testing Mode', 'student-result-management'); ?>
                        </button>
                    </p>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- Feature Testing Guide -->
        <div class="srm-testing-section">
            <h2><?php _e('Feature Testing Guide', 'student-result-management'); ?></h2>
            
            <div class="srm-testing-guide">
                <h3><?php _e('Free Features (Always Available):', 'student-result-management'); ?></h3>
                <ul>
                    <li><?php _e('Student Management (Add, Edit, Delete)', 'student-result-management'); ?></li>
                    <li><?php _e('Result Management (Add, Edit, Delete)', 'student-result-management'); ?></li>
                    <li><?php _e('Result Lookup (Frontend)', 'student-result-management'); ?></li>
                    <li><?php _e('Basic Dashboard', 'student-result-management'); ?></li>
                    <li><?php _e('Basic Settings', 'student-result-management'); ?></li>
                </ul>
                
                <h3><?php _e('Premium Features (Require License):', 'student-result-management'); ?></h3>
                <ul>
                    <li><?php _e('CSV Import/Export', 'student-result-management'); ?></li>
                    <li><?php _e('Advanced Analytics', 'student-result-management'); ?></li>
                    <li><?php _e('Email Notifications', 'student-result-management'); ?></li>
                    <li><?php _e('Data Backup & Restore', 'student-result-management'); ?></li>
                    <li><?php _e('Custom Templates', 'student-result-management'); ?></li>
                    <li><?php _e('Student Profile Images', 'student-result-management'); ?></li>
                    <li><?php _e('PDF Certificate Upload', 'student-result-management'); ?></li>
                    <li><?php _e('Certificate Download', 'student-result-management'); ?></li>
                </ul>
                
                <h3><?php _e('Testing Steps:', 'student-result-management'); ?></h3>
                <ol>
                    <li><?php _e('Activate testing mode as a "Free User"', 'student-result-management'); ?></li>
                    <li><?php _e('Try to access premium features - you should see upgrade prompts', 'student-result-management'); ?></li>
                    <li><?php _e('Test the payment flow and license activation', 'student-result-management'); ?></li>
                    <li><?php _e('Switch to "Premium User" mode and verify all features work', 'student-result-management'); ?></li>
                    <li><?php _e('Test "Expired License" mode to see how expired users are handled', 'student-result-management'); ?></li>
                </ol>
            </div>
        </div>
        
        <!-- Quick Test Buttons -->
        <div class="srm-testing-section">
            <h2><?php _e('Quick Test Actions', 'student-result-management'); ?></h2>
            
            <div class="srm-quick-tests">
                <a href="<?php echo admin_url('admin.php?page=srm-csv-import-export'); ?>" class="button button-secondary">
                    <?php _e('Test CSV Import/Export', 'student-result-management'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=srm-advanced-analytics'); ?>" class="button button-secondary">
                    <?php _e('Test Advanced Analytics', 'student-result-management'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=srm-email-notifications'); ?>" class="button button-secondary">
                    <?php _e('Test Email Notifications', 'student-result-management'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=srm-data-backup-restore'); ?>" class="button button-secondary">
                    <?php _e('Test Data Backup & Restore', 'student-result-management'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=srm-custom-templates'); ?>" class="button button-secondary">
                    <?php _e('Test Custom Templates', 'student-result-management'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=srm-premium'); ?>" class="button button-secondary">
                    <?php _e('Test Premium Features Page', 'student-result-management'); ?>
                </a>
            </div>
        </div>
        
        <style>
        .srm-testing-overview {
            margin-bottom: 30px;
        }
        
        .srm-testing-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .srm-status-info p {
            margin: 10px 0;
        }
        
        .srm-role-owner {
            background: #cce5ff;
            color: #004085;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .srm-testing-active {
            background: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .srm-testing-inactive {
            background: #f8d7da;
            color: #721c24;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .srm-testing-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .srm-testing-features ul {
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .srm-testing-guide ul, .srm-testing-guide ol {
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .srm-testing-guide h3 {
            margin-top: 25px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .srm-quick-tests {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .srm-quick-tests .button {
            margin: 0;
        }
        </style>
        
    <?php endif; ?>
</div>