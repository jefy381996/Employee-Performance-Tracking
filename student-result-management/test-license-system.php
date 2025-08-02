<?php
/**
 * Test the license system
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>License System Test</h2>";

// Include license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
$license_manager = new SRM_License_Manager();

echo "<h3>Current License Status:</h3>";
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

echo "<h3>Valid License Keys:</h3>";
$valid_keys = $license_manager->get_valid_license_keys();
if (empty($valid_keys)) {
    echo "<p>No valid license keys found. Add some keys in License Keys section.</p>";
} else {
    echo "<ul>";
    foreach ($valid_keys as $key) {
        echo "<li><code>$key</code></li>";
    }
    echo "</ul>";
}

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Try activating with owner key: <code>Bismillah^512</code></li>";
echo "<li>Check if you get owner access</li>";
echo "<li>Try deactivating the license</li>";
echo "<li>Check if you're redirected to license activation</li>";
echo "</ol>";
?>