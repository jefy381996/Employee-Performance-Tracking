<?php
/**
 * Test Results Fix
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🔧 Test Results Fix</h2>";

// Include necessary files
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';

global $wpdb;

echo "<h3>✅ 1. License Manager Test</h3>";
try {
    $license_manager = new SRM_License_Manager();
    echo "<div class='notice notice-success'>";
    echo "<p><strong>✅ License Manager loaded successfully</strong></p>";
    echo "</div>";
    
    echo "<ul>";
    echo "<li><strong>License Key:</strong> " . ($license_manager->get_license_key() ?: 'None') . "</li>";
    echo "<li><strong>License Status:</strong> " . $license_manager->get_license_status() . "</li>";
    echo "<li><strong>Has Premium Access:</strong> " . ($license_manager->has_premium_access() ? 'Yes' : 'No') . "</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div class='notice notice-error'>";
    echo "<p><strong>❌ License Manager Error:</strong> " . esc_html($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<h3>✅ 2. Database Tables Check</h3>";
$tables_to_check = array(
    'srm_students' => $wpdb->prefix . 'srm_students',
    'srm_results' => $wpdb->prefix . 'srm_results'
);

foreach ($tables_to_check as $table_name => $table) {
    $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        echo "<div class='notice notice-success'>";
        echo "<p><strong>✅ $table_name table exists</strong> ($count records)</p>";
        echo "</div>";
    } else {
        echo "<div class='notice notice-error'>";
        echo "<p><strong>❌ $table_name table missing!</strong></p>";
        echo "</div>";
    }
}

echo "<h3>✅ 3. Test Result Insertion</h3>";

// Get first student for testing
$first_student = $wpdb->get_row("SELECT id, first_name, last_name FROM {$wpdb->prefix}srm_students LIMIT 1");

if ($first_student) {
    echo "<p><strong>Test Student:</strong> " . esc_html($first_student->first_name . ' ' . $first_student->last_name) . " (ID: {$first_student->id})</p>";
    
    // Test data
    $test_result_data = array(
        'student_id' => $first_student->id,
        'exam_name' => 'Test Exam',
        'exam_date' => date('Y-m-d'),
        'total_marks' => 100,
        'obtained_marks' => 85,
        'percentage' => 85.00,
        'grade' => 'A',
        'status' => 'pass',
        'subjects' => json_encode(array()),
        'certificate_pdf' => ''
    );
    
    echo "<h4>Attempting to insert test result:</h4>";
    echo "<pre>" . print_r($test_result_data, true) . "</pre>";
    
    $results_table = $wpdb->prefix . 'srm_results';
    $result = $wpdb->insert($results_table, $test_result_data);
    
    if ($result !== false) {
        echo "<div class='notice notice-success'>";
        echo "<p><strong>✅ Test result insert successful!</strong> Insert ID: " . $wpdb->insert_id . "</p>";
        echo "</div>";
        
        // Clean up test data
        $wpdb->delete($results_table, array('id' => $wpdb->insert_id));
        echo "<p><em>Test record deleted.</em></p>";
    } else {
        echo "<div class='notice notice-error'>";
        echo "<p><strong>❌ Test result insert failed!</strong></p>";
        echo "<p><strong>Error:</strong> " . esc_html($wpdb->last_error) . "</p>";
        echo "</div>";
    }
} else {
    echo "<div class='notice notice-warning'>";
    echo "<p><strong>⚠️ No students available for testing!</strong></p>";
    echo "<p>Please add at least one student before testing result insertion.</p>";
    echo "</div>";
}

echo "<h3>✅ 4. File Include Test</h3>";

$files_to_check = array(
    'results.php' => SRM_PLUGIN_PATH . 'includes/admin/results.php',
    'students.php' => SRM_PLUGIN_PATH . 'includes/admin/students.php',
    'settings.php' => SRM_PLUGIN_PATH . 'includes/admin/settings.php',
    'enhanced-settings.php' => SRM_PLUGIN_PATH . 'includes/admin/enhanced-settings.php',
    'enhanced-premium.php' => SRM_PLUGIN_PATH . 'includes/admin/enhanced-premium.php'
);

foreach ($files_to_check as $file_name => $file_path) {
    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);
        if (strpos($content, 'require_once SRM_PLUGIN_PATH . \'includes/admin/license-manager.php\';') !== false) {
            echo "<div class='notice notice-success'>";
            echo "<p><strong>✅ $file_name has proper license manager include</strong></p>";
            echo "</div>";
        } else {
            echo "<div class='notice notice-error'>";
            echo "<p><strong>❌ $file_name missing license manager include!</strong></p>";
            echo "</div>";
        }
    } else {
        echo "<div class='notice notice-error'>";
        echo "<p><strong>❌ $file_name file not found!</strong></p>";
        echo "</div>";
    }
}

echo "<h3>✅ 5. Manual Test Steps</h3>";
echo "<ol>";
echo "<li><strong>Add a Student:</strong> Go to Students page and add a student if none exist</li>";
echo "<li><strong>Add a Result:</strong> Go to Results page and try to add a result</li>";
echo "<li><strong>Check for Errors:</strong> If any error occurs, note the exact message</li>";
echo "<li><strong>Test All Fields:</strong> Fill all required fields and submit</li>";
echo "<li><strong>Verify Success:</strong> Check if result is added successfully</li>";
echo "</ol>";

echo "<h3>✅ 6. Expected Behavior</h3>";
echo "<ul>";
echo "<li>✅ <strong>No PHP Errors:</strong> Should not show 'Class SRM_License_Manager not found'</li>";
echo "<li>✅ <strong>Form Submission:</strong> Should process form without errors</li>";
echo "<li>✅ <strong>Database Insert:</strong> Should insert result into database</li>";
echo "<li>✅ <strong>Success Message:</strong> Should show 'Result added successfully!'</li>";
echo "<li>✅ <strong>Redirect:</strong> Should redirect to results list after success</li>";
echo "</ul>";

echo "<h3>✅ 7. Common Issues Fixed</h3>";
echo "<ul>";
echo "<li>✅ <strong>Missing Include:</strong> Added license manager include to results.php</li>";
echo "<li>✅ <strong>Missing Include:</strong> Added license manager include to students.php</li>";
echo "<li>✅ <strong>Missing Include:</strong> Added license manager include to settings.php</li>";
echo "<li>✅ <strong>Class Not Found:</strong> License manager class should now be available</li>";
echo "<li>✅ <strong>Premium Features:</strong> Certificate upload should work for premium users</li>";
echo "<li>✅ <strong>Student Limits:</strong> Student count limits should work for free users</li>";
echo "</ul>";

echo "<h3>🔗 Quick Test Links</h3>";
echo "<ul>";
echo "<li><a href='?page=srm-students'>Students Page</a></li>";
echo "<li><a href='?page=srm-results'>Results Page</a></li>";
echo "<li><a href='?page=srm-settings'>Settings Page</a></li>";
echo "<li><a href='?page=srm-premium'>Premium Features Page</a></li>";
echo "</ul>";

echo "<h3>📱 Contact Information</h3>";
echo "<p><strong>WhatsApp:</strong> <a href='https://wa.me/923083430923' target='_blank'>+923083430923</a></p>";
echo "<p><strong>Email:</strong> <a href='mailto:jaffar381996152@gmail.com'>jaffar381996152@gmail.com</a></p>";

echo "<h3>🎯 Summary</h3>";
echo "<p><strong>The results error should now be fixed:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>License Manager:</strong> Properly included in all admin files</li>";
echo "<li>✅ <strong>No Class Errors:</strong> SRM_License_Manager class is now available</li>";
echo "<li>✅ <strong>Form Processing:</strong> Results form should work without errors</li>";
echo "<li>✅ <strong>Database Operations:</strong> Insert/update operations should work</li>";
echo "<li>✅ <strong>Premium Features:</strong> Certificate upload should work for licensed users</li>";
echo "<li>✅ <strong>Student Limits:</strong> 20 student limit should work for free users</li>";
echo "</ul>";

echo "<h3>🧪 To Test Results Fix</h3>";
echo "<ol>";
echo "<li><strong>Go to Results Page:</strong> Navigate to the results admin page</li>";
echo "<li><strong>Click Add New:</strong> Click the 'Add New' button</li>";
echo "<li><strong>Fill Form:</strong> Fill in all required fields</li>";
echo "<li><strong>Submit Form:</strong> Click 'Add Result' button</li>";
echo "<li><strong>Check Success:</strong> Should see success message and redirect to list</li>";
echo "<li><strong>Verify Data:</strong> Check that result appears in the results list</li>";
echo "</ol>";

echo "<h3>🔧 Files Modified</h3>";
echo "<ul>";
echo "<li>✅ <strong>results.php:</strong> Added license manager include</li>";
echo "<li>✅ <strong>students.php:</strong> Added license manager include</li>";
echo "<li>✅ <strong>settings.php:</strong> Added license manager include</li>";
echo "<li>✅ <strong>enhanced-settings.php:</strong> Already had proper include</li>";
echo "<li>✅ <strong>enhanced-premium.php:</strong> Already had proper include</li>";
echo "</ul>";
?>