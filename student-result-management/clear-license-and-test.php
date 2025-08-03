<?php
/**
 * Clear License Data and Test Fresh Installation
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🧹 Clear License Data and Test Fresh Installation</h2>";

// Include license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
$license_manager = new SRM_License_Manager();

echo "<h3>✅ 1. Current License Status (Before Clearing)</h3>";
echo "<ul>";
echo "<li><strong>License Key:</strong> " . ($license_manager->get_license_key() ?: 'None') . "</li>";
echo "<li><strong>License Status:</strong> " . $license_manager->get_license_status() . "</li>";
echo "<li><strong>Is Plugin Owner:</strong> " . ($license_manager->is_plugin_owner() ? 'Yes' : 'No') . "</li>";
echo "<li><strong>Has Premium Access:</strong> " . ($license_manager->has_premium_access() ? 'Yes' : 'No') . "</li>";
echo "</ul>";

echo "<h3>✅ 2. Clearing License Data</h3>";

if (isset($_POST['clear_license'])) {
    // Clear all license-related options
    delete_option('srm_license_key');
    delete_option('srm_license_status');
    delete_option('srm_plugin_owner');
    
    // Set fresh free status
    update_option('srm_license_key', '');
    update_option('srm_license_status', 'free');
    
    echo "<div class='notice notice-success'><p><strong>✅ License data cleared successfully!</strong></p></div>";
    
    echo "<h3>✅ 3. License Status After Clearing</h3>";
    echo "<ul>";
    echo "<li><strong>License Key:</strong> " . ($license_manager->get_license_key() ?: 'None') . "</li>";
    echo "<li><strong>License Status:</strong> " . $license_manager->get_license_status() . "</li>";
    echo "<li><strong>Is Plugin Owner:</strong> " . ($license_manager->is_plugin_owner() ? 'Yes' : 'No') . "</li>";
    echo "<li><strong>Has Premium Access:</strong> " . ($license_manager->has_premium_access() ? 'Yes' : 'No') . "</li>";
    echo "</ul>";
    
    echo "<h3>✅ 4. Expected Behavior After Clearing</h3>";
    echo "<ul>";
    echo "<li>✅ <strong>Contact Notice:</strong> Should appear on all admin pages</li>";
    echo "<li>✅ <strong>Upload Restrictions:</strong> All upload features should be hidden</li>";
    echo "<li>✅ <strong>Premium Features:</strong> Should be restricted</li>";
    echo "<li>✅ <strong>Owner Access:</strong> Should require license activation</li>";
    echo "</ul>";
    
    echo "<h3>✅ 5. Test License Activation</h3>";
    echo "<p><strong>To test owner access:</strong></p>";
    echo "<ol>";
    echo "<li>Go to <a href='?page=srm-premium'>Premium Features</a> page</li>";
    echo "<li>Enter the owner key: <code>Bismillah^512</code></li>";
    echo "<li>Click 'Activate License'</li>";
    echo "<li>You should get owner access with full features</li>";
    echo "</ol>";
    
    echo "<p><strong>To test premium access:</strong></p>";
    echo "<ol>";
    echo "<li>Go to <a href='?page=srm-premium'>Premium Features</a> page</li>";
    echo "<li>Enter a valid 13-digit premium key (e.g., <code>B5XK!@#$%^&*F</code>)</li>";
    echo "<li>Click 'Activate License'</li>";
    echo "<li>You should get premium access (but not owner access)</li>";
    echo "</ol>";
    
} else {
    echo "<form method='post'>";
    echo "<p><strong>Click the button below to clear all license data and simulate a fresh installation:</strong></p>";
    echo "<input type='submit' name='clear_license' value='Clear License Data' class='button button-primary'>";
    echo "</form>";
    
    echo "<h3>✅ 6. What This Will Do</h3>";
    echo "<ul>";
    echo "<li>✅ <strong>Clear License Key:</strong> Remove any existing license key</li>";
    echo "<li>✅ <strong>Reset License Status:</strong> Set status to 'free'</li>";
    echo "<li>✅ <strong>Clear Owner Status:</strong> Remove plugin owner designation</li>";
    echo "<li>✅ <strong>Simulate Fresh Install:</strong> Plugin will behave like new installation</li>";
    echo "</ul>";
    
    echo "<h3>✅ 7. After Clearing - Expected Behavior</h3>";
    echo "<ul>";
    echo "<li>✅ <strong>Contact Notice:</strong> Prominent contact notice on all pages</li>";
    echo "<li>✅ <strong>Upload Restrictions:</strong> Profile image, certificate PDF, CSV import all hidden</li>";
    echo "<li>✅ <strong>Premium Features:</strong> Analytics, templates, notifications all restricted</li>";
    echo "<li>✅ <strong>Owner Features:</strong> License management hidden until owner key activated</li>";
    echo "<li>✅ <strong>Student Limit:</strong> Limited to 20 students for free users</li>";
    echo "</ul>";
}

echo "<h3>🔗 Quick Test Links</h3>";
echo "<ul>";
echo "<li><a href='?page=srm-dashboard'>Dashboard</a></li>";
echo "<li><a href='?page=srm-students'>Students</a></li>";
echo "<li><a href='?page=srm-results'>Results</a></li>";
echo "<li><a href='?page=srm-settings'>Settings</a></li>";
echo "<li><a href='?page=srm-premium'>Premium Features</a></li>";
echo "</ul>";

echo "<h3>📱 Contact Information</h3>";
echo "<p><strong>WhatsApp:</strong> <a href='https://wa.me/923083430923' target='_blank'>+923083430923</a></p>";
echo "<p><strong>Email:</strong> <a href='mailto:jaffar381996152@gmail.com'>jaffar381996152@gmail.com</a></p>";

echo "<h3>🎯 Summary</h3>";
echo "<p><strong>Plugin should now start as free version:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>Fresh Installation:</strong> No license activated by default</li>";
echo "<li>✅ <strong>Contact Notice:</strong> Prominent display for free users</li>";
echo "<li>✅ <strong>Upload Restrictions:</strong> All premium features restricted</li>";
echo "<li>✅ <strong>License Required:</strong> Users must activate license for premium features</li>";
echo "<li>✅ <strong>Owner Key Required:</strong> Even owner must activate 'Bismillah^512'</li>";
echo "</ul>";
?>