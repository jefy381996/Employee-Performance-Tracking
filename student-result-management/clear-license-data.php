<?php
/**
 * Clear all license data for testing
 * Run this once, then delete the file
 */

if (!defined('ABSPATH')) exit;

// Clear all license-related options
delete_option('srm_license_key');
delete_option('srm_license_status');
delete_option('srm_plugin_owner');
delete_option('srm_valid_license_keys');

// Clear any transients
delete_transient('srm_license_check');

echo "<h2>License Data Cleared</h2>";
echo "<p>✅ All license data has been cleared</p>";
echo "<p>✅ Plugin owner status reset</p>";
echo "<p>✅ Valid license keys cleared</p>";
echo "<p>✅ License cache cleared</p>";
echo "<br>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Go to <strong>Student Results > Premium Features</strong></li>";
echo "<li>You should see the license activation form</li>";
echo "<li>Enter the owner key: <code>Bismillah^512</code></li>";
echo "<li>Click 'Activate License'</li>";
echo "<li>You should get full owner access</li>";
echo "</ol>";
echo "<br>";
echo "<p><strong>Test the system:</strong></p>";
echo "<ul>";
echo "<li>Try accessing other plugin pages - should redirect to license activation</li>";
echo "<li>Activate with owner key - should get owner access</li>";
echo "<li>Deactivate license - should lose access</li>";
echo "<li>Add premium keys in License Keys section</li>";
echo "<li>Test with premium keys - should get premium access (not owner)</li>";
echo "</ul>";
?>