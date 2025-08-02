<?php
if (!defined('ABSPATH')) exit;

// Include license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';

$license_manager = new SRM_License_Manager();

// Check if user is plugin owner
if (!$license_manager->is_plugin_owner()) {
    wp_die(__('Access denied. Only plugin owner can manage license keys.', 'student-result-management'));
}

$valid_keys = $license_manager->get_valid_license_keys();
?>

<div class="wrap srm-license-key-manager">
    <h1><?php _e('License Key Management', 'student-result-management'); ?></h1>
    
    <div class="notice notice-info">
        <p><strong><?php _e('Plugin Owner Access:', 'student-result-management'); ?></strong> <?php _e('You can manage valid license keys that users can use to activate premium features.', 'student-result-management'); ?></p>
    </div>
    
    <!-- Add New License Key -->
    <div class="srm-add-key-section">
        <h3><?php _e('Add New License Key', 'student-result-management'); ?></h3>
        <form method="post" id="srm-add-key-form">
            <?php wp_nonce_field('srm_license_nonce', 'srm_license_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="new_license_key"><?php _e('License Key', 'student-result-management'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="new_license_key" name="license_key" class="regular-text" required>
                        <p class="description"><?php _e('Enter a valid license key to add to the system. This key will be available for users to activate premium features.', 'student-result-management'); ?></p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="button" class="button button-primary" id="srm-add-key-btn">
                    <?php _e('Add License Key', 'student-result-management'); ?>
                </button>
            </p>
        </form>
    </div>
    
    <!-- Valid License Keys List -->
    <div class="srm-valid-keys-section">
        <h3><?php _e('Valid License Keys', 'student-result-management'); ?></h3>
        
        <div id="srm-keys-loading" style="display: none;">
            <p><?php _e('Loading license keys...', 'student-result-management'); ?></p>
        </div>
        
        <div id="srm-keys-list">
            <!-- Keys will be loaded here via AJAX -->
        </div>
    </div>
    
    <!-- License Key Guidelines -->
    <div class="srm-key-guidelines">
        <h3><?php _e('License Key Guidelines', 'student-result-management'); ?></h3>
        <div class="srm-guidelines-content">
            <h4><?php _e('Key Format Requirements:', 'student-result-management'); ?></h4>
            <ul>
                <li><?php _e('Length: 8-32 characters', 'student-result-management'); ?></li>
                <li><?php _e('Must contain at least one letter and one number', 'student-result-management'); ?></li>
                <li><?php _e('Can include special characters', 'student-result-management'); ?></li>
                <li><?php _e('Should be unique and secure', 'student-result-management'); ?></li>
            </ul>
            
            <h4><?php _e('Example Valid Keys:', 'student-result-management'); ?></h4>
            <ul>
                <li><code>Premium2024!</code></li>
                <li><code>StudentPro123</code></li>
                <li><code>ResultManager2024</code></li>
                <li><code>EduPlugin2024#</code></li>
            </ul>
            
            <h4><?php _e('Usage Tracking:', 'student-result-management'); ?></h4>
            <p><?php _e('The system tracks which license keys are currently in use. You can see usage information for each key below.', 'student-result-management'); ?></p>
        </div>
    </div>
</div>

<style>
.srm-license-key-manager {
    max-width: 1200px;
}

.srm-add-key-section,
.srm-valid-keys-section,
.srm-key-guidelines {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.srm-add-key-section h3,
.srm-valid-keys-section h3,
.srm-key-guidelines h3 {
    margin-top: 0;
    color: #23282d;
}

.srm-keys-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.srm-keys-table th,
.srm-keys-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.srm-keys-table th {
    background: #f9f9f9;
    font-weight: bold;
}

.srm-key-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.srm-key-status-unused {
    background: #d4edda;
    color: #155724;
}

.srm-key-status-in-use {
    background: #fff3cd;
    color: #856404;
}

.srm-key-actions {
    display: flex;
    gap: 10px;
}

.srm-key-actions button {
    padding: 4px 8px;
    font-size: 12px;
}

.srm-guidelines-content h4 {
    margin: 20px 0 10px 0;
    color: #23282d;
}

.srm-guidelines-content ul {
    margin: 10px 0;
    padding-left: 20px;
}

.srm-guidelines-content li {
    margin-bottom: 5px;
}

.srm-guidelines-content code {
    background: #f1f1f1;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: monospace;
}

.srm-no-keys {
    text-align: center;
    padding: 40px;
    color: #666;
}

.srm-usage-info {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Load valid keys on page load
    loadValidKeys();
    
    // Add new license key
    $('#srm-add-key-btn').on('click', function() {
        var licenseKey = $('#new_license_key').val();
        
        if (!licenseKey) {
            alert('Please enter a license key');
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'srm_add_valid_key',
                license_key: licenseKey,
                nonce: $('#srm_license_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('License key added successfully!');
                    $('#new_license_key').val('');
                    loadValidKeys();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // Load valid license keys
    function loadValidKeys() {
        $('#srm-keys-loading').show();
        $('#srm-keys-list').hide();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'srm_get_valid_keys',
                nonce: $('#srm_license_nonce').val()
            },
            success: function(response) {
                $('#srm-keys-loading').hide();
                
                if (response.success) {
                    displayValidKeys(response.data);
                } else {
                    $('#srm-keys-list').html('<div class="srm-no-keys">Error loading keys: ' + response.data + '</div>').show();
                }
            },
            error: function() {
                $('#srm-keys-loading').hide();
                $('#srm-keys-list').html('<div class="srm-no-keys">Error loading keys. Please try again.</div>').show();
            }
        });
    }
    
    // Display valid keys
    function displayValidKeys(keys) {
        if (keys.length === 0) {
            $('#srm-keys-list').html('<div class="srm-no-keys">No valid license keys found. Add some keys above.</div>').show();
            return;
        }
        
        var html = '<table class="srm-keys-table">';
        html += '<thead><tr>';
        html += '<th>License Key</th>';
        html += '<th>Status</th>';
        html += '<th>Usage Count</th>';
        html += '<th>Actions</th>';
        html += '</tr></thead>';
        html += '<tbody>';
        
        keys.forEach(function(keyData) {
            var statusClass = keyData.in_use ? 'srm-key-status-in-use' : 'srm-key-status-unused';
            var statusText = keyData.in_use ? 'In Use' : 'Unused';
            
            html += '<tr>';
            html += '<td><code>' + keyData.key + '</code></td>';
            html += '<td><span class="srm-key-status ' + statusClass + '">' + statusText + '</span></td>';
            html += '<td>' + keyData.usage_count + '</td>';
            html += '<td class="srm-key-actions">';
            html += '<button type="button" class="button button-small button-secondary srm-remove-key" data-key="' + keyData.key + '">Remove</button>';
            html += '</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table>';
        $('#srm-keys-list').html(html).show();
    }
    
    // Remove license key
    $(document).on('click', '.srm-remove-key', function() {
        var key = $(this).data('key');
        
        if (confirm('Are you sure you want to remove this license key? Users with this key will lose premium access.')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'srm_remove_valid_key',
                    license_key: key,
                    nonce: $('#srm_license_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('License key removed successfully!');
                        loadValidKeys();
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
});
</script>