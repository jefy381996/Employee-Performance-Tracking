<?php
/**
 * Test Fresh Installation Behavior
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🧪 Test Fresh Installation Behavior</h2>";

// Include license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
$license_manager = new SRM_License_Manager();

echo "<h3>✅ 1. Current Installation Status</h3>";
echo "<ul>";
echo "<li><strong>License Key:</strong> " . ($license_manager->get_license_key() ?: 'None') . "</li>";
echo "<li><strong>License Status:</strong> " . $license_manager->get_license_status() . "</li>";
echo "<li><strong>Is Plugin Owner:</strong> " . ($license_manager->is_plugin_owner() ? 'Yes' : 'No') . "</li>";
echo "<li><strong>Has Premium Access:</strong> " . ($license_manager->has_premium_access() ? 'Yes' : 'No') . "</li>";
echo "<li><strong>Plugin Owner ID:</strong> " . (get_option('srm_plugin_owner') ?: 'Not Set') . "</li>";
echo "</ul>";

echo "<h3>✅ 2. Fresh Installation Requirements</h3>";
echo "<p><strong>The plugin should now behave like a fresh installation:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>No License Activated:</strong> License key should be empty</li>";
echo "<li>✅ <strong>Free Status:</strong> License status should be 'free'</li>";
echo "<li>✅ <strong>No Owner Set:</strong> Plugin owner should not be automatically set</li>";
echo "<li>✅ <strong>Contact Notice:</strong> Should appear on all admin pages</li>";
echo "<li>✅ <strong>Upload Restrictions:</strong> All premium features should be restricted</li>";
echo "</ul>";

echo "<h3>✅ 3. Expected Behavior for Free Users</h3>";
echo "<ul>";
echo "<li>✅ <strong>Contact Notice:</strong> Prominent display on all admin pages</li>";
echo "<li>✅ <strong>Profile Image Upload:</strong> Hidden in Students > Add/Edit</li>";
echo "<li>✅ <strong>Certificate PDF Upload:</strong> Hidden in Results > Add/Edit</li>";
echo "<li>✅ <strong>CSV Import:</strong> Restricted with upgrade prompts</li>";
echo "<li>✅ <strong>School Logo Upload:</strong> Hidden in Settings</li>";
echo "<li>✅ <strong>Analytics Export:</strong> Restricted with upgrade prompts</li>";
echo "<li>✅ <strong>Student Limit:</strong> Limited to 20 students</li>";
echo "<li>✅ <strong>Premium Features:</strong> All premium menus hidden</li>";
echo "</ul>";

echo "<h3>✅ 4. License Activation Process</h3>";
echo "<p><strong>To activate owner access:</strong></p>";
echo "<ol>";
echo "<li>Go to <a href='?page=srm-premium'>Premium Features</a> page</li>";
echo "<li>Enter owner key: <code>Bismillah^512</code></li>";
echo "<li>Click 'Activate License'</li>";
echo "<li>You should get full owner access</li>";
echo "</ol>";

echo "<p><strong>To activate premium access:</strong></p>";
echo "<ol>";
echo "<li>Go to <a href='?page=srm-premium'>Premium Features</a> page</li>";
echo "<li>Enter valid 13-digit key (e.g., <code>B5XK!@#$%^&*F</code>)</li>";
echo "<li>Click 'Activate License'</li>";
echo "<li>You should get premium access (not owner access)</li>";
echo "</ol>";

echo "<h3>✅ 5. Test Scenarios</h3>";
echo "<p><strong>Scenario 1: Fresh Installation (Current State)</strong></p>";
echo "<ul>";
echo "<li>✅ Contact notice should be visible</li>";
echo "<li>✅ Upload features should be restricted</li>";
echo "<li>✅ Premium features should be hidden</li>";
echo "<li>✅ Student limit should be 20</li>";
echo "</ul>";

echo "<p><strong>Scenario 2: After Owner Key Activation</strong></p>";
echo "<ul>";
echo "<li>✅ Contact notice should disappear</li>";
echo "<li>✅ All upload features should be available</li>";
echo "<li>✅ All premium features should be available</li>";
echo "<li>✅ No student limit</li>";
echo "<li>✅ Owner management features available</li>";
echo "</ul>";

echo "<p><strong>Scenario 3: After Premium Key Activation</strong></p>";
echo "<ul>";
echo "<li>✅ Contact notice should disappear</li>";
echo "<li>✅ All upload features should be available</li>";
echo "<li>✅ All premium features should be available</li>";
echo "<li>✅ No student limit</li>";
echo "<li>✅ Owner management features NOT available</li>";
echo "</ul>";

echo "<h3>✅ 6. Manual Testing Steps</h3>";
echo "<ol>";
echo "<li><strong>Check Current State:</strong> Verify you're in free mode (contact notice visible)</li>";
echo "<li><strong>Test Upload Restrictions:</strong> Try to add students/results - upload fields should be hidden</li>";
echo "<li><strong>Test Premium Features:</strong> Try to access CSV import, analytics - should be restricted</li>";
echo "<li><strong>Activate Owner Key:</strong> Go to Premium Features, enter 'Bismillah^512'</li>";
echo "<li><strong>Verify Owner Access:</strong> Contact notice should disappear, all features available</li>";
echo "<li><strong>Deactivate License:</strong> Go back to Premium Features, click 'Deactivate'</li>";
echo "<li><strong>Verify Free Mode:</strong> Contact notice should reappear, features restricted</li>";
echo "</ol>";

echo "<h3>✅ 7. Fresh Installation Verification</h3>";
echo "<p><strong>To simulate a completely fresh installation:</strong></p>";
echo "<ol>";
echo "<li>Go to <a href='?page=clear-license-and-test'>Clear License Data</a> page</li>";
echo "<li>Click 'Clear License Data' button</li>";
echo "<li>Verify all license data is cleared</li>";
echo "<li>Test that plugin behaves like fresh installation</li>";
echo "</ol>";

echo "<h3>🔗 Quick Test Links</h3>";
echo "<ul>";
echo "<li><a href='?page=srm-dashboard'>Dashboard</a></li>";
echo "<li><a href='?page=srm-students'>Students</a></li>";
echo "<li><a href='?page=srm-results'>Results</a></li>";
echo "<li><a href='?page=srm-settings'>Settings</a></li>";
echo "<li><a href='?page=srm-premium'>Premium Features</a></li>";
echo "<li><a href='?page=clear-license-and-test'>Clear License Data</a></li>";
echo "</ul>";

echo "<h3>📱 Contact Information</h3>";
echo "<p><strong>WhatsApp:</strong> <a href='https://wa.me/923083430923' target='_blank'>+923083430923</a></p>";
echo "<p><strong>Email:</strong> <a href='mailto:jaffar381996152@gmail.com'>jaffar381996152@gmail.com</a></p>";

echo "<h3>🎯 Expected Fresh Installation Behavior</h3>";
echo "<ul>";
echo "<li>✅ <strong>No License:</strong> Plugin starts with no license activated</li>";
echo "<li>✅ <strong>Free Status:</strong> All users start with free access</li>";
echo "<li>✅ <strong>Contact Notice:</strong> Prominent display for all free users</li>";
echo "<li>✅ <strong>Upload Restrictions:</strong> All premium features restricted</li>";
echo "<li>✅ <strong>License Required:</strong> Users must activate license for premium features</li>";
echo "<li>✅ <strong>Owner Key Required:</strong> Even owner must activate 'Bismillah^512'</li>";
echo "<li>✅ <strong>Student Limit:</strong> 20 students for free users</li>";
echo "<li>✅ <strong>Clear Upgrade Path:</strong> Users know how to upgrade</li>";
echo "</ul>";

echo "<h3>🧪 Test License Keys</h3>";
echo "<ul>";
echo "<li><strong>Owner Key:</strong> <code>Bismillah^512</code> (grants owner access)</li>";
echo "<li><strong>Valid Premium Keys:</strong> <code>B5XK!@#$%^&*F</code>, <code>J2G#ABC123@P</code>, <code>N8K$XYZ789%B</code></li>";
echo "<li><strong>Invalid Keys:</strong> <code>ABCDEFGHIJKLM</code>, <code>B5XKABCDEFGH</code></li>";
echo "</ul>";
?>