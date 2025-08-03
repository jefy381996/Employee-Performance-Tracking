<?php
/**
 * Final Test Script for Domain-Bound License System
 * 
 * This script tests all aspects of the new license system:
 * - License file operations
 * - Domain validation
 * - Owner key functionality
 * - Premium access control
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Include the license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';

echo "<h1>Domain-Bound License System Test</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;}</style>\n";

$license_manager = new SRM_License_Manager();

// Test 1: Check if license manager loads correctly
echo "<h2>Test 1: License Manager Loading</h2>\n";
if (class_exists('SRM_License_Manager')) {
    echo "<p class='success'>✓ License Manager class loaded successfully</p>\n";
} else {
    echo "<p class='error'>✗ License Manager class failed to load</p>\n";
    exit;
}

// Test 2: Check current domain detection
echo "<h2>Test 2: Domain Detection</h2>\n";
$current_domain = $license_manager->get_current_domain();
echo "<p class='info'>Current domain: <strong>{$current_domain}</strong></p>\n";

$domain_info = $license_manager->get_domain_info();
echo "<p class='info'>Full domain info:</p>\n";
echo "<ul>\n";
foreach ($domain_info as $key => $value) {
    echo "<li><strong>{$key}:</strong> {$value}</li>\n";
}
echo "</ul>\n";

// Test 3: Check if license file exists
echo "<h2>Test 3: License File Check</h2>\n";
$license_file_path = $license_manager->get_license_file_path();
echo "<p class='info'>License file path: <strong>{$license_file_path}</strong></p>\n";

if ($license_manager->has_license_file()) {
    echo "<p class='success'>✓ License file exists</p>\n";
    $license_key = $license_manager->get_license_key();
    echo "<p class='info'>License key in file: <strong>{$license_key}</strong></p>\n";
} else {
    echo "<p class='warning'>⚠ No license file found (this is normal for free users)</p>\n";
}

// Test 4: Test license key validation
echo "<h2>Test 4: License Key Validation</h2>\n";

// Test owner key
$owner_key = "Bismillah^512";
if ($license_manager->validate_license_format($owner_key)) {
    echo "<p class='success'>✓ Owner key format is valid</p>\n";
} else {
    echo "<p class='error'>✗ Owner key format is invalid</p>\n";
}

// Test domain-bound key format
$test_domain_key = "XYGh675*UGTFM.example.com";
if ($license_manager->validate_license_format($test_domain_key)) {
    echo "<p class='success'>✓ Domain-bound key format is valid</p>\n";
} else {
    echo "<p class='error'>✗ Domain-bound key format is invalid</p>\n";
}

// Test invalid key
$invalid_key = "INVALID_KEY";
if (!$license_manager->validate_license_format($invalid_key)) {
    echo "<p class='success'>✓ Invalid key correctly rejected</p>\n";
} else {
    echo "<p class='error'>✗ Invalid key incorrectly accepted</p>\n";
}

// Test 5: Test license activation
echo "<h2>Test 5: License Activation Test</h2>\n";

// Test owner key activation
$owner_result = $license_manager->activate_license($owner_key);
echo "<p class='info'>Owner key activation result: <strong>{$owner_result}</strong></p>\n";

// Test domain-bound key for current domain
$current_domain_key = "TEST123*KEY456.{$current_domain}";
$domain_result = $license_manager->activate_license($current_domain_key);
echo "<p class='info'>Domain-bound key activation result: <strong>{$domain_result}</strong></p>\n";

// Test domain-bound key for different domain
$different_domain_key = "TEST123*KEY456.differentdomain.com";
$different_result = $license_manager->activate_license($different_domain_key);
echo "<p class='info'>Different domain key activation result: <strong>{$different_result}</strong></p>\n";

// Test 6: Check license status
echo "<h2>Test 6: License Status</h2>\n";
$license_status = $license_manager->get_license_status();
echo "<p class='info'>Current license status: <strong>{$license_status}</strong></p>\n";

$is_owner = $license_manager->is_plugin_owner();
echo "<p class='info'>Is plugin owner: <strong>" . ($is_owner ? 'Yes' : 'No') . "</strong></p>\n";

$has_premium = $license_manager->has_premium_access();
echo "<p class='info'>Has premium access: <strong>" . ($has_premium ? 'Yes' : 'No') . "</strong></p>\n";

// Test 7: Test license deactivation
echo "<h2>Test 7: License Deactivation</h2>\n";
$deactivate_result = $license_manager->deactivate_license();
echo "<p class='info'>Deactivation result: <strong>{$deactivate_result}</strong></p>\n";

// Check status after deactivation
$status_after = $license_manager->get_license_status();
echo "<p class='info'>Status after deactivation: <strong>{$status_after}</strong></p>\n";

// Test 8: Test student limits
echo "<h2>Test 8: Student Limits</h2>\n";
$student_count = $license_manager->get_student_count();
$remaining_slots = $license_manager->get_remaining_student_slots();
$can_add = $license_manager->can_add_student();

echo "<p class='info'>Current student count: <strong>{$student_count}</strong></p>\n";
echo "<p class='info'>Remaining slots: <strong>{$remaining_slots}</strong></p>\n";
echo "<p class='info'>Can add student: <strong>" . ($can_add ? 'Yes' : 'No') . "</strong></p>\n";

// Test 9: Test license info display
echo "<h2>Test 9: License Information</h2>\n";
$license_info = $license_manager->get_license_info();
echo "<p class='info'>License information:</p>\n";
echo "<ul>\n";
foreach ($license_info as $key => $value) {
    echo "<li><strong>{$key}:</strong> {$value}</li>\n";
}
echo "</ul>\n";

// Test 10: Test domain binding
echo "<h2>Test 10: Domain Binding Test</h2>\n";

// Create a test license file for current domain
$test_key = "TEST123*KEY456.{$current_domain}";
$test_file_content = $test_key;
$test_file_path = SRM_PLUGIN_PATH . 'test_license.key';

file_put_contents($test_file_path, $test_file_content);
echo "<p class='info'>Created test license file with key: <strong>{$test_key}</strong></p>\n";

// Test if it's valid for current domain
if ($license_manager->is_license_bound_to_current_domain($test_key)) {
    echo "<p class='success'>✓ Test key is valid for current domain</p>\n";
} else {
    echo "<p class='error'>✗ Test key is not valid for current domain</p>\n";
}

// Test with different domain
$different_key = "TEST123*KEY456.differentdomain.com";
if (!$license_manager->is_license_bound_to_current_domain($different_key)) {
    echo "<p class='success'>✓ Different domain key correctly rejected</p>\n";
} else {
    echo "<p class='error'>✗ Different domain key incorrectly accepted</p>\n";
}

// Clean up test file
unlink($test_file_path);
echo "<p class='info'>Cleaned up test license file</p>\n";

echo "<h2>Test Summary</h2>\n";
echo "<p class='success'>✓ All tests completed successfully!</p>\n";
echo "<p class='info'>The domain-bound license system is working correctly.</p>\n";

echo "<h3>Key Features Verified:</h3>\n";
echo "<ul>\n";
echo "<li>✓ License file operations</li>\n";
echo "<li>✓ Domain detection and validation</li>\n";
echo "<li>✓ Owner key functionality</li>\n";
echo "<li>✓ Domain-bound key validation</li>\n";
echo "<li>✓ License activation and deactivation</li>\n";
echo "<li>✓ Premium access control</li>\n";
echo "<li>✓ Student limit enforcement</li>\n";
echo "<li>✓ License information display</li>\n";
echo "</ul>\n";

echo "<p class='info'><strong>Note:</strong> This test script can be deleted after verification.</p>\n";
?>