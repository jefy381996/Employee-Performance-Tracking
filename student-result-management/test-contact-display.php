<?php
/**
 * Test Contact Display and Upload Restrictions
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🔍 Test Contact Display and Upload Restrictions</h2>";

// Include license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
$license_manager = new SRM_License_Manager();

echo "<h3>✅ 1. Current License Status</h3>";
echo "<ul>";
echo "<li><strong>License Key:</strong> " . ($license_manager->get_license_key() ?: 'None') . "</li>";
echo "<li><strong>License Status:</strong> " . $license_manager->get_license_status() . "</li>";
echo "<li><strong>Is Plugin Owner:</strong> " . ($license_manager->is_plugin_owner() ? 'Yes' : 'No') . "</li>";
echo "<li><strong>Has Premium Access:</strong> " . ($license_manager->has_premium_access() ? 'Yes' : 'No') . "</li>";
echo "</ul>";

echo "<h3>✅ 2. Contact Notice Test</h3>";
if (!$license_manager->has_premium_access()) {
    echo '<div class="notice notice-info" style="margin: 20px 0; padding: 20px; background: #f0f8ff; border-left: 4px solid #0073aa; font-size: 16px; text-align: center;">';
    echo '<h2 style="margin: 0 0 15px 0; color: #0073aa; font-size: 20px;">📞 Contact for Premium Version</h2>';
    echo '<p style="margin: 0 0 10px 0; font-size: 16px;"><strong>To buy the full Version, Contact Jaffar Abbas:</strong></p>';
    echo '<div style="display: flex; justify-content: center; gap: 30px; margin-top: 15px;">';
    echo '<div style="text-align: center;"><strong>WhatsApp:</strong><br><a href="https://wa.me/923083430923" target="_blank" style="font-size: 18px; color: #0073aa;">+923083430923</a></div>';
    echo '<div style="text-align: center;"><strong>Email:</strong><br><a href="mailto:jaffar381996152@gmail.com" style="font-size: 18px; color: #0073aa;">jaffar381996152@gmail.com</a></div>';
    echo '</div>';
    echo '</div>';
    echo "<p style='color: green;'><strong>✅ Contact notice should appear above for free users</strong></p>";
} else {
    echo "<p style='color: blue;'><strong>ℹ️ You have premium access, so contact notice is hidden</strong></p>";
}

echo "<h3>✅ 3. Upload Restrictions Test</h3>";
echo "<ul>";
echo "<li><strong>Profile Image Upload:</strong> " . ($license_manager->has_premium_access() ? 'Available (Premium)' : 'Restricted (Free)') . "</li>";
echo "<li><strong>Certificate PDF Upload:</strong> " . ($license_manager->has_premium_access() ? 'Available (Premium)' : 'Restricted (Free)') . "</li>";
echo "<li><strong>CSV Import:</strong> " . ($license_manager->has_premium_access() ? 'Available (Premium)' : 'Restricted (Free)') . "</li>";
echo "<li><strong>School Logo Upload:</strong> " . ($license_manager->has_premium_access() ? 'Available (Premium)' : 'Restricted (Free)') . "</li>";
echo "</ul>";

echo "<h3>✅ 4. Manual Test Instructions</h3>";
echo "<ol>";
echo "<li><strong>Clear License:</strong> Go to Premium Features page and deactivate your license</li>";
echo "<li><strong>Check Contact Notice:</strong> Visit any admin page - should see prominent contact notice</li>";
echo "<li><strong>Test Profile Upload:</strong> Go to Students > Add New - profile image upload should be hidden</li>";
echo "<li><strong>Test Certificate Upload:</strong> Go to Results > Add New - certificate PDF upload should be hidden</li>";
echo "<li><strong>Test CSV Import:</strong> Go to CSV Import/Export - should show upgrade prompt</li>";
echo "<li><strong>Test Settings:</strong> Go to Settings - school logo upload should be hidden</li>";
echo "</ol>";

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

echo "<h3>🎯 Expected Behavior</h3>";
echo "<ul>";
echo "<li>✅ <strong>Free Users:</strong> Should see contact notice on every page</li>";
echo "<li>✅ <strong>Free Users:</strong> Upload fields should be hidden/disabled</li>";
echo "<li>✅ <strong>Premium Users:</strong> No contact notice, full access</li>";
echo "<li>✅ <strong>Owner:</strong> No contact notice, full access</li>";
echo "</ul>";
?>