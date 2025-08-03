<?php
/**
 * Test Script for Domain-Bound License System
 * 
 * This script tests the complete license system to ensure it's working correctly.
 * Run this file in your browser to verify all license functionality.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Include WordPress functions if not already loaded
if (!function_exists('wp_mail')) {
    require_once(ABSPATH . 'wp-includes/pluggable.php');
}

// Include the license manager
require_once 'includes/admin/license-manager.php';

echo "<h1>🔍 Domain-Bound License System Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    pre { background: #fff; padding: 10px; border: 1px solid #ddd; }
</style>";

// Initialize license manager
$license_manager = new SRM_License_Manager();

echo "<div class='test-section'>";
echo "<h2>📋 System Information</h2>";

// Test 1: Check current domain
$current_domain = $license_manager->get_current_domain();
echo "<p><strong>Current Domain:</strong> <span class='info'>$current_domain</span></p>";

// Test 2: Check license file status
$has_license_file = $license_manager->has_license_file();
$license_file_path = $license_manager->get_license_file_path();
echo "<p><strong>License File Exists:</strong> " . ($has_license_file ? "<span class='success'>Yes</span>" : "<span class='error'>No</span>") . "</p>";
echo "<p><strong>License File Path:</strong> <code>$license_file_path</code></p>";

// Test 3: Check current license status
$license_status = $license_manager->get_license_status();
$license_key = $license_manager->get_license_key();
echo "<p><strong>Current License Status:</strong> <span class='info'>$license_status</span></p>";
echo "<p><strong>Current License Key:</strong> <code>" . ($license_key ?: 'None') . "</code></p>";

echo "</div>";

echo "<div class='test-section'>";
echo "<h2>🔑 License Key Validation Tests</h2>";

// Test 4: Owner key validation
$owner_key = "Bismillah^512";
$is_owner_valid = $license_manager->validate_license_format($owner_key);
echo "<p><strong>Owner Key Validation:</strong> " . ($is_owner_valid ? "<span class='success'>Valid</span>" : "<span class='error'>Invalid</span>") . "</p>";

// Test 5: Domain-bound key validation
$test_domain_key = "XYGh675*UGTFM." . $current_domain;
$is_domain_key_valid = $license_manager->validate_license_format($test_domain_key);
echo "<p><strong>Domain-Bound Key Validation:</strong> " . ($is_domain_key_valid ? "<span class='success'>Valid</span>" : "<span class='error'>Invalid</span>") . "</p>";

// Test 6: Invalid key validation
$invalid_key = "INVALID123";
$is_invalid_key_valid = $license_manager->validate_license_format($invalid_key);
echo "<p><strong>Invalid Key Validation:</strong> " . ($is_invalid_key_valid ? "<span class='error'>Should be Invalid</span>" : "<span class='success'>Correctly Invalid</span>") . "</p>";

echo "</div>";

echo "<div class='test-section'>";
echo "<h2>🌐 Domain Validation Tests</h2>";

// Test 7: Test domain binding
$test_key_for_current_domain = "ABC123#XYZ789." . $current_domain;
$is_bound_to_current = $license_manager->is_valid_domain_license($test_key_for_current_domain);
echo "<p><strong>Key Bound to Current Domain:</strong> " . ($is_bound_to_current ? "<span class='success'>Yes</span>" : "<span class='error'>No</span>") . "</p>";

// Test 8: Test domain mismatch
$test_key_for_wrong_domain = "ABC123#XYZ789.wrongdomain.com";
$is_bound_to_wrong = $license_manager->is_valid_domain_license($test_key_for_wrong_domain);
echo "<p><strong>Key Bound to Wrong Domain:</strong> " . ($is_bound_to_wrong ? "<span class='error'>Should be No</span>" : "<span class='success'>Correctly No</span>") . "</p>";

echo "</div>";

echo "<div class='test-section'>";
echo "<h2>👤 Access Level Tests</h2>";

// Test 9: Check owner access
$is_owner = $license_manager->is_plugin_owner();
echo "<p><strong>Is Plugin Owner:</strong> " . ($is_owner ? "<span class='success'>Yes</span>" : "<span class='info'>No</span>") . "</p>";

// Test 10: Check premium access
$has_premium = $license_manager->has_premium_access();
echo "<p><strong>Has Premium Access:</strong> " . ($has_premium ? "<span class='success'>Yes</span>" : "<span class='info'>No</span>") . "</p>";

// Test 11: Check student limits
$student_count = $license_manager->get_student_count();
$remaining_slots = $license_manager->get_remaining_student_slots();
$can_add_student = $license_manager->can_add_student();
echo "<p><strong>Current Students:</strong> $student_count</p>";
echo "<p><strong>Remaining Slots:</strong> $remaining_slots</p>";
echo "<p><strong>Can Add Student:</strong> " . ($can_add_student ? "<span class='success'>Yes</span>" : "<span class='warning'>No</span>") . "</p>";

echo "</div>";

echo "<div class='test-section'>";
echo "<h2>📁 File System Tests</h2>";

// Test 12: Check license file operations
$license_info = $license_manager->get_license_info();
echo "<p><strong>License Info:</strong></p>";
echo "<pre>" . print_r($license_info, true) . "</pre>";

// Test 13: Test domain info
$domain_info = $license_manager->get_domain_info();
echo "<p><strong>Domain Info:</strong></p>";
echo "<pre>" . print_r($domain_info, true) . "</pre>";

echo "</div>";

echo "<div class='test-section'>";
echo "<h2>🔄 License Activation Tests</h2>";

// Test 14: Test owner key activation
echo "<p><strong>Testing Owner Key Activation:</strong></p>";
$owner_activation_result = $license_manager->activate_license($owner_key);
echo "<p>Owner Key Activation Result: <span class='info'>$owner_activation_result</span></p>";

// Test 15: Test domain key activation
echo "<p><strong>Testing Domain Key Activation:</strong></p>";
$domain_activation_result = $license_manager->activate_license($test_key_for_current_domain);
echo "<p>Domain Key Activation Result: <span class='info'>$domain_activation_result</span></p>";

// Test 16: Test wrong domain activation
echo "<p><strong>Testing Wrong Domain Activation:</strong></p>";
$wrong_domain_result = $license_manager->activate_license($test_key_for_wrong_domain);
echo "<p>Wrong Domain Activation Result: <span class='info'>$wrong_domain_result</span></p>";

echo "</div>";

echo "<div class='test-section'>";
echo "<h2>📧 License Request System Test</h2>";

// Test 17: Test email functionality
$test_email_data = array(
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '+1234567890',
    'domain' => $current_domain
);

echo "<p><strong>Test Email Data:</strong></p>";
echo "<pre>" . print_r($test_email_data, true) . "</pre>";

// Note: We won't actually send the email in this test
echo "<p><span class='info'>Email functionality would send request to: jaffar381996152@gmail.com</span></p>";

echo "</div>";

echo "<div class='test-section'>";
echo "<h2>✅ Summary</h2>";

$all_tests_passed = true;
if (!$license_manager->validate_license_format($owner_key)) {
    echo "<p class='error'>❌ Owner key validation failed</p>";
    $all_tests_passed = false;
}
if (!$license_manager->validate_license_format($test_domain_key)) {
    echo "<p class='error'>❌ Domain key validation failed</p>";
    $all_tests_passed = false;
}
if ($license_manager->validate_license_format($invalid_key)) {
    echo "<p class='error'>❌ Invalid key validation failed</p>";
    $all_tests_passed = false;
}
if (!$license_manager->is_valid_domain_license($test_key_for_current_domain)) {
    echo "<p class='error'>❌ Domain binding validation failed</p>";
    $all_tests_passed = false;
}
if ($license_manager->is_valid_domain_license($test_key_for_wrong_domain)) {
    echo "<p class='error'>❌ Wrong domain validation failed</p>";
    $all_tests_passed = false;
}

if ($all_tests_passed) {
    echo "<p class='success'>🎉 All license system tests passed!</p>";
} else {
    echo "<p class='error'>⚠️ Some tests failed. Please check the system.</p>";
}

echo "<p><strong>Current Status:</strong> <span class='info'>$license_status</span></p>";
echo "<p><strong>Premium Access:</strong> " . ($license_manager->has_premium_access() ? "<span class='success'>Active</span>" : "<span class='info'>Inactive</span>") . "</p>";

echo "</div>";

echo "<div class='test-section'>";
echo "<h2>📖 Quick Reference</h2>";
echo "<p><strong>Owner Key:</strong> <code>Bismillah^512</code></p>";
echo "<p><strong>Domain Key Format:</strong> <code>[RANDOM][SPECIAL][RANDOM].[DOMAIN]</code></p>";
echo "<p><strong>Example:</strong> <code>XYGh675*UGTFM.example.com</code></p>";
echo "<p><strong>License File Location:</strong> <code>student-result-management/license.key</code></p>";
echo "<p><strong>Contact:</strong> +923083430923 or jaffar381996152@gmail.com</p>";
echo "</div>";

echo "<p><em>Test completed at: " . date('Y-m-d H:i:s') . "</em></p>";
?>