<?php
/**
 * Test the fixed license system
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>Fixed License System Test</h2>";

// Include license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
$license_manager = new SRM_License_Manager();

echo "<h3>Current Status:</h3>";
echo "<ul>";
echo "<li><strong>License Key:</strong> " . ($license_manager->get_license_key() ?: 'None') . "</li>";
echo "<li><strong>License Status:</strong> " . $license_manager->get_license_status() . "</li>";
echo "<li><strong>Is Plugin Owner:</strong> " . ($license_manager->is_plugin_owner() ? 'Yes' : 'No') . "</li>";
echo "<li><strong>Has Premium Access:</strong> " . ($license_manager->has_premium_access() ? 'Yes' : 'No') . "</li>";
echo "<li><strong>Has Any License:</strong> " . ($license_manager->has_any_license() ? 'Yes' : 'No') . "</li>";
echo "</ul>";

echo "<h3>Test License Activation:</h3>";
echo "<form method='post'>";
echo "<input type='text' name='test_key' placeholder='Enter license key' style='width: 300px;'>";
echo "<input type='submit' name='test_activate' value='Test Activate' class='button'>";
echo "</form>";

if (isset($_POST['test_activate']) && !empty($_POST['test_key'])) {
    $test_key = sanitize_text_field($_POST['test_key']);
    $result = $license_manager->activate_license($test_key);
    
    echo "<h4>Activation Result:</h4>";
    echo "<p><strong>Success:</strong> " . ($result['success'] ? 'Yes' : 'No') . "</p>";
    echo "<p><strong>Message:</strong> " . $result['message'] . "</p>";
    
    if ($result['success']) {
        echo "<p><a href='?page=srm-premium'>Go to Premium Features</a></p>";
    }
}

echo "<h3>Test License Deactivation:</h3>";
echo "<form method='post'>";
echo "<input type='submit' name='test_deactivate' value='Test Deactivate' class='button'>";
echo "</form>";

if (isset($_POST['test_deactivate'])) {
    $result = $license_manager->deactivate_license();
    
    echo "<h4>Deactivation Result:</h4>";
    echo "<p><strong>Success:</strong> " . ($result['success'] ? 'Yes' : 'No') . "</p>";
    echo "<p><strong>Message:</strong> " . $result['message'] . "</p>";
}

echo "<h3>Test Free Features Access:</h3>";
echo "<ul>";
echo "<li><a href='?page=srm-dashboard'>Dashboard</a> - Should work for everyone</li>";
echo "<li><a href='?page=srm-students'>Students</a> - Should work for everyone (20 limit for free)</li>";
echo "<li><a href='?page=srm-results'>Results</a> - Should work for everyone</li>";
echo "<li><a href='?page=srm-premium'>Premium Features</a> - Should show license form</li>";
echo "</ul>";

echo "<h3>Expected Behavior:</h3>";
echo "<ol>";
echo "<li><strong>No License:</strong> Free features work, premium features redirect to license activation</li>";
echo "<li><strong>Owner Key:</strong> Full access + License Keys and Testing Mode visible</li>";
echo "<li><strong>Premium Key:</strong> Premium access but no License Keys or Testing Mode</li>";
echo "<li><strong>After Deactivation:</strong> Back to free features, license form always available</li>";
echo "</ol>";

echo "<h3>Test Steps:</h3>";
echo "<ol>";
echo "<li>Clear license data (if needed)</li>";
echo "<li>Try accessing free features - should work</li>";
echo "<li>Try accessing premium features - should redirect to license activation</li>";
echo "<li>Activate with owner key: <code>Bismillah^512</code></li>";
echo "<li>Check if License Keys and Testing Mode are visible</li>";
echo "<li>Deactivate license</li>";
echo "<li>Check if free features still work</li>";
echo "<li>Check if license form is still available</li>";
echo "</ol>";
?>