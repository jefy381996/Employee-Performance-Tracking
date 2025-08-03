<?php
/**
 * Test License System
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🧪 License System Test</h2>";

// Include license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';

$license_manager = new SRM_License_Manager();

echo "<div style='background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);'>";
echo "<h3>📊 Current System Status</h3>";

// Test 1: Domain Information
echo "<h4>🌐 Domain Information</h4>";
$domain_info = $license_manager->get_domain_info();
echo "<p><strong>Current Domain:</strong> " . esc_html($domain_info['domain']) . "</p>";
echo "<p><strong>Full URL:</strong> " . esc_html($domain_info['full_url']) . "</p>";
echo "<p><strong>Server Name:</strong> " . esc_html($domain_info['server_name']) . "</p>";
echo "<p><strong>HTTP Host:</strong> " . esc_html($domain_info['http_host']) . "</p>";

// Test 2: License File Status
echo "<h4>📄 License File Status</h4>";
echo "<p><strong>License File Exists:</strong> " . ($license_manager->has_license_file() ? 'Yes' : 'No') . "</p>";
echo "<p><strong>License File Path:</strong> " . esc_html($license_manager->get_license_file_path()) . "</p>";

// Test 3: Current License Status
echo "<h4>🔑 Current License Status</h4>";
$license_info = $license_manager->get_license_info();
echo "<p><strong>License Key:</strong> " . esc_html($license_info['key']) . "</p>";
echo "<p><strong>License Status:</strong> " . esc_html($license_info['status']) . "</p>";
echo "<p><strong>Is Owner:</strong> " . ($license_info['is_owner'] ? 'Yes' : 'No') . "</p>";
echo "<p><strong>Has Premium:</strong> " . ($license_info['has_premium'] ? 'Yes' : 'No') . "</p>";

echo "</div>";

// Test 4: License Key Validation
echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 20px; margin: 20px 0;'>";
echo "<h3>🔍 License Key Validation Test</h3>";

$test_keys = array(
    'Bismillah^512', // Owner key
    'BJKmNpQrStU*localhost', // Valid 13-digit key for localhost
    'FGHjKlMnOpQ#localhost', // Another valid key
    'ABC123!DEF456.localhost', // Invalid format
    'BJKmNpQrStU*example.com', // Valid key but wrong domain
    'XYZ789@GHI012.localhost', // Valid key for localhost
);

foreach ($test_keys as $test_key) {
    $is_valid = $license_manager->validate_license_format($test_key);
    $domain_match = $license_manager->is_valid_domain_license($test_key);
    $status = $is_valid ? ($domain_match ? '✅ Valid & Domain Match' : '⚠️ Valid but Wrong Domain' : '❌ Invalid');
    
    echo "<p><strong>Key:</strong> <code>" . esc_html($test_key) . "</code> - <strong>Status:</strong> " . $status . "</p>";
}

echo "</div>";

// Test 5: License Activation Test
echo "<div style='background: #e7f3ff; border-left: 4px solid #0073aa; padding: 20px; margin: 20px 0; border-radius: 4px;'>";
echo "<h3>⚡ License Activation Test</h3>";

if (isset($_POST['test_activation'])) {
    $test_key = sanitize_text_field($_POST['test_key']);
    $result = $license_manager->activate_license($test_key);
    
    echo "<p><strong>Test Key:</strong> " . esc_html($test_key) . "</p>";
    echo "<p><strong>Result:</strong> " . esc_html($result) . "</p>";
    
    // Refresh license info
    $license_info = $license_manager->get_license_info();
    echo "<p><strong>New Status:</strong> " . esc_html($license_info['status']) . "</p>";
    echo "<p><strong>Has Premium:</strong> " . ($license_info['has_premium'] ? 'Yes' : 'No') . "</p>";
} else {
    echo "<form method='post'>";
    echo "<p><strong>Test License Activation:</strong></p>";
    echo "<input type='text' name='test_key' placeholder='Enter license key to test' style='width: 300px; padding: 5px;' required>";
    echo "<input type='submit' name='test_activation' value='Test Activation' class='button button-primary' style='margin-left: 10px;'>";
    echo "</form>";
}

echo "</div>";

// Test 6: License File Creation Test
echo "<div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 4px;'>";
echo "<h3>📁 License File Creation Test</h3>";

if (isset($_POST['create_test_file'])) {
    $test_key = sanitize_text_field($_POST['file_key']);
    $file_path = SRM_PLUGIN_PATH . 'license.key';
    
    if (file_put_contents($file_path, $test_key)) {
        echo "<p>✅ Test license file created successfully!</p>";
        echo "<p><strong>File Path:</strong> " . esc_html($file_path) . "</p>";
        echo "<p><strong>Content:</strong> " . esc_html($test_key) . "</p>";
        
        // Refresh license info
        $license_info = $license_manager->get_license_info();
        echo "<p><strong>License Status:</strong> " . esc_html($license_info['status']) . "</p>";
    } else {
        echo "<p>❌ Failed to create test license file.</p>";
    }
} else {
    echo "<form method='post'>";
    echo "<p><strong>Create Test License File:</strong></p>";
    echo "<input type='text' name='file_key' placeholder='Enter license key for file' style='width: 300px; padding: 5px;' required>";
    echo "<input type='submit' name='create_test_file' value='Create Test File' class='button button-secondary' style='margin-left: 10px;'>";
    echo "</form>";
}

echo "</div>";

// Test 7: Student Limit Test
echo "<div style='background: #d4edda; border-left: 4px solid #28a745; padding: 20px; margin: 20px 0; border-radius: 4px;'>";
echo "<h3>👥 Student Limit Test</h3>";

$student_count = $license_manager->get_student_count();
$remaining_slots = $license_manager->get_remaining_student_slots();
$can_add = $license_manager->can_add_student();

echo "<p><strong>Current Student Count:</strong> " . $student_count . "</p>";
echo "<p><strong>Remaining Slots:</strong> " . $remaining_slots . "</p>";
echo "<p><strong>Can Add Student:</strong> " . ($can_add ? 'Yes' : 'No') . "</p>";

echo "</div>";

// Test 8: Cleanup
echo "<div style='background: #f8d7da; border-left: 4px solid #dc3545; padding: 20px; margin: 20px 0; border-radius: 4px;'>";
echo "<h3>🧹 Cleanup Test</h3>";

if (isset($_POST['cleanup'])) {
    $license_manager->deactivate_license();
    echo "<p>✅ License deactivated and file removed.</p>";
    
    // Refresh license info
    $license_info = $license_manager->get_license_info();
    echo "<p><strong>New Status:</strong> " . esc_html($license_info['status']) . "</p>";
} else {
    echo "<form method='post'>";
    echo "<p><strong>Cleanup (Deactivate License):</strong></p>";
    echo "<input type='submit' name='cleanup' value='Deactivate License' class='button button-secondary'>";
    echo "</form>";
}

echo "</div>";

echo "<h3>📋 Test Instructions for localhost/AACI</h3>";
echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 20px; margin: 20px 0;'>";
echo "<ol>";
echo "<li><strong>Test License File:</strong> Create a license.key file in the plugin directory</li>";
echo "<li><strong>Test Key Format:</strong> Use the 13-digit format (e.g., BJKmNpQrStU*localhost)</li>";
echo "<li><strong>Test Domain:</strong> Make sure the domain part matches 'localhost'</li>";
echo "<li><strong>Test Activation:</strong> Use the activation form above</li>";
echo "<li><strong>Test Features:</strong> Check if premium features are unlocked</li>";
echo "<li><strong>Test Cleanup:</strong> Deactivate and verify free features still work</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🔧 Valid License Key Examples for localhost</h3>";
echo "<div style='background: #e7f3ff; border-left: 4px solid #0073aa; padding: 20px; margin: 20px 0; border-radius: 4px;'>";
echo "<p><strong>Format:</strong> [13 characters].localhost</p>";
echo "<ul>";
echo "<li><code>BJKmNpQrStU*localhost</code></li>";
echo "<li><code>FGHjKlMnOpQ#localhost</code></li>";
echo "<li><code>TUVwXyZaBcD@localhost</code></li>";
echo "<li><code>NJKmNpQrStU!localhost</code></li>";
echo "</ul>";
echo "<p><em>Note: The owner key 'Bismillah^512' works on any domain.</em></p>";
echo "</div>";
?>