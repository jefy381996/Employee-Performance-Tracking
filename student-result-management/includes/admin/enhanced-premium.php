<?php
if (!defined('ABSPATH')) exit;

// Include license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';

$license_manager = new SRM_License_Manager();

$current_user_id = get_current_user_id();
$plugin_owner = get_option('srm_plugin_owner');
$is_owner = ($current_user_id == $plugin_owner);
$has_premium = $license_manager->has_premium_access();
$license_status = $license_manager->get_license_status();
$license_key = $license_manager->get_license_key();

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
    
    <?php 
    // Show notice if license is required
    if (isset($_GET['license_required'])) {
        echo '<div class="notice notice-error">';
        echo '<p><strong>' . __('License Required!', 'student-result-management') . '</strong> ' . __('You must activate a license key to access plugin features.', 'student-result-management') . '</p>';
        echo '</div>';
    }
    ?>
    
    <!-- License Status Section -->
    <?php if ($has_premium): ?>
        <?php if ($is_owner): ?>
            <!-- Owner Features -->
            <div class="srm-owner-features">
                <div class="srm-owner-info">
                    <h3><?php _e('Owner Access', 'student-result-management'); ?></h3>
                    <ul>
                        <li><?php _e('Full access to all premium features', 'student-result-management'); ?></li>
                        <li><?php _e('Access to all admin functions', 'student-result-management'); ?></li>
                        <li><?php _e('Can deactivate and reactivate licenses', 'student-result-management'); ?></li>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <!-- Premium User Features -->
            <div class="srm-premium-features">
                <div class="notice notice-success">
                    <h3><?php _e('✅ Premium License Active', 'student-result-management'); ?></h3>
                    <p><?php _e('Your premium license is active! You have access to all premium features.', 'student-result-management'); ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="post" id="srm-deactivate-form">
            <?php wp_nonce_field('srm_license_nonce', 'srm_license_nonce'); ?>
            <p class="submit">
                <button type="button" class="button button-secondary" id="srm-deactivate-license">
                    <?php _e('Deactivate License', 'student-result-management'); ?>
                </button>
            </p>
        </form>
    <?php else: ?>
        <!-- No License Active -->
        <div class="srm-no-license">
            <div class="notice notice-warning">
                <h3><?php _e('No License Active', 'student-result-management'); ?></h3>
                <p><?php _e('You currently have access to free features only. Activate a license to access premium features.', 'student-result-management'); ?></p>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- License Activation Section (Always Visible) -->
    <div class="srm-license-activation">
        <h3><?php _e('License Management', 'student-result-management'); ?></h3>
        
        <?php if (!$has_premium): ?>
            <div class="notice notice-info">
                <p><?php _e('To access premium features, you need to activate a valid license key.', 'student-result-management'); ?></p>
                <p><strong><?php _e('Plugin Owner Key:', 'student-result-management'); ?></strong> <code>Bismillah^512</code></p>
                <p><strong><?php _e('Premium User Keys:', 'student-result-management'); ?></strong> <?php _e('13-digit license keys provided by the plugin owner.', 'student-result-management'); ?></p>
                <p><strong><?php _e('License Key Format:', 'student-result-management'); ?></strong> <?php _e('13 characters with specific character requirements at positions 1, 4, 8-10, and 13.', 'student-result-management'); ?></p>
            </div>
        <?php endif; ?>
        
        <form method="post" id="srm-activate-form">
            <?php wp_nonce_field('srm_license_nonce', 'srm_license_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="license_key"><?php _e('License Key', 'student-result-management'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="license_key" name="license_key" value="" class="regular-text" required>
                        <p class="description"><?php _e('Enter your license key to activate premium features', 'student-result-management'); ?></p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="button" class="button button-primary" id="srm-activate-license">
                    <?php _e('Activate License', 'student-result-management'); ?>
                </button>
                <button type="button" class="button button-secondary" id="srm-check-license">
                    <?php _e('Check License Status', 'student-result-management'); ?>
                </button>
            </p>
        </form>
    </div>
    
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
                    <td><span class="dashicons dashicons-yes"></span> <small>(20 students max)</small></td>
                    <td><span class="dashicons dashicons-yes"></span> <small>(Unlimited)</small></td>
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
                    <td><?php _e('CSV Import/Export', 'student-result-management'); ?></td>
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
                    <td><?php _e('Student Profile Images', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                    <td><?php _e('PDF Certificate Upload', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                    <td><?php _e('Certificate Download', 'student-result-management'); ?></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- How to Get License -->
    <div class="srm-license-info">
        <h3><?php _e('How to Get a License Key', 'student-result-management'); ?></h3>
        <div class="srm-license-steps">
            <ol>
                <li><?php _e('Contact the plugin owner to request a license key', 'student-result-management'); ?></li>
                <li><?php _e('Provide your website URL and intended use', 'student-result-management'); ?></li>
                <li><?php _e('Receive your unique license key', 'student-result-management'); ?></li>
                <li><?php _e('Enter the license key above to activate premium features', 'student-result-management'); ?></li>
            </ol>
        </div>
        
        <div class="srm-license-note">
            <h4><?php _e('Important Notes:', 'student-result-management'); ?></h4>
            <ul>
                <li><?php _e('License keys are unique and tied to your website', 'student-result-management'); ?></li>
                <li><?php _e('Do not share your license key with others', 'student-result-management'); ?></li>
                <li><?php _e('Contact the plugin owner for support or license issues', 'student-result-management'); ?></li>
            </ul>
        </div>
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
.srm-status-owner { background: #cce5ff; color: #004085; }
.srm-status-invalid { background: #f8d7da; color: #721c24; }

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

.srm-owner-info {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
}

.srm-owner-info ul {
    margin: 15px 0 0 0;
    padding-left: 20px;
}

.srm-owner-info li {
    margin-bottom: 8px;
}

.srm-license-activation {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
}

.srm-feature-comparison {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
}

.srm-license-info {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
}

.srm-license-steps ol {
    margin: 15px 0;
    padding-left: 20px;
}

.srm-license-steps li {
    margin-bottom: 10px;
}

.srm-license-note {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 15px;
    margin-top: 20px;
}

.srm-license-note ul {
    margin: 10px 0 0 0;
    padding-left: 20px;
}

.srm-license-note li {
    margin-bottom: 5px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // License activation
    $('#srm-activate-license').on('click', function() {
        var licenseKey = $('#license_key').val();
        
        if (!licenseKey) {
            alert('Please enter a license key');
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
        if (confirm('Are you sure you want to deactivate your license? This will remove access to premium features but you will still have access to free features.')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'srm_deactivate_license',
                    nonce: $('#srm_license_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('License deactivated successfully! You now have access to free features only.');
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
});
</script>