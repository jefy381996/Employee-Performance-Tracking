<?php
/**
 * Test menu visibility
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>Menu Visibility Test</h2>";

// Include license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
$license_manager = new SRM_License_Manager();

echo "<h3>Current License Status:</h3>";
echo "<ul>";
echo "<li><strong>License Key:</strong> " . ($license_manager->get_license_key() ?: 'None') . "</li>";
echo "<li><strong>License Status:</strong> " . $license_manager->get_license_status() . "</li>";
echo "<li><strong>Is Plugin Owner:</strong> " . ($license_manager->is_plugin_owner() ? 'Yes' : 'No') . "</li>";
echo "<li><strong>Has Premium Access:</strong> " . ($license_manager->has_premium_access() ? 'Yes' : 'No') . "</li>";
echo "</ul>";

echo "<h3>Expected Menu Items:</h3>";

if ($license_manager->is_plugin_owner()) {
    echo "<h4>For Plugin Owner (should see all):</h4>";
    echo "<ul>";
    echo "<li>✅ Dashboard</li>";
    echo "<li>✅ Students</li>";
    echo "<li>✅ Results</li>";
    echo "<li>✅ Settings</li>";
    echo "<li>✅ Premium Features</li>";
    echo "<li>✅ CSV Import/Export</li>";
    echo "<li>✅ Advanced Analytics</li>";
    echo "<li>✅ Email Notifications</li>";
    echo "<li>✅ Data Backup & Restore</li>";
    echo "<li>✅ Custom Templates</li>";
    echo "<li>✅ License Keys</li>";
    echo "<li>✅ Testing Mode</li>";
    echo "</ul>";
} elseif ($license_manager->has_premium_access()) {
    echo "<h4>For Premium User (should NOT see License Keys or Testing Mode):</h4>";
    echo "<ul>";
    echo "<li>✅ Dashboard</li>";
    echo "<li>✅ Students</li>";
    echo "<li>✅ Results</li>";
    echo "<li>✅ Settings</li>";
    echo "<li>✅ Premium Features</li>";
    echo "<li>✅ CSV Import/Export</li>";
    echo "<li>✅ Advanced Analytics</li>";
    echo "<li>✅ Email Notifications</li>";
    echo "<li>✅ Data Backup & Restore</li>";
    echo "<li>✅ Custom Templates</li>";
    echo "<li>❌ License Keys (should be hidden)</li>";
    echo "<li>❌ Testing Mode (should be hidden)</li>";
    echo "</ul>";
} else {
    echo "<h4>For Free User (should only see basic features):</h4>";
    echo "<ul>";
    echo "<li>✅ Dashboard</li>";
    echo "<li>✅ Students (20 limit)</li>";
    echo "<li>✅ Results</li>";
    echo "<li>✅ Settings</li>";
    echo "<li>✅ Premium Features (license form)</li>";
    echo "<li>❌ CSV Import/Export (should be hidden)</li>";
    echo "<li>❌ Advanced Analytics (should be hidden)</li>";
    echo "<li>❌ Email Notifications (should be hidden)</li>";
    echo "<li>❌ Data Backup & Restore (should be hidden)</li>";
    echo "<li>❌ Custom Templates (should be hidden)</li>";
    echo "<li>❌ License Keys (should be hidden)</li>";
    echo "<li>❌ Testing Mode (should be hidden)</li>";
    echo "</ul>";
}

echo "<h3>Test Steps:</h3>";
echo "<ol>";
echo "<li>Check your WordPress admin menu</li>";
echo "<li>Look under 'Student Results' menu</li>";
echo "<li>Verify only appropriate items are visible</li>";
echo "<li>If you're owner, you should see License Keys and Testing Mode</li>";
echo "<li>If you're premium user, you should NOT see License Keys or Testing Mode</li>";
echo "<li>If you're free user, you should only see basic features</li>";
echo "</ol>";

echo "<h3>To Test Different Roles:</h3>";
echo "<ul>";
echo "<li><strong>Clear License:</strong> <a href='?page=srm-premium&clear_license=1'>Clear License Data</a></li>";
echo "<li><strong>Activate Owner:</strong> Go to Premium Features and enter: <code>Bismillah^512</code></li>";
echo "<li><strong>Activate Premium:</strong> Add a premium key in License Keys section, then activate it</li>";
echo "</ul>";
?>