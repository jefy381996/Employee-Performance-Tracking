<?php
/**
 * Test License Activation and Premium Access
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🧪 Test License Activation and Premium Access</h2>";

// Include license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
$license_manager = new SRM_License_Manager();

echo "<h3>✅ 1. Current License Status</h3>";
echo "<ul>";
echo "<li><strong>License Key:</strong> " . ($license_manager->get_license_key() ?: 'None') . "</li>";
echo "<li><strong>License Status:</strong> " . $license_manager->get_license_status() . "</li>";
echo "<li><strong>Is Plugin Owner:</strong> " . ($license_manager->is_plugin_owner() ? 'Yes' : 'No') . "</li>";
echo "<li><strong>Has Premium Access:</strong> " . ($license_manager->has_premium_access() ? 'Yes' : 'No') . "</li>";
echo "<li><strong>Has Any License:</strong> " . ($license_manager->has_any_license() ? 'Yes' : 'No') . "</li>";
echo "</ul>";

echo "<h3>✅ 2. License Key Validation Test</h3>";

// Test various license keys
$test_keys = array(
    'Bismillah^512' => 'Owner Key',
    'B5XK!@#$%^&*F' => 'Valid Premium Key',
    'J2G#ABC123@P' => 'Valid Premium Key',
    'N8K$XYZ789%B' => 'Valid Premium Key',
    'ABCDEFGHIJKLM' => 'Invalid Key (wrong format)',
    'B5XKABCDEFGH' => 'Invalid Key (no special char)',
    'Z5XK!@#$%^&*F' => 'Invalid Key (wrong first letter)',
    'B5XK!@#$%^&*Z' => 'Invalid Key (wrong last letter)'
);

echo "<table class='widefat' style='margin: 20px 0;'>";
echo "<thead><tr><th>License Key</th><th>Type</th><th>Valid</th><th>Would Grant Access</th></tr></thead>";
echo "<tbody>";

foreach ($test_keys as $key => $type) {
    $is_valid = $license_manager->is_valid_license_key($key);
    $would_grant_access = $is_valid && !empty($key);
    
    echo "<tr>";
    echo "<td><code>$key</code></td>";
    echo "<td>$type</td>";
    echo "<td>" . ($is_valid ? "✅ Valid" : "❌ Invalid") . "</td>";
    echo "<td>" . ($would_grant_access ? "✅ Yes" : "❌ No") . "</td>";
    echo "</tr>";
}

echo "</tbody></table>";

echo "<h3>✅ 3. Manual License Activation Test</h3>";

if (isset($_POST['test_activate'])) {
    $test_key = sanitize_text_field($_POST['test_key']);
    echo "<h4>Testing License Key: <code>$test_key</code></h4>";
    
    // Test if key is valid
    $is_valid = $license_manager->is_valid_license_key($test_key);
    echo "<p><strong>Key Validation:</strong> " . ($is_valid ? "✅ Valid" : "❌ Invalid") . "</p>";
    
    if ($is_valid) {
        // Activate the license
        $result = $license_manager->activate_license($test_key);
        echo "<p><strong>Activation Result:</strong> " . ($result['success'] ? "✅ Success" : "❌ Failed") . "</p>";
        echo "<p><strong>Message:</strong> " . $result['message'] . "</p>";
        
        if ($result['success']) {
            echo "<div class='notice notice-success'>";
            echo "<p><strong>✅ License activated successfully!</strong></p>";
            echo "<p>You should now have premium access. Refresh the page to see changes.</p>";
            echo "</div>";
        }
    } else {
        echo "<div class='notice notice-error'>";
        echo "<p><strong>❌ Invalid license key!</strong></p>";
        echo "<p>The key does not meet the required format.</p>";
        echo "</div>";
    }
    
} else {
    echo "<form method='post'>";
    echo "<p><strong>Enter a license key to test activation:</strong></p>";
    echo "<input type='text' name='test_key' placeholder='Enter license key' style='width: 300px;' required>";
    echo "<input type='submit' name='test_activate' value='Test Activation' class='button button-primary'>";
    echo "</form>";
    
    echo "<h4>Test License Keys:</h4>";
    echo "<ul>";
    echo "<li><strong>Owner Key:</strong> <code>Bismillah^512</code> (grants owner access)</li>";
    echo "<li><strong>Valid Premium Keys:</strong> <code>B5XK!@#$%^&*F</code>, <code>J2G#ABC123@P</code>, <code>N8K$XYZ789%B</code></li>";
    echo "<li><strong>Invalid Keys:</strong> <code>ABCDEFGHIJKLM</code>, <code>B5XKABCDEFGH</code></li>";
    echo "</ul>";
}

echo "<h3>✅ 4. Premium Access Verification</h3>";
echo "<ul>";
echo "<li><strong>Profile Image Upload:</strong> " . ($license_manager->has_premium_access() ? '✅ Available' : '❌ Restricted') . "</li>";
echo "<li><strong>Certificate PDF Upload:</strong> " . ($license_manager->has_premium_access() ? '✅ Available' : '❌ Restricted') . "</li>";
echo "<li><strong>CSV Import:</strong> " . ($license_manager->has_premium_access() ? '✅ Available' : '❌ Restricted') . "</li>";
echo "<li><strong>Analytics Export:</strong> " . ($license_manager->has_premium_access() ? '✅ Available' : '❌ Restricted') . "</li>";
echo "<li><strong>School Logo Upload:</strong> " . ($license_manager->has_premium_access() ? '✅ Available' : '❌ Restricted') . "</li>";
echo "<li><strong>Student Limit:</strong> " . ($license_manager->has_premium_access() ? 'Unlimited' : '20 students') . "</li>";
echo "<li><strong>Contact Notice:</strong> " . ($license_manager->has_premium_access() ? '❌ Hidden' : '✅ Visible') . "</li>";
echo "</ul>";

echo "<h3>✅ 5. Expected Behavior</h3>";
echo "<p><strong>After activating a valid license:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>Contact Notice:</strong> Should disappear from all pages</li>";
echo "<li>✅ <strong>Upload Features:</strong> Should become available</li>";
echo "<li>✅ <strong>Premium Menus:</strong> Should appear in admin menu</li>";
echo "<li>✅ <strong>Student Limit:</strong> Should be unlimited</li>";
echo "<li>✅ <strong>AJAX Functions:</strong> Should work without restrictions</li>";
echo "</ul>";

echo "<h3>✅ 6. Manual Testing Steps</h3>";
echo "<ol>";
echo "<li><strong>Test Owner Key:</strong> Enter 'Bismillah^512' and activate</li>";
echo "<li><strong>Verify Owner Access:</strong> Check if all features are available</li>";
echo "<li><strong>Test Premium Key:</strong> Enter a valid 13-digit key and activate</li>";
echo "<li><strong>Verify Premium Access:</strong> Check if premium features are available</li>";
echo "<li><strong>Test Restrictions:</strong> Deactivate license and verify restrictions return</li>";
echo "</ol>";

echo "<h3>🔗 Quick Test Links</h3>";
echo "<ul>";
echo "<li><a href='?page=srm-dashboard'>Dashboard</a></li>";
echo "<li><a href='?page=srm-students'>Students</a></li>";
echo "<li><a href='?page=srm-results'>Results</a></li>";
echo "<li><a href='?page=srm-settings'>Settings</a></li>";
echo "<li><a href='?page=srm-premium'>Premium Features</a></li>";
echo "<li><a href='?page=srm-csv-import-export'>CSV Import/Export</a></li>";
echo "<li><a href='?page=srm-advanced-analytics'>Advanced Analytics</a></li>";
echo "</ul>";

echo "<h3>📱 Contact Information</h3>";
echo "<p><strong>WhatsApp:</strong> <a href='https://wa.me/923083430923' target='_blank'>+923083430923</a></p>";
echo "<p><strong>Email:</strong> <a href='mailto:jaffar381996152@gmail.com'>jaffar381996152@gmail.com</a></p>";

echo "<h3>🎯 Summary</h3>";
echo "<p><strong>License activation should now work properly:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>Owner Key:</strong> 'Bismillah^512' grants full owner access</li>";
echo "<li>✅ <strong>Premium Keys:</strong> Valid 13-digit keys grant premium access</li>";
echo "<li>✅ <strong>Access Control:</strong> Premium features become available</li>";
echo "<li>✅ <strong>Contact Notice:</strong> Disappears for licensed users</li>";
echo "<li>✅ <strong>Upload Features:</strong> Become available for licensed users</li>";
echo "<li>✅ <strong>Student Limit:</strong> Removed for licensed users</li>";
echo "</ul>";

echo "<h3>🧪 Test License Keys</h3>";
echo "<ul>";
echo "<li><strong>Owner Key:</strong> <code>Bismillah^512</code> (grants owner access)</li>";
echo "<li><strong>Valid Premium Keys:</strong> <code>B5XK!@#$%^&*F</code>, <code>J2G#ABC123@P</code>, <code>N8K$XYZ789%B</code></li>";
echo "<li><strong>Invalid Keys:</strong> <code>ABCDEFGHIJKLM</code>, <code>B5XKABCDEFGH</code></li>";
echo "</ul>";
?>