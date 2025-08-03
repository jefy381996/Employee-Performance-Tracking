<?php
/**
 * License Request Form
 * Allows users to request premium licenses with domain information
 */

if (!defined('ABSPATH')) exit;

// Include license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';

$license_manager = new SRM_License_Manager();
$domain_info = $license_manager->get_domain_info();
$license_info = $license_manager->get_license_info();

$message = '';
$error = '';

// Handle license request submission
if ($_POST && isset($_POST['submit_license_request'])) {
    if (!wp_verify_nonce($_POST['srm_license_request_nonce'], 'srm_license_request')) {
        $error = __('Security check failed.', 'student-result-management');
    } else {
        $customer_name = sanitize_text_field($_POST['customer_name']);
        $customer_email = sanitize_email($_POST['customer_email']);
        $customer_phone = sanitize_text_field($_POST['customer_phone']);
        $domain_name = sanitize_text_field($_POST['domain_name']);
        $additional_info = sanitize_textarea_field($_POST['additional_info']);
        
        if (empty($customer_name) || empty($customer_email) || empty($domain_name)) {
            $error = __('Please fill in all required fields.', 'student-result-management');
        } else {
            // Send email to owner
            $to = 'jaffar381996152@gmail.com';
            $subject = 'New Premium License Request - Student Result Management';
            
            $message_body = "New premium license request received:\n\n";
            $message_body .= "Customer Name: $customer_name\n";
            $message_body .= "Customer Email: $customer_email\n";
            $message_body .= "Customer Phone: $customer_phone\n";
            $message_body .= "Domain Name: $domain_name\n";
            $message_body .= "Current Site URL: " . get_site_url() . "\n";
            $message_body .= "Additional Information: $additional_info\n\n";
            $message_body .= "Please generate a license key in the format: XYGh675*UGTFM.$domain_name\n";
            $message_body .= "And send it to the customer along with installation instructions.";
            
            $headers = array('Content-Type: text/plain; charset=UTF-8');
            
            if (wp_mail($to, $subject, $message_body, $headers)) {
                $message = __('License request submitted successfully! We will contact you soon with your premium license key.', 'student-result-management');
            } else {
                $error = __('Failed to submit license request. Please contact us directly.', 'student-result-management');
            }
        }
    }
}
?>

<div class="wrap srm-license-request">
    <h1 class="wp-heading-inline"><?php _e('Request Premium License', 'student-result-management'); ?></h1>
    
    <a href="<?php echo admin_url('admin.php?page=srm-enhanced-premium'); ?>" class="page-title-action">
        <?php _e('Back to Premium Features', 'student-result-management'); ?>
    </a>
    
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
    
    <div class="srm-license-request-container" style="
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 30px;
        margin: 20px 0;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    ">
        
        <div class="srm-request-info" style="margin-bottom: 30px;">
            <h2 style="color: #0073aa; margin-bottom: 15px;">📋 Premium License Request</h2>
            <p style="font-size: 16px; line-height: 1.6;">
                To get a premium license for your domain, please fill out the form below. 
                We will generate a unique domain-bound license key for your website.
            </p>
            
            <div style="background: #f0f8ff; border-left: 4px solid #0073aa; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <h4 style="margin: 0 0 10px 0; color: #0073aa;">🔒 Domain-Bound Licensing System</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>Each license key is bound to a specific domain</li>
                    <li>License keys cannot be shared between different websites</li>
                    <li>Format: <code>XYGh675*UGTFM.yourdomain.com</code></li>
                    <li>Premium features are unlocked only on the licensed domain</li>
                </ul>
            </div>
        </div>
        
        <div class="srm-current-domain-info" style="
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        ">
            <h4 style="margin: 0 0 10px 0; color: #856404;">🌐 Current Domain Information</h4>
            <p style="margin: 5px 0;"><strong>Domain:</strong> <?php echo esc_html($domain_info['domain']); ?></p>
            <p style="margin: 5px 0;"><strong>Full URL:</strong> <?php echo esc_html($domain_info['full_url']); ?></p>
            <p style="margin: 5px 0; font-size: 12px; color: #856404;">
                <em>This information will be included in your license request.</em>
            </p>
        </div>
        
        <form method="post" class="srm-license-request-form">
            <?php wp_nonce_field('srm_license_request', 'srm_license_request_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="customer_name"><?php _e('Full Name', 'student-result-management'); ?> *</label>
                    </th>
                    <td>
                        <input type="text" name="customer_name" id="customer_name" class="regular-text" 
                               value="<?php echo isset($_POST['customer_name']) ? esc_attr($_POST['customer_name']) : ''; ?>" required>
                        <p class="description"><?php _e('Your full name as it should appear on the license.', 'student-result-management'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="customer_email"><?php _e('Email Address', 'student-result-management'); ?> *</label>
                    </th>
                    <td>
                        <input type="email" name="customer_email" id="customer_email" class="regular-text" 
                               value="<?php echo isset($_POST['customer_email']) ? esc_attr($_POST['customer_email']) : ''; ?>" required>
                        <p class="description"><?php _e('We will send your license key to this email address.', 'student-result-management'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="customer_phone"><?php _e('Phone Number', 'student-result-management'); ?></label>
                    </th>
                    <td>
                        <input type="tel" name="customer_phone" id="customer_phone" class="regular-text" 
                               value="<?php echo isset($_POST['customer_phone']) ? esc_attr($_POST['customer_phone']) : ''; ?>">
                        <p class="description"><?php _e('Optional: For faster communication.', 'student-result-management'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="domain_name"><?php _e('Domain Name', 'student-result-management'); ?> *</label>
                    </th>
                    <td>
                        <input type="text" name="domain_name" id="domain_name" class="regular-text" 
                               value="<?php echo isset($_POST['domain_name']) ? esc_attr($_POST['domain_name']) : esc_attr($domain_info['domain']); ?>" required>
                        <p class="description"><?php _e('The domain where you want to activate the premium license (e.g., mysite.com).', 'student-result-management'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="additional_info"><?php _e('Additional Information', 'student-result-management'); ?></label>
                    </th>
                    <td>
                        <textarea name="additional_info" id="additional_info" rows="4" class="large-text"><?php echo isset($_POST['additional_info']) ? esc_textarea($_POST['additional_info']) : ''; ?></textarea>
                        <p class="description"><?php _e('Optional: Any additional information or special requirements.', 'student-result-management'); ?></p>
                    </td>
                </tr>
            </table>
            
            <div style="margin-top: 30px;">
                <input type="submit" name="submit_license_request" value="<?php _e('Submit License Request', 'student-result-management'); ?>" class="button button-primary button-large">
            </div>
        </form>
        
        <div class="srm-contact-info" style="
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 20px;
            margin-top: 30px;
        ">
            <h4 style="margin: 0 0 15px 0; color: #495057;">📞 Contact Information</h4>
            <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                <div>
                    <strong>WhatsApp:</strong><br>
                    <a href="https://wa.me/923083430923" target="_blank" style="color: #0073aa;">+923083430923</a>
                </div>
                <div>
                    <strong>Email:</strong><br>
                    <a href="mailto:jaffar381996152@gmail.com" style="color: #0073aa;">jaffar381996152@gmail.com</a>
                </div>
            </div>
            <p style="margin: 15px 0 0 0; font-size: 14px; color: #6c757d;">
                <em>You can also contact us directly for faster service.</em>
            </p>
        </div>
        
        <div class="srm-license-process" style="
            background: #e7f3ff;
            border-left: 4px solid #0073aa;
            padding: 20px;
            margin-top: 30px;
            border-radius: 4px;
        ">
            <h4 style="margin: 0 0 15px 0; color: #0073aa;">⚡ License Process</h4>
            <ol style="margin: 0; padding-left: 20px;">
                <li><strong>Submit Request:</strong> Fill out the form above with your details</li>
                <li><strong>Review:</strong> We review your request and domain information</li>
                <li><strong>Generate Key:</strong> We create a unique domain-bound license key</li>
                <li><strong>Send License:</strong> You receive the license key via email</li>
                <li><strong>Activate:</strong> Enter the license key in the Premium Features page</li>
                <li><strong>Enjoy:</strong> All premium features are unlocked for your domain</li>
            </ol>
        </div>
        
    </div>
</div>