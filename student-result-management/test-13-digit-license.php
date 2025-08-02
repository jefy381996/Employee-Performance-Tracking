<?php
/**
 * Test the new 13-digit license key validation
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>13-Digit License Key Validation Test</h2>";

// Include license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
$license_manager = new SRM_License_Manager();

echo "<h3>License Key Format Requirements:</h3>";
echo "<ul>";
echo "<li><strong>Length:</strong> Exactly 13 characters</li>";
echo "<li><strong>1st character:</strong> B, J, N, A, F, or T</li>";
echo "<li><strong>4th character:</strong> X, G, K, D, E, or P</li>";
echo "<li><strong>8th, 9th, or 10th character:</strong> Special character (!@#$%^&*() etc.)</li>";
echo "<li><strong>13th character:</strong> B, G, N, K, F, or P</li>";
echo "</ul>";

echo "<h3>Test License Key Validation:</h3>";
echo "<form method='post'>";
echo "<input type='text' name='test_key' placeholder='Enter 13-digit license key' style='width: 300px;' maxlength='13'>";
echo "<input type='submit' name='test_validate' value='Test Validation' class='button'>";
echo "</form>";

if (isset($_POST['test_validate']) && !empty($_POST['test_key'])) {
    $test_key = sanitize_text_field($_POST['test_key']);
    
    echo "<h4>Validation Result for: <code>$test_key</code></h4>";
    
    // Test the validation logic
    $is_valid = $license_manager->activate_license($test_key);
    
    echo "<p><strong>Success:</strong> " . ($is_valid['success'] ? 'Yes' : 'No') . "</p>";
    echo "<p><strong>Message:</strong> " . $is_valid['message'] . "</p>";
    
    if ($is_valid['success']) {
        echo "<p><a href='?page=srm-premium'>Go to Premium Features</a></p>";
    }
}

echo "<h3>Example Valid License Keys:</h3>";
echo "<ul>";
echo "<li><code>B5XK!@#$%^&*F</code> - Valid (B at start, X at 4th, ! at 8th, F at end)</li>";
echo "<li><code>J2G#ABC123@P</code> - Valid (J at start, G at 4th, # at 9th, P at end)</li>";
echo "<li><code>N8K$XYZ789%B</code> - Valid (N at start, K at 4th, $ at 10th, B at end)</li>";
echo "</ul>";

echo "<h3>Example Invalid License Keys:</h3>";
echo "<ul>";
echo "<li><code>ABCDEFGHIJKLM</code> - Invalid (wrong 1st, 4th, 8-10th, 13th characters)</li>";
echo "<li><code>B5XKABCDEFGH</code> - Invalid (no special char at 8-10th position)</li>";
echo "<li><code>B5XK!@#$%^&*Z</code> - Invalid (Z not allowed at 13th position)</li>";
echo "<li><code>Z5XK!@#$%^&*F</code> - Invalid (Z not allowed at 1st position)</li>";
echo "</ul>";

echo "<h3>Test Owner Key:</h3>";
echo "<p>Owner key <code>Bismillah^512</code> should give owner access.</p>";

echo "<h3>Current License Status:</h3>";
echo "<ul>";
echo "<li><strong>License Key:</strong> " . ($license_manager->get_license_key() ?: 'None') . "</li>";
echo "<li><strong>License Status:</strong> " . $license_manager->get_license_status() . "</li>";
echo "<li><strong>Is Plugin Owner:</strong> " . ($license_manager->is_plugin_owner() ? 'Yes' : 'No') . "</li>";
echo "<li><strong>Has Premium Access:</strong> " . ($license_manager->has_premium_access() ? 'Yes' : 'No') . "</li>";
echo "</ul>";

echo "<h3>Test Steps:</h3>";
echo "<ol>";
echo "<li>Try the owner key: <code>Bismillah^512</code></li>";
echo "<li>Try a valid 13-digit key (see examples above)</li>";
echo "<li>Try an invalid key to see error messages</li>";
echo "<li>Check if premium features are accessible after activation</li>";
echo "</ol>";
?>