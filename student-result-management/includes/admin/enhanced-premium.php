<?php
if (!defined('ABSPATH')) exit;

// Include license manager and payment processor
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
require_once SRM_PLUGIN_PATH . 'includes/admin/payment-processor.php';

$license_manager = new SRM_License_Manager();
$payment_processor = new SRM_Payment_Processor();

$current_user_id = get_current_user_id();
$plugin_owner = get_option('srm_plugin_owner');
$is_owner = ($current_user_id == $plugin_owner);
$has_premium = $license_manager->has_premium_access();
$license_status = $license_manager->get_license_status();
$license_key = $license_manager->get_license_key();
$payment_methods = $payment_processor->get_available_payment_methods();
$payment_history = $payment_processor->get_payment_history(5);


?>

<div class="wrap srm-enhanced-premium">
    <h1><?php _e('Premium Features & License Management', 'student-result-management'); ?></h1>
    
    <!-- License Status Overview -->
    <div class="srm-license-overview">
        <div class="srm-license-card">
            <h3><?php _e('License Status', 'student-result-management'); ?></h3>
            <div class="srm-license-info">
                <p><strong><?php _e('Status:', 'student-result-management'); ?></strong> 
                    <span class="srm-status srm-status-<?php echo $license_status; ?>">
                        <?php echo ucfirst($license_status); ?>
                    </span>
                </p>
                <?php if ($is_owner): ?>
                    <p><strong><?php _e('Role:', 'student-result-management'); ?></strong> 
                        <span class="srm-role srm-role-owner"><?php _e('Plugin Owner', 'student-result-management'); ?></span>
                    </p>
                <?php endif; ?>
                <?php if (!empty($license_key)): ?>
                    <p><strong><?php _e('License Key:', 'student-result-management'); ?></strong> 
                        <code><?php echo esc_html($license_key); ?></code>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if ($is_owner): ?>
        <!-- Owner Features -->
        <div class="srm-owner-features">
            <div class="notice notice-success">
                <h3><?php _e('🎉 Congratulations! You are the Plugin Owner', 'student-result-management'); ?></h3>
                <p><?php _e('As the plugin owner, you have full access to all premium features without any restrictions. You can also manage licenses for other users.', 'student-result-management'); ?></p>
            </div>
            
            <!-- License Management for Owner -->
            <div class="srm-license-management">
                <h3><?php _e('License Management', 'student-result-management'); ?></h3>
                <div class="srm-license-actions">
                    <button class="button button-primary" id="srm-generate-license">
                        <?php _e('Generate New License Key', 'student-result-management'); ?>
                    </button>
                    <button class="button button-secondary" id="srm-check-licenses">
                        <?php _e('Check All Licenses', 'student-result-management'); ?>
                    </button>
                </div>
            </div>
            

            
            <!-- Payment History -->
            <div class="srm-payment-history">
                <h3><?php _e('Recent Payments', 'student-result-management'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Transaction ID', 'student-result-management'); ?></th>
                            <th><?php _e('Amount', 'student-result-management'); ?></th>
                            <th><?php _e('Method', 'student-result-management'); ?></th>
                            <th><?php _e('Customer', 'student-result-management'); ?></th>
                            <th><?php _e('Status', 'student-result-management'); ?></th>
                            <th><?php _e('Date', 'student-result-management'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payment_history)): ?>
                            <?php foreach ($payment_history as $payment): ?>
                                <tr>
                                    <td><?php echo esc_html($payment->transaction_id); ?></td>
                                    <td><?php echo esc_html($payment->amount . ' ' . $payment->currency); ?></td>
                                    <td><?php echo esc_html(ucfirst($payment->payment_method)); ?></td>
                                    <td><?php echo esc_html($payment->customer_name); ?></td>
                                    <td>
                                        <span class="srm-status srm-status-<?php echo $payment->status; ?>">
                                            <?php echo esc_html(ucfirst($payment->status)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html(date('M j, Y', strtotime($payment->created_at))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6"><?php _e('No payment history found.', 'student-result-management'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Non-Owner Features -->
        <?php if ($has_premium): ?>
            <div class="notice notice-success">
                <h3><?php _e('✅ Premium Access Active', 'student-result-management'); ?></h3>
                <p><?php _e('You have premium access to all features. Your license is valid and active.', 'student-result-management'); ?></p>
            </div>
        <?php else: ?>
            <div class="notice notice-warning">
                <h3><?php _e('🔒 Premium Features Locked', 'student-result-management'); ?></h3>
                <p><?php _e('You currently have access to basic features only. Upgrade to premium to unlock all advanced features.', 'student-result-management'); ?></p>
            </div>
            
            <!-- Payment Options -->
            <div class="srm-payment-options">
                <h3><?php _e('Upgrade to Premium', 'student-result-management'); ?></h3>
                <div class="srm-pricing-cards">
                    <div class="srm-pricing-card">
                        <div class="srm-price-header">
                            <h4><?php _e('Premium License', 'student-result-management'); ?></h4>
                            <div class="srm-price">
                                <span class="srm-currency">$</span>
                                <span class="srm-amount">49</span>
                                <span class="srm-period">/year</span>
                            </div>
                            <p class="srm-price-description"><?php _e('One-time payment for lifetime access', 'student-result-management'); ?></p>
                        </div>
                        <div class="srm-features-list">
                            <ul>
                                <li><span class="dashicons dashicons-yes"></span> <?php _e('PDF Result Cards', 'student-result-management'); ?></li>
                                <li><span class="dashicons dashicons-yes"></span> <?php _e('CSV Import/Export', 'student-result-management'); ?></li>
                                <li><span class="dashicons dashicons-yes"></span> <?php _e('Student Profile Images', 'student-result-management'); ?></li>
                                <li><span class="dashicons dashicons-yes"></span> <?php _e('Advanced Analytics', 'student-result-management'); ?></li>
                                <li><span class="dashicons dashicons-yes"></span> <?php _e('Email Notifications', 'student-result-management'); ?></li>
                                <li><span class="dashicons dashicons-yes"></span> <?php _e('Data Backup & Restore', 'student-result-management'); ?></li>
                                <li><span class="dashicons dashicons-yes"></span> <?php _e('Custom Templates', 'student-result-management'); ?></li>
                                <li><span class="dashicons dashicons-yes"></span> <?php _e('Priority Support', 'student-result-management'); ?></li>
                            </ul>
                        </div>
                        <button class="button button-primary button-hero srm-upgrade-btn" data-plan="premium">
                            <?php _e('Upgrade Now', 'student-result-management'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- Payment Methods -->
                <div class="srm-payment-methods">
                    <h4><?php _e('Payment Methods', 'student-result-management'); ?></h4>
                    <div class="srm-payment-methods-grid">
                        <?php foreach ($payment_methods as $method => $details): ?>
                            <div class="srm-payment-method">
                                <span class="dashicons <?php echo esc_attr($details['icon']); ?>"></span>
                                <h5><?php echo esc_html($details['name']); ?></h5>
                                <p><?php echo esc_html($details['description']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- License Activation -->
        <div class="srm-license-activation">
            <h3><?php _e('License Activation', 'student-result-management'); ?></h3>
            <p><?php _e('If you already have a license key, enter it below to activate premium features.', 'student-result-management'); ?></p>
            
            <form id="srm-license-form">
                <?php wp_nonce_field('srm_license_nonce', 'srm_license_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="license_key"><?php _e('License Key', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="license_key" name="license_key" class="regular-text" 
                                   value="<?php echo esc_attr($license_key); ?>" 
                                   placeholder="<?php _e('Enter your license key', 'student-result-management'); ?>">
                            <p class="description"><?php _e('Enter your license key to activate premium features.', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <div class="srm-license-actions">
                    <button type="submit" class="button button-primary" id="srm-activate-license">
                        <?php _e('Activate License', 'student-result-management'); ?>
                    </button>
                    <?php if (!empty($license_key)): ?>
                        <button type="button" class="button button-secondary" id="srm-deactivate-license">
                            <?php _e('Deactivate License', 'student-result-management'); ?>
                        </button>
                    <?php endif; ?>
                    <button type="button" class="button button-secondary" id="srm-check-license">
                        <?php _e('Check License Status', 'student-result-management'); ?>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
    
    <!-- Feature Comparison -->
    <div class="srm-feature-comparison">
        <h3><?php _e('Feature Comparison', 'student-result-management'); ?></h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Feature', 'student-result-management'); ?></th>
                    <th><?php _e('Free', 'student-result-management'); ?></th>
                    <th><?php _e('Premium', 'student-result-management'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php _e('Student Management', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                    <td><?php _e('Result Management', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                    <td><?php _e('Result Lookup', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                    <td><?php _e('Basic Export', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                    <td><?php _e('Dashboard Analytics', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                    <td><?php _e('PDF Result Cards', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                    <td><?php _e('CSV Import/Export', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                    <td><?php _e('Student Profile Images', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                    <td><?php _e('Advanced Analytics', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                    <td><?php _e('Email Notifications', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                    <td><?php _e('Data Backup & Restore', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                    <td><?php _e('Custom Templates', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                    <td><?php _e('Priority Support', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Payment Modal -->
<div id="srm-payment-modal" class="srm-modal" style="display: none;">
    <div class="srm-modal-content">
        <span class="srm-modal-close">&times;</span>
        <h3><?php _e('Complete Your Purchase', 'student-result-management'); ?></h3>
        
        <form id="srm-payment-form">
            <?php wp_nonce_field('srm_payment_nonce', 'srm_payment_nonce'); ?>
            
            <div class="srm-payment-details">
                <h4><?php _e('Order Summary', 'student-result-management'); ?></h4>
                <div class="srm-order-summary">
                    <p><strong><?php _e('Product:', 'student-result-management'); ?></strong> Premium License</p>
                    <p><strong><?php _e('Amount:', 'student-result-management'); ?></strong> $49.00 USD</p>
                    <p><strong><?php _e('Duration:', 'student-result-management'); ?></strong> Lifetime</p>
                </div>
            </div>
            
            <div class="srm-customer-info">
                <h4><?php _e('Customer Information', 'student-result-management'); ?></h4>
                <div class="srm-form-row">
                    <div class="srm-form-group">
                        <label for="customer_name"><?php _e('Full Name', 'student-result-management'); ?></label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>
                    <div class="srm-form-group">
                        <label for="customer_email"><?php _e('Email Address', 'student-result-management'); ?></label>
                        <input type="email" id="customer_email" name="customer_email" required>
                    </div>
                </div>
            </div>
            
            <div class="srm-payment-method-selection">
                <h4><?php _e('Payment Method', 'student-result-management'); ?></h4>
                <div class="srm-payment-methods">
                    <?php foreach ($payment_methods as $method => $details): ?>
                        <div class="srm-payment-method-option">
                            <input type="radio" id="payment_<?php echo $method; ?>" name="payment_method" value="<?php echo $method; ?>" required>
                            <label for="payment_<?php echo $method; ?>">
                                <span class="dashicons <?php echo esc_attr($details['icon']); ?>"></span>
                                <?php echo esc_html($details['name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="srm-payment-actions">
                <button type="submit" class="button button-primary button-hero">
                    <?php _e('Complete Purchase', 'student-result-management'); ?>
                </button>
                <button type="button" class="button button-secondary srm-modal-cancel">
                    <?php _e('Cancel', 'student-result-management'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.srm-enhanced-premium {
    max-width: 1200px;
}

.srm-license-overview {
    margin-bottom: 30px;
}

.srm-license-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
.srm-status-active { background: #d4edda; color: #155724; }
.srm-status-pending { background: #fff3cd; color: #856404; }

.srm-role-owner {
    background: #cce5ff;
    color: #004085;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: bold;
}

.srm-owner-features {
    margin-bottom: 30px;
}

.srm-license-management,
.srm-payment-history {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.srm-pricing-cards {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.srm-pricing-card {
    background: #fff;
    border: 2px solid #0073aa;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    flex: 1;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.srm-price {
    font-size: 48px;
    font-weight: bold;
    color: #0073aa;
    margin: 20px 0;
}

.srm-currency {
    font-size: 24px;
    vertical-align: top;
}

.srm-period {
    font-size: 16px;
    color: #666;
}

.srm-features-list ul {
    list-style: none;
    padding: 0;
    margin: 20px 0;
}

.srm-features-list li {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.srm-features-list .dashicons {
    color: #28a745;
    margin-right: 10px;
}

.srm-payment-methods-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.srm-payment-method {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.srm-payment-method .dashicons {
    font-size: 32px;
    color: #0073aa;
    margin-bottom: 10px;
}

.srm-modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.srm-modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 30px;
    border-radius: 8px;
    width: 80%;
    max-width: 600px;
    position: relative;
}

.srm-modal-close {
    position: absolute;
    right: 20px;
    top: 20px;
    font-size: 28px;
    cursor: pointer;
}

.srm-form-row {
    display: flex;
    gap: 20px;
}

.srm-form-group {
    flex: 1;
}

.srm-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.srm-form-group input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.srm-payment-methods {
    margin: 20px 0;
}

.srm-payment-method-option {
    margin: 10px 0;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
}

.srm-payment-method-option:hover {
    background: #f9f9f9;
}

.srm-payment-method-option input[type="radio"] {
    margin-right: 10px;
}

.srm-payment-actions {
    margin-top: 30px;
    text-align: center;
}

.srm-payment-actions .button {
    margin: 0 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // License activation
    $('#srm-license-form').on('submit', function(e) {
        e.preventDefault();
        
        var licenseKey = $('#license_key').val();
        if (!licenseKey) {
            alert('Please enter a license key.');
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'srm_activate_license',
                license_key: licenseKey,
                nonce: $('#srm_license_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('License activated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // License deactivation
    $('#srm-deactivate-license').on('click', function() {
        if (confirm('Are you sure you want to deactivate your license?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'srm_deactivate_license',
                    nonce: $('#srm_license_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('License deactivated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    });
    
    // License status check
    $('#srm-check-license').on('click', function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'srm_check_license_status',
                nonce: $('#srm_license_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('License status: ' + response.data.status);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // Payment modal
    $('.srm-upgrade-btn').on('click', function() {
        $('#srm-payment-modal').show();
    });
    
    $('.srm-modal-close, .srm-modal-cancel').on('click', function() {
        $('#srm-payment-modal').hide();
    });
    
    // Payment form submission
    $('#srm-payment-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&action=srm_process_payment&amount=49&currency=USD';
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('Payment processed successfully! Your license has been activated.');
                    $('#srm-payment-modal').hide();
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // Generate license key (owner only)
    $('#srm-generate-license').on('click', function() {
        if (confirm('Generate a new license key?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'srm_generate_license',
                    nonce: $('#srm_license_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('New license key generated: ' + response.data.license_key);
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    });
    
    // Check all licenses (owner only)
    $('#srm-check-licenses').on('click', function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'srm_check_all_licenses',
                nonce: $('#srm_license_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('License check completed. Status: ' + response.data.status);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
});
</script>