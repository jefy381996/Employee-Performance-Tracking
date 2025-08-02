<?php
/**
 * Enhanced Activation Script for Student Result Management
 * Run this to test the enhanced licensing system
 */

// Load WordPress
require_once('../../../wp-config.php');

// Security check
if (!current_user_can('manage_options')) {
    die('Access denied. You need administrator privileges to run this script.');
}

echo "<h1>Enhanced Student Result Management - Activation Test</h1>";

// Test license manager
if (file_exists('includes/admin/license-manager.php')) {
    require_once('includes/admin/license-manager.php');
    $license_manager = new SRM_License_Manager();
    
    echo "<h2>✅ License Manager Test</h2>";
    echo "<p>License Manager loaded successfully!</p>";
    
    // Test if current user is plugin owner
    $is_owner = $license_manager->is_plugin_owner();
    echo "<p><strong>Plugin Owner Status:</strong> " . ($is_owner ? '✅ YES - You are the plugin owner!' : '❌ NO') . "</p>";
    
    // Test premium access
    $has_premium = $license_manager->has_premium_access();
    echo "<p><strong>Premium Access:</strong> " . ($has_premium ? '✅ YES - Full premium access!' : '❌ NO - Limited access') . "</p>";
    
    // Test license status
    $license_status = $license_manager->get_license_status();
    echo "<p><strong>License Status:</strong> " . ucfirst($license_status) . "</p>";
    
} else {
    echo "<h2>❌ License Manager Test</h2>";
    echo "<p>License Manager file not found!</p>";
}

// Test feature control
if (file_exists('includes/admin/feature-control.php')) {
    require_once('includes/admin/feature-control.php');
    echo "<h2>✅ Feature Control Test</h2>";
    echo "<p>Feature Control loaded successfully!</p>";
    
    // Test feature access
    $feature_control = new SRM_Feature_Control();
    $pdf_access = $feature_control->has_feature_access('pdf_generation');
    echo "<p><strong>PDF Generation Access:</strong> " . ($pdf_access ? '✅ YES' : '❌ NO') . "</p>";
    
    $csv_access = $feature_control->has_feature_access('csv_import_export');
    echo "<p><strong>CSV Import/Export Access:</strong> " . ($csv_access ? '✅ YES' : '❌ NO') . "</p>";
    
} else {
    echo "<h2>❌ Feature Control Test</h2>";
    echo "<p>Feature Control file not found!</p>";
}

// Test payment processor
if (file_exists('includes/admin/payment-processor.php')) {
    require_once('includes/admin/payment-processor.php');
    echo "<h2>✅ Payment Processor Test</h2>";
    echo "<p>Payment Processor loaded successfully!</p>";
    
    $payment_processor = new SRM_Payment_Processor();
    $methods = $payment_processor->get_available_payment_methods();
    echo "<p><strong>Available Payment Methods:</strong></p>";
    echo "<ul>";
    foreach ($methods as $method => $details) {
        echo "<li>✅ " . $details['name'] . "</li>";
    }
    echo "</ul>";
    
} else {
    echo "<h2>❌ Payment Processor Test</h2>";
    echo "<p>Payment Processor file not found!</p>";
}

// Test database tables
global $wpdb;
echo "<h2>✅ Database Tables Test</h2>";

$tables = array(
    'students' => $wpdb->prefix . 'srm_students',
    'results' => $wpdb->prefix . 'srm_results',
    'settings' => $wpdb->prefix . 'srm_settings',
    'payments' => $wpdb->prefix . 'srm_payments'
);

foreach ($tables as $name => $table) {
    $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
    if ($exists) {
        echo "<p>✅ {$name} table exists</p>";
    } else {
        echo "<p>❌ {$name} table missing</p>";
    }
}

echo "<h2>🎉 Activation Complete!</h2>";
echo "<p>If all tests passed, your enhanced licensing system is ready!</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Go to WordPress Admin → Student Results → Premium Features</li>";
echo "<li>Configure payment gateways in Settings (if desired)</li>";
echo "<li>Test premium features as the plugin owner</li>";
echo "<li>Start offering premium licenses to users</li>";
echo "</ol>";

echo "<p><strong>Plugin Owner Benefits:</strong></p>";
echo "<ul>";
echo "<li>✅ Full access to all premium features</li>";
echo "<li>✅ Can configure payment gateways</li>";
echo "<li>✅ Can manage licenses for other users</li>";
echo "<li>✅ Can earn revenue from license sales</li>";
echo "</ul>";
?>