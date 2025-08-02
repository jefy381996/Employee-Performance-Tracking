<?php
/**
 * Test file to verify all changes are properly implemented
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>Student Result Management - Change Verification</h2>";

// Test 1: Check if payment processor is removed
echo "<h3>Test 1: Payment Processor Removal</h3>";
$payment_file = SRM_PLUGIN_PATH . 'includes/admin/payment-processor.php';
if (file_exists($payment_file)) {
    echo "❌ Payment processor file still exists<br>";
} else {
    echo "✅ Payment processor file removed<br>";
}

// Test 2: Check license manager updates
echo "<h3>Test 2: License Manager Updates</h3>";
$license_file = SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
if (file_exists($license_file)) {
    $content = file_get_contents($license_file);
    if (strpos($content, 'owner_key = \'Bismillah^512\'') !== false) {
        echo "✅ Owner key is set to 'Bismillah^512'<br>";
    } else {
        echo "❌ Owner key not found<br>";
    }
    
    if (strpos($content, 'Check if current user has the owner key activated') !== false) {
        echo "✅ Owner key activation check implemented<br>";
    } else {
        echo "❌ Owner key activation check not found<br>";
    }
} else {
    echo "❌ License manager file not found<br>";
}

// Test 3: Check enhanced premium page updates
echo "<h3>Test 3: Enhanced Premium Page Updates</h3>";
$premium_file = SRM_PLUGIN_PATH . 'includes/admin/enhanced-premium.php';
if (file_exists($premium_file)) {
    $content = file_get_contents($premium_file);
    if (strpos($content, 'Plugin Owner Key:') !== false) {
        echo "✅ Plugin owner key display implemented<br>";
    } else {
        echo "❌ Plugin owner key display not found<br>";
    }
    
    if (strpos($content, 'Bismillah^512') !== false) {
        echo "✅ Owner key shown in interface<br>";
    } else {
        echo "❌ Owner key not shown in interface<br>";
    }
} else {
    echo "❌ Enhanced premium file not found<br>";
}

// Test 4: Check license key manager
echo "<h3>Test 4: License Key Manager</h3>";
$key_manager_file = SRM_PLUGIN_PATH . 'includes/admin/license-key-manager.php';
if (file_exists($key_manager_file)) {
    echo "✅ License key manager file exists<br>";
} else {
    echo "❌ License key manager file not found<br>";
}

// Test 5: Check main plugin file updates
echo "<h3>Test 5: Main Plugin File Updates</h3>";
$main_file = SRM_PLUGIN_PATH . 'student-result-management.php';
if (file_exists($main_file)) {
    $content = file_get_contents($main_file);
    if (strpos($content, 'License Keys') !== false) {
        echo "✅ License Keys menu item added<br>";
    } else {
        echo "❌ License Keys menu item not found<br>";
    }
    
    if (strpos($content, 'ajax_add_valid_key') !== false) {
        echo "✅ AJAX handlers for license key management added<br>";
    } else {
        echo "❌ AJAX handlers not found<br>";
    }
} else {
    echo "❌ Main plugin file not found<br>";
}

echo "<h3>Summary</h3>";
echo "If all tests show ✅, then all changes are properly implemented.<br>";
echo "If you're still seeing the old version, try:<br>";
echo "1. Refresh your WordPress admin page<br>";
echo "2. Clear any caching plugins<br>";
echo "3. Deactivate and reactivate the plugin<br>";
echo "4. Check if you're looking at the correct WordPress installation<br>";
?>