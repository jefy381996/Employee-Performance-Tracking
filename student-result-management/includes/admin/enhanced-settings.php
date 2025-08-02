<?php
if (!defined('ABSPATH')) exit;

// Include license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
$license_manager = new SRM_License_Manager();

$current_user_id = get_current_user_id();
$plugin_owner = get_option('srm_plugin_owner');
$is_owner = ($current_user_id == $plugin_owner);

// Handle form submission
if (isset($_POST['srm_save_settings'])) {
    check_admin_referer('srm_settings_nonce', 'srm_settings_nonce');
    
    // Save general settings
    update_option('srm_school_name', sanitize_text_field($_POST['school_name']));
    update_option('srm_school_logo', esc_url_raw($_POST['school_logo']));
    update_option('srm_admin_email', sanitize_email($_POST['admin_email']));
    update_option('srm_result_template', sanitize_text_field($_POST['result_template']));
    update_option('srm_grade_system', sanitize_text_field($_POST['grade_system']));
    update_option('srm_passing_marks', intval($_POST['passing_marks']));
    
    // Save payment settings (only for plugin owner)
    if ($is_owner) {
        update_option('srm_stripe_secret_key', sanitize_text_field($_POST['stripe_secret_key']));
        update_option('srm_stripe_publishable_key', sanitize_text_field($_POST['stripe_publishable_key']));
        update_option('srm_paypal_client_id', sanitize_text_field($_POST['paypal_client_id']));
        update_option('srm_paypal_secret', sanitize_text_field($_POST['paypal_secret']));
        update_option('srm_premium_price', floatval($_POST['premium_price']));
        update_option('srm_currency', sanitize_text_field($_POST['currency']));
    }
    
    echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'student-result-management') . '</p></div>';
}

// Get current settings
$school_name = get_option('srm_school_name', get_bloginfo('name'));
$school_logo = get_option('srm_school_logo', '');
$admin_email = get_option('srm_admin_email', get_option('admin_email'));
$result_template = get_option('srm_result_template', 'default');
$grade_system = get_option('srm_grade_system', 'letter');
$passing_marks = get_option('srm_passing_marks', 40);
$stripe_secret_key = get_option('srm_stripe_secret_key', '');
$stripe_publishable_key = get_option('srm_stripe_publishable_key', '');
$paypal_client_id = get_option('srm_paypal_client_id', '');
$paypal_secret = get_option('srm_paypal_secret', '');
$premium_price = get_option('srm_premium_price', 49);
$currency = get_option('srm_currency', 'USD');
?>

<div class="wrap srm-settings">
    <h1><?php _e('Settings', 'student-result-management'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('srm_settings_nonce', 'srm_settings_nonce'); ?>
        
        <div class="srm-settings-container">
            <!-- General Settings -->
            <div class="srm-settings-section">
                <h2><?php _e('General Settings', 'student-result-management'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="school_name"><?php _e('School Name', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="school_name" name="school_name" class="regular-text" 
                                   value="<?php echo esc_attr($school_name); ?>">
                            <p class="description"><?php _e('This will be displayed on result cards and reports.', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="school_logo"><?php _e('School Logo URL', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="school_logo" name="school_logo" class="regular-text" 
                                   value="<?php echo esc_attr($school_logo); ?>">
                            <p class="description"><?php _e('URL of your school logo (optional).', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="admin_email"><?php _e('Admin Email', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="admin_email" name="admin_email" class="regular-text" 
                                   value="<?php echo esc_attr($admin_email); ?>">
                            <p class="description"><?php _e('Email address for notifications and support.', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="result_template"><?php _e('Result Template', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <select id="result_template" name="result_template">
                                <option value="default" <?php selected($result_template, 'default'); ?>>
                                    <?php _e('Default Template', 'student-result-management'); ?>
                                </option>
                                <option value="modern" <?php selected($result_template, 'modern'); ?>>
                                    <?php _e('Modern Template', 'student-result-management'); ?>
                                </option>
                                <option value="classic" <?php selected($result_template, 'classic'); ?>>
                                    <?php _e('Classic Template', 'student-result-management'); ?>
                                </option>
                            </select>
                            <p class="description"><?php _e('Choose the template for displaying results.', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="grade_system"><?php _e('Grading System', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <select id="grade_system" name="grade_system">
                                <option value="letter" <?php selected($grade_system, 'letter'); ?>>
                                    <?php _e('Letter Grades (A, B, C, D, F)', 'student-result-management'); ?>
                                </option>
                                <option value="number" <?php selected($grade_system, 'number'); ?>>
                                    <?php _e('Number Grades (1-10)', 'student-result-management'); ?>
                                </option>
                                <option value="percentage" <?php selected($grade_system, 'percentage'); ?>>
                                    <?php _e('Percentage Only', 'student-result-management'); ?>
                                </option>
                            </select>
                            <p class="description"><?php _e('Choose the grading system for your institution.', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="passing_marks"><?php _e('Passing Marks (%)', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="passing_marks" name="passing_marks" min="0" max="100" 
                                   value="<?php echo esc_attr($passing_marks); ?>">
                            <p class="description"><?php _e('Minimum percentage required to pass an exam.', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <?php if ($is_owner): ?>
                <!-- Payment Gateway Settings (Owner Only) -->
                <div class="srm-settings-section">
                    <h2><?php _e('Payment Gateway Settings', 'student-result-management'); ?></h2>
                    <p class="description"><?php _e('Configure payment gateways for premium license sales. Only the plugin owner can access these settings.', 'student-result-management'); ?></p>
                    
                    <h3><?php _e('Stripe Configuration', 'student-result-management'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="stripe_secret_key"><?php _e('Stripe Secret Key', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <input type="password" id="stripe_secret_key" name="stripe_secret_key" class="regular-text" 
                                       value="<?php echo esc_attr($stripe_secret_key); ?>">
                                <p class="description"><?php _e('Your Stripe secret key for processing payments.', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="stripe_publishable_key"><?php _e('Stripe Publishable Key', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="stripe_publishable_key" name="stripe_publishable_key" class="regular-text" 
                                       value="<?php echo esc_attr($stripe_publishable_key); ?>">
                                <p class="description"><?php _e('Your Stripe publishable key for the frontend.', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <h3><?php _e('PayPal Configuration', 'student-result-management'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="paypal_client_id"><?php _e('PayPal Client ID', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="paypal_client_id" name="paypal_client_id" class="regular-text" 
                                       value="<?php echo esc_attr($paypal_client_id); ?>">
                                <p class="description"><?php _e('Your PayPal client ID for processing payments.', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="paypal_secret"><?php _e('PayPal Secret', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <input type="password" id="paypal_secret" name="paypal_secret" class="regular-text" 
                                       value="<?php echo esc_attr($paypal_secret); ?>">
                                <p class="description"><?php _e('Your PayPal secret key.', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <h3><?php _e('Pricing Settings', 'student-result-management'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="premium_price"><?php _e('Premium License Price', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="premium_price" name="premium_price" min="0" step="0.01" 
                                       value="<?php echo esc_attr($premium_price); ?>">
                                <p class="description"><?php _e('Price for premium license in your selected currency.', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="currency"><?php _e('Currency', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <select id="currency" name="currency">
                                    <option value="USD" <?php selected($currency, 'USD'); ?>>USD - US Dollar</option>
                                    <option value="EUR" <?php selected($currency, 'EUR'); ?>>EUR - Euro</option>
                                    <option value="GBP" <?php selected($currency, 'GBP'); ?>>GBP - British Pound</option>
                                    <option value="CAD" <?php selected($currency, 'CAD'); ?>>CAD - Canadian Dollar</option>
                                    <option value="AUD" <?php selected($currency, 'AUD'); ?>>AUD - Australian Dollar</option>
                                    <option value="INR" <?php selected($currency, 'INR'); ?>>INR - Indian Rupee</option>
                                </select>
                                <p class="description"><?php _e('Currency for premium license sales.', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- License Management (Owner Only) -->
                <div class="srm-settings-section">
                    <h2><?php _e('License Management', 'student-result-management'); ?></h2>
                    
                    <div class="srm-license-info">
                        <h3><?php _e('Current License Status', 'student-result-management'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Plugin Owner', 'student-result-management'); ?></th>
                                <td>
                                    <?php 
                                    $owner_user = get_user_by('id', $plugin_owner);
                                    echo esc_html($owner_user ? $owner_user->display_name : 'Unknown');
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('License Status', 'student-result-management'); ?></th>
                                <td>
                                    <span class="srm-status srm-status-premium">
                                        <?php _e('Premium (Owner)', 'student-result-management'); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Access Level', 'student-result-management'); ?></th>
                                <td><?php _e('Full access to all features', 'student-result-management'); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="srm-license-actions">
                        <h3><?php _e('License Actions', 'student-result-management'); ?></h3>
                        <p><?php _e('As the plugin owner, you can manage licenses and view payment history.', 'student-result-management'); ?></p>
                        
                        <a href="<?php echo admin_url('admin.php?page=srm-premium'); ?>" class="button button-primary">
                            <?php _e('Manage Licenses', 'student-result-management'); ?>
                        </a>
                        
                        <a href="<?php echo admin_url('admin.php?page=srm-premium#payment-history'); ?>" class="button button-secondary">
                            <?php _e('View Payment History', 'student-result-management'); ?>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Non-Owner License Info -->
                <div class="srm-settings-section">
                    <h2><?php _e('License Information', 'student-result-management'); ?></h2>
                    
                    <div class="srm-license-info">
                        <h3><?php _e('Your License Status', 'student-result-management'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('License Status', 'student-result-management'); ?></th>
                                <td>
                                    <?php 
                                    $license_status = $license_manager->get_license_status();
                                    $status_class = $license_status === 'premium' ? 'premium' : 'free';
                                    ?>
                                    <span class="srm-status srm-status-<?php echo $status_class; ?>">
                                        <?php echo ucfirst($license_status); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Access Level', 'student-result-management'); ?></th>
                                <td>
                                    <?php if ($license_manager->has_premium_access()): ?>
                                        <?php _e('Premium - Full access to all features', 'student-result-management'); ?>
                                    <?php else: ?>
                                        <?php _e('Free - Basic features only', 'student-result-management'); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <?php if (!$license_manager->has_premium_access()): ?>
                        <div class="srm-upgrade-prompt">
                            <h3><?php _e('Upgrade to Premium', 'student-result-management'); ?></h3>
                            <p><?php _e('Get access to all premium features including PDF generation, CSV import/export, and more.', 'student-result-management'); ?></p>
                            
                            <a href="<?php echo admin_url('admin.php?page=srm-premium'); ?>" class="button button-primary">
                                <?php _e('Upgrade Now', 'student-result-management'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <p class="submit">
            <input type="submit" name="srm_save_settings" class="button button-primary" 
                   value="<?php _e('Save Settings', 'student-result-management'); ?>">
        </p>
    </form>
</div>

<style>
.srm-settings {
    max-width: 1200px;
}

.srm-settings-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 30px;
}

.srm-settings-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.srm-settings-section h2 {
    margin-top: 0;
    color: #0073aa;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 10px;
}

.srm-settings-section h3 {
    color: #333;
    margin-top: 25px;
    margin-bottom: 15px;
}

.srm-license-info {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 20px;
    margin: 20px 0;
}

.srm-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: bold;
    text-transform: uppercase;
    font-size: 12px;
}

.srm-status-free { background: #f0f0f0; color: #666; }
.srm-status-premium { background: #d4edda; color: #155724; }

.srm-license-actions {
    margin-top: 20px;
}

.srm-license-actions .button {
    margin-right: 10px;
    margin-bottom: 10px;
}

.srm-upgrade-prompt {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 6px;
    padding: 20px;
    margin: 20px 0;
    text-align: center;
}

.srm-upgrade-prompt h3 {
    color: #856404;
    margin-top: 0;
}

.srm-upgrade-prompt p {
    color: #856404;
    margin-bottom: 20px;
}

@media (min-width: 768px) {
    .srm-settings-container {
        grid-template-columns: 2fr 1fr;
    }
}
</style>