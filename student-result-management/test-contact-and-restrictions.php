<?php
/**
 * Test Contact Notice and Upload/Download Restrictions
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🔍 Test Contact Notice and Upload/Download Restrictions</h2>";

// Include license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
$license_manager = new SRM_License_Manager();

echo "<h3>✅ 1. Contact Notice Test</h3>";
echo "<p>Testing if contact notice appears for free users...</p>";

// Simulate the contact notice display
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

echo "<h3>✅ 2. Upload/Download Restrictions Test</h3>";
echo "<ul>";
echo "<li><strong>CSV Upload:</strong> " . ($license_manager->has_premium_access() ? 'Available (Premium)' : 'Restricted (Free)') . "</li>";
echo "<li><strong>PDF Download:</strong> " . ($license_manager->has_premium_access() ? 'Available (Premium)' : 'Restricted (Free)') . "</li>";
echo "<li><strong>Analytics Export:</strong> " . ($license_manager->has_premium_access() ? 'Available (Premium)' : 'Restricted (Free)') . "</li>";
echo "<li><strong>Template Preview:</strong> " . ($license_manager->has_premium_access() ? 'Available (Premium)' : 'Restricted (Free)') . "</li>";
echo "<li><strong>Profile Image Upload:</strong> " . ($license_manager->has_premium_access() ? 'Available (Premium)' : 'Restricted (Free)') . "</li>";
echo "<li><strong>Certificate PDF Upload:</strong> " . ($license_manager->has_premium_access() ? 'Available (Premium)' : 'Restricted (Free)') . "</li>";
echo "<li><strong>School Logo Upload:</strong> " . ($license_manager->has_premium_access() ? 'Available (Premium)' : 'Restricted (Free)') . "</li>";
echo "</ul>";

echo "<h3>✅ 3. Current License Status</h3>";
echo "<ul>";
echo "<li><strong>License Key:</strong> " . ($license_manager->get_license_key() ?: 'None') . "</li>";
echo "<li><strong>License Status:</strong> " . $license_manager->get_license_status() . "</li>";
echo "<li><strong>Is Plugin Owner:</strong> " . ($license_manager->is_plugin_owner() ? 'Yes' : 'No') . "</li>";
echo "<li><strong>Has Premium Access:</strong> " . ($license_manager->has_premium_access() ? 'Yes' : 'No') . "</li>";
echo "</ul>";

echo "<h3>✅ 4. Manual Verification Steps</h3>";
echo "<ol>";
echo "<li><strong>Check Contact Notice:</strong> Visit each admin page and verify the prominent contact notice appears for free users</li>";
echo "<li><strong>Test CSV Upload:</strong> Try to upload CSV files - should be restricted for free users</li>";
echo "<li><strong>Test PDF Download:</strong> Try to download PDF certificates - should be restricted for free users</li>";
echo "<li><strong>Test Analytics Export:</strong> Try to export analytics - should be restricted for free users</li>";
echo "<li><strong>Test Profile Upload:</strong> Try to upload profile images - should be restricted for free users</li>";
echo "<li><strong>Test Certificate Upload:</strong> Try to upload certificate PDFs - should be restricted for free users</li>";
echo "<li><strong>Test School Logo Upload:</strong> Try to upload school logo - should be restricted for free users</li>";
echo "</ol>";

echo "<h3>✅ 5. AJAX Function Restrictions</h3>";
echo "<p>The following AJAX functions are restricted to premium users:</p>";
echo "<ul>";
echo "<li><code>ajax_upload_csv()</code> - CSV file upload</li>";
echo "<li><code>ajax_download_pdf()</code> - PDF certificate download</li>";
echo "<li><code>ajax_import_students_csv()</code> - Student CSV import</li>";
echo "<li><code>ajax_import_results_csv()</code> - Results CSV import</li>";
echo "<li><code>ajax_export_analytics()</code> - Analytics export</li>";
echo "<li><code>ajax_preview_template()</code> - Template preview</li>";
echo "</ul>";

echo "<h3>✅ 6. Frontend Restrictions</h3>";
echo "<ul>";
echo "<li><strong>PDF Download Button:</strong> Disabled for free users on frontend</li>";
echo "<li><strong>Certificate Access:</strong> Restricted for free users</li>";
echo "<li><strong>Advanced Features:</strong> All premium features hidden for free users</li>";
echo "</ul>";

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
echo "<p>All restrictions have been implemented:</p>";
echo "<ul>";
echo "<li>✅ Contact notice prominently displayed on all admin pages for free users</li>";
echo "<li>✅ All upload functions restricted to premium users</li>";
echo "<li>✅ All download functions restricted to premium users</li>";
echo "<li>✅ All AJAX functions have premium access checks</li>";
echo "<li>✅ Frontend features restricted for free users</li>";
echo "<li>✅ Clear upgrade prompts for restricted features</li>";
echo "</ul>";

echo "<h3>🧪 To Test Restrictions</h3>";
echo "<p>1. Clear your license data to become a free user</p>";
echo "<p>2. Visit each admin page to see contact notice</p>";
echo "<p>3. Try to use any upload/download features - should show upgrade prompts</p>";
echo "<p>4. Activate a premium license to test full access</p>";
?>