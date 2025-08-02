<?php
/**
 * Force refresh script to ensure all changes are active
 * Run this once, then delete the file
 */

if (!defined('ABSPATH')) exit;

// Clear any cached options
delete_option('srm_license_key');
delete_option('srm_license_status');
delete_option('srm_plugin_owner');

// Clear any transients
delete_transient('srm_license_check');

echo "<h2>Plugin Refresh Complete</h2>";
echo "<p>✅ License options cleared</p>";
echo "<p>✅ Plugin owner status reset</p>";
echo "<p>✅ License check cache cleared</p>";
echo "<br>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Go to <strong>Student Results > Premium Features</strong></li>";
echo "<li>You should now see the license activation form</li>";
echo "<li>Enter the owner key: <code>Bismillah^512</code></li>";
echo "<li>Click 'Activate License'</li>";
echo "<li>You should get full owner access</li>";
echo "</ol>";
echo "<br>";
echo "<p><strong>If you still see the old version:</strong></p>";
echo "<ul>";
echo "<li>Hard refresh your browser (Ctrl+F5 or Cmd+Shift+R)</li>";
echo "<li>Clear WordPress cache if you have caching plugins</li>";
echo "<li>Deactivate and reactivate the plugin</li>";
echo "<li>Check if you're looking at the correct WordPress site</li>";
echo "</ul>";
?>