<?php
/**
 * Test License Uniqueness System
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🔒 Test License Uniqueness System</h2>";

// Include necessary files
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';

global $wpdb;

echo "<h3>✅ 1. Current License Status</h3>";
$license_manager = new SRM_License_Manager();

echo "<ul>";
echo "<li><strong>License Key:</strong> " . ($license_manager->get_license_key() ?: 'None') . "</li>";
echo "<li><strong>License Status:</strong> " . $license_manager->get_license_status() . "</li>";
echo "<li><strong>Has Premium Access:</strong> " . ($license_manager->has_premium_access() ? 'Yes' : 'No') . "</li>";
echo "<li><strong>Is Plugin Owner:</strong> " . ($license_manager->is_plugin_owner() ? 'Yes' : 'No') . "</li>";
echo "</ul>";

echo "<h3>✅ 2. Current License Usage Information</h3>";

$current_usage = $license_manager->get_current_license_usage();
if (!empty($current_usage)) {
    echo "<div class='notice notice-info'>";
    echo "<h4>📋 Current License Usage:</h4>";
    echo "<ul>";
    echo "<li><strong>Site ID:</strong> " . esc_html($current_usage['site_id']) . "</li>";
    echo "<li><strong>Site URL:</strong> " . esc_html($current_usage['site_url']) . "</li>";
    echo "<li><strong>Activated At:</strong> " . esc_html($current_usage['activated_at']) . "</li>";
    echo "<li><strong>User ID:</strong> " . esc_html($current_usage['user_id']) . "</li>";
    echo "<li><strong>User Email:</strong> " . esc_html($current_usage['user_email']) . "</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div class='notice notice-warning'>";
    echo "<p><strong>ℹ️ No license currently activated</strong></p>";
    echo "</div>";
}

echo "<h3>✅ 3. License Uniqueness Test</h3>";

if (isset($_POST['test_uniqueness'])) {
    $test_key = sanitize_text_field($_POST['test_key']);
    echo "<h4>Testing License Key: <code>$test_key</code></h4>";
    
    // Check if key is valid format
    $is_valid_format = $license_manager->is_valid_license_key($test_key);
    echo "<p><strong>Format Validation:</strong> " . ($is_valid_format ? "✅ Valid" : "❌ Invalid") . "</p>";
    
    if ($is_valid_format) {
        // Check if key is already in use
        $is_in_use = $license_manager->is_license_in_use($test_key);
        echo "<p><strong>Already in Use:</strong> " . ($is_in_use ? "✅ Yes" : "❌ No") . "</p>";
        
        if ($is_in_use) {
            $usage_info = $license_manager->get_license_usage_info($test_key);
            echo "<div class='notice notice-warning'>";
            echo "<p><strong>⚠️ This license key is already in use on another website:</strong></p>";
            echo "<ul>";
            echo "<li><strong>Site URL:</strong> " . esc_html($usage_info['site_url']) . "</li>";
            echo "<li><strong>Activated:</strong> " . esc_html($usage_info['activated_at']) . "</li>";
            echo "<li><strong>User:</strong> " . esc_html($usage_info['user_email']) . "</li>";
            echo "</ul>";
            echo "<p><strong>This license cannot be activated here!</strong></p>";
            echo "</div>";
        } else {
            echo "<div class='notice notice-success'>";
            echo "<p><strong>✅ This license key is available for activation!</strong></p>";
            echo "<p>It has not been used on any other website.</p>";
            echo "</div>";
        }
    } else {
        echo "<div class='notice notice-error'>";
        echo "<p><strong>❌ Invalid license key format!</strong></p>";
        echo "<p>The key must be 13 characters with specific requirements.</p>";
        echo "</div>";
    }
} else {
    echo "<form method='post'>";
    echo "<h4>Test License Key Uniqueness:</h4>";
    echo "<p><strong>Enter a license key to test if it's already in use:</strong></p>";
    echo "<input type='text' name='test_key' placeholder='Enter 13-digit license key' style='width: 300px;' required>";
    echo "<input type='submit' name='test_uniqueness' value='Test Uniqueness' class='button button-primary'>";
    echo "</form>";
    
    echo "<h4>Test License Keys:</h4>";
    echo "<ul>";
    echo "<li><strong>Valid Available Keys:</strong> <code>B5XK!@#$%^&*F</code>, <code>J2G#ABC123@P</code>, <code>N8K$XYZ789%B</code></li>";
    echo "<li><strong>Invalid Keys:</strong> <code>ABCDEFGHIJKLM</code>, <code>B5XKABCDEFGH</code></li>";
    echo "<li><strong>Owner Key:</strong> <code>Bismillah^512</code> (always works)</li>";
    echo "</ul>";
}

echo "<h3>✅ 4. License Uniqueness System Features</h3>";

echo "<h4>How the System Works:</h4>";
echo "<ul>";
echo "<li>✅ <strong>Site Identification:</strong> Each website gets a unique identifier based on URL, name, and description</li>";
echo "<li>✅ <strong>License Binding:</strong> When activated, a license becomes bound to the current site</li>";
echo "<li>✅ <strong>Usage Tracking:</strong> System tracks which site is using which license</li>";
echo "<li>✅ <strong>Prevention:</strong> Same license cannot be activated on multiple sites</li>";
echo "<li>✅ <strong>Release:</strong> When deactivated, license becomes available for other sites</li>";
echo "</ul>";

echo "<h4>Security Features:</h4>";
echo "<ul>";
echo "<li>✅ <strong>Unique Site ID:</strong> Generated from site characteristics</li>";
echo "<li>✅ <strong>Usage Records:</strong> Stored in WordPress options</li>";
echo "<li>✅ <strong>Validation:</strong> Checks both format and usage status</li>";
echo "<li>✅ <strong>Cleanup:</strong> Removes usage records on deactivation</li>";
echo "</ul>";

echo "<h3>✅ 5. Expected Behavior</h3>";

echo "<h4>For New License Activation:</h4>";
echo "<ul>";
echo "<li>✅ <strong>Available Key:</strong> Should activate successfully</li>";
echo "<li>✅ <strong>Usage Record:</strong> Should create usage record</li>";
echo "<li>✅ <strong>Binding:</strong> Should bind to current site</li>";
echo "<li>✅ <strong>Success Message:</strong> Should show 'bound to this installation'</li>";
echo "</ul>";

echo "<h4>For Already Used License:</h4>";
echo "<ul>";
echo "<li>❌ <strong>Rejection:</strong> Should reject activation</li>";
echo "<li>❌ <strong>Error Message:</strong> Should show 'already activated on another website'</li>";
echo "<li>❌ <strong>No Binding:</strong> Should not create usage record</li>";
echo "<li>❌ <strong>No Access:</strong> Should not grant premium access</li>";
echo "</ul>";

echo "<h4>For License Deactivation:</h4>";
echo "<ul>";
echo "<li>✅ <strong>Usage Removal:</strong> Should remove usage record</li>";
echo "<li>✅ <strong>Availability:</strong> Should make license available for other sites</li>";
echo "<li>✅ <strong>Success Message:</strong> Should show 'available for use on another installation'</li>";
echo "</ul>";

echo "<h3>✅ 6. Database Storage</h3>";

// Check for license usage records in database
$license_usage_options = $wpdb->get_results("
    SELECT option_name, option_value 
    FROM {$wpdb->options} 
    WHERE option_name LIKE 'srm_license_usage_%'
    ORDER BY option_name
");

if ($license_usage_options) {
    echo "<div class='notice notice-info'>";
    echo "<h4>📊 License Usage Records in Database:</h4>";
    echo "<table class='widefat'>";
    echo "<thead><tr><th>License Key</th><th>Usage Data</th></tr></thead>";
    echo "<tbody>";
    foreach ($license_usage_options as $option) {
        $license_key = str_replace('srm_license_usage_', '', $option->option_name);
        $usage_data = maybe_unserialize($option->option_value);
        echo "<tr>";
        echo "<td><code>" . esc_html($license_key) . "</code></td>";
        echo "<td><pre>" . esc_html(print_r($usage_data, true)) . "</pre></td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    echo "</div>";
} else {
    echo "<div class='notice notice-warning'>";
    echo "<p><strong>ℹ️ No license usage records found in database</strong></p>";
    echo "<p>This means no licenses have been activated yet.</p>";
    echo "</div>";
}

echo "<h3>✅ 7. Manual Test Steps</h3>";
echo "<ol>";
echo "<li><strong>Activate License:</strong> Go to Premium Features page and activate a license</li>";
echo "<li><strong>Check Usage:</strong> Verify usage information is displayed</li>";
echo "<li><strong>Try Duplicate:</strong> Try to activate the same license on another site</li>";
echo "<li><strong>Verify Rejection:</strong> Should see 'already activated' error</li>";
echo "<li><strong>Deactivate License:</strong> Deactivate the license</li>";
echo "<li><strong>Check Availability:</strong> License should become available again</li>";
echo "</ol>";

echo "<h3>🔗 Quick Test Links</h3>";
echo "<ul>";
echo "<li><a href='?page=srm-premium'>Premium Features Page</a></li>";
echo "<li><a href='?page=srm-results'>Results Page</a></li>";
echo "<li><a href='?page=srm-students'>Students Page</a></li>";
echo "</ul>";

echo "<h3>📱 Contact Information</h3>";
echo "<p><strong>WhatsApp:</strong> <a href='https://wa.me/923083430923' target='_blank'>+923083430923</a></p>";
echo "<p><strong>Email:</strong> <a href='mailto:jaffar381996152@gmail.com'>jaffar381996152@gmail.com</a></p>";

echo "<h3>🎯 Summary</h3>";
echo "<p><strong>The license uniqueness system has been implemented:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>One License Per Site:</strong> Each license can only be used on one website</li>";
echo "<li>✅ <strong>Usage Tracking:</strong> System tracks which site uses which license</li>";
echo "<li>✅ <strong>Prevention:</strong> Blocks duplicate license usage</li>";
echo "<li>✅ <strong>Release:</strong> Makes licenses available when deactivated</li>";
echo "<li>✅ <strong>Security:</strong> Prevents unauthorized license sharing</li>";
echo "<li>✅ <strong>Fair Usage:</strong> Ensures each buyer needs their own license</li>";
echo "</ul>";

echo "<h3>🧪 To Test License Uniqueness</h3>";
echo "<ol>";
echo "<li><strong>Activate License:</strong> Activate a license on this site</li>";
echo "<li><strong>Check Usage Info:</strong> Verify usage information is shown</li>";
echo "<li><strong>Try Another Site:</strong> Try to use same license on different site</li>";
echo "<li><strong>Verify Blocking:</strong> Should be rejected with error message</li>";
echo "<li><strong>Deactivate:</strong> Deactivate license to make it available again</li>";
echo "<li><strong>Test Reuse:</strong> License should work on other sites after deactivation</li>";
echo "</ol>";

echo "<h3>🔧 Files Modified</h3>";
echo "<ul>";
echo "<li>✅ <strong>license-manager.php:</strong> Added uniqueness checking and usage tracking</li>";
echo "<li>✅ <strong>enhanced-premium.php:</strong> Added usage information display</li>";
echo "<li>✅ <strong>Database:</strong> Uses WordPress options to store usage data</li>";
echo "</ul>";
?>