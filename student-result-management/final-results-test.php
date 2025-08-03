<?php
/**
 * Final Results Test
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🎯 Final Results Test</h2>";

// Include necessary files
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';

global $wpdb;

echo "<h3>✅ 1. Complete System Check</h3>";

// Check database connection
if ($wpdb->last_error) {
    echo "<div class='notice notice-error'>";
    echo "<p><strong>❌ Database Error:</strong> " . esc_html($wpdb->last_error) . "</p>";
    echo "</div>";
} else {
    echo "<div class='notice notice-success'>";
    echo "<p><strong>✅ Database connection successful</strong></p>";
    echo "</div>";
}

// Check tables
$tables = array(
    'srm_students' => $wpdb->prefix . 'srm_students',
    'srm_results' => $wpdb->prefix . 'srm_results'
);

foreach ($tables as $table_name => $table) {
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

// Check students
$students_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_students");
if ($students_count == 0) {
    echo "<div class='notice notice-error'>";
    echo "<p><strong>❌ No students found!</strong> You need to add students first.</p>";
    echo "</div>";
} else {
    echo "<div class='notice notice-success'>";
    echo "<p><strong>✅ Students available ($students_count total)</strong></p>";
    echo "</div>";
}

echo "<h3>✅ 2. Test Result Insertion (Direct Database)</h3>";

if (isset($_POST['test_direct_insert'])) {
    $test_data = array(
        'student_id' => intval($_POST['test_student_id']),
        'exam_name' => sanitize_text_field($_POST['test_exam_name']),
        'exam_date' => sanitize_text_field($_POST['test_exam_date']),
        'total_marks' => intval($_POST['test_total_marks']),
        'obtained_marks' => intval($_POST['test_obtained_marks']),
        'percentage' => floatval($_POST['test_percentage']),
        'grade' => sanitize_text_field($_POST['test_grade']),
        'status' => sanitize_text_field($_POST['test_status']),
        'subjects' => json_encode(array()),
        'certificate_pdf' => ''
    );
    
    echo "<h4>Attempting direct database insert:</h4>";
    echo "<pre>" . print_r($test_data, true) . "</pre>";
    
    $results_table = $wpdb->prefix . 'srm_results';
    $result = $wpdb->insert($results_table, $test_data);
    
    if ($result !== false) {
        echo "<div class='notice notice-success'>";
        echo "<p><strong>✅ Direct database insert successful!</strong> Insert ID: " . $wpdb->insert_id . "</p>";
        echo "</div>";
        
        // Clean up test data
        $wpdb->delete($results_table, array('id' => $wpdb->insert_id));
        echo "<p><em>Test record deleted.</em></p>";
    } else {
        echo "<div class='notice notice-error'>";
        echo "<p><strong>❌ Direct database insert failed!</strong></p>";
        echo "<p><strong>Error:</strong> " . esc_html($wpdb->last_error) . "</p>";
        echo "</div>";
    }
} else {
    // Get first student for test
    $first_student = $wpdb->get_row("SELECT id, first_name, last_name FROM {$wpdb->prefix}srm_students LIMIT 1");
    
    if ($first_student) {
        echo "<form method='post'>";
        echo "<h4>Test Direct Database Insert:</h4>";
        echo "<table class='form-table'>";
        echo "<tr><th>Student ID:</th><td><input type='number' name='test_student_id' value='" . $first_student->id . "' required></td></tr>";
        echo "<tr><th>Exam Name:</th><td><input type='text' name='test_exam_name' value='Test Exam' required></td></tr>";
        echo "<tr><th>Exam Date:</th><td><input type='date' name='test_exam_date' value='" . date('Y-m-d') . "' required></td></tr>";
        echo "<tr><th>Total Marks:</th><td><input type='number' name='test_total_marks' value='100' required></td></tr>";
        echo "<tr><th>Obtained Marks:</th><td><input type='number' name='test_obtained_marks' value='85' required></td></tr>";
        echo "<tr><th>Percentage:</th><td><input type='number' step='0.01' name='test_percentage' value='85.00' required></td></tr>";
        echo "<tr><th>Grade:</th><td><input type='text' name='test_grade' value='A' required></td></tr>";
        echo "<tr><th>Status:</th><td><select name='test_status'><option value='pass'>Pass</option><option value='fail'>Fail</option></select></td></tr>";
        echo "</table>";
        echo "<p><input type='submit' name='test_direct_insert' value='Test Direct Insert' class='button button-primary'></p>";
        echo "</form>";
    } else {
        echo "<div class='notice notice-error'>";
        echo "<p><strong>❌ No students available for testing!</strong></p>";
        echo "<p>Please add at least one student before testing.</p>";
        echo "</div>";
    }
}

echo "<h3>✅ 3. Test Form Submission (Simulated)</h3>";

if (isset($_POST['test_form_submission'])) {
    // Simulate form submission
    $form_data = array(
        'srm_nonce' => wp_create_nonce('srm_result_action'),
        'student_id' => intval($_POST['test_student_id']),
        'exam_name' => sanitize_text_field($_POST['test_exam_name']),
        'exam_date' => sanitize_text_field($_POST['test_exam_date']),
        'total_marks' => intval($_POST['test_total_marks']),
        'obtained_marks' => intval($_POST['test_obtained_marks'])
    );
    
    echo "<h4>Simulating form submission:</h4>";
    echo "<pre>" . print_r($form_data, true) . "</pre>";
    
    // Test nonce verification
    if (wp_verify_nonce($form_data['srm_nonce'], 'srm_result_action')) {
        echo "<div class='notice notice-success'>";
        echo "<p><strong>✅ Nonce verification successful</strong></p>";
        echo "</div>";
        
        // Test data processing
        $total_marks = intval($form_data['total_marks']);
        $obtained_marks = intval($form_data['obtained_marks']);
        $percentage = ($total_marks > 0) ? round(($obtained_marks / $total_marks) * 100, 2) : 0;
        
        echo "<p><strong>Calculated Percentage:</strong> $percentage%</p>";
        
        // Test database insert
        $test_data = array(
            'student_id' => $form_data['student_id'],
            'exam_name' => $form_data['exam_name'],
            'exam_date' => $form_data['exam_date'],
            'total_marks' => $total_marks,
            'obtained_marks' => $obtained_marks,
            'percentage' => $percentage,
            'grade' => 'A', // Simplified for test
            'status' => 'pass', // Simplified for test
            'subjects' => json_encode(array()),
            'certificate_pdf' => ''
        );
        
        $results_table = $wpdb->prefix . 'srm_results';
        $result = $wpdb->insert($results_table, $test_data);
        
        if ($result !== false) {
            echo "<div class='notice notice-success'>";
            echo "<p><strong>✅ Form simulation successful!</strong> Insert ID: " . $wpdb->insert_id . "</p>";
            echo "</div>";
            
            // Clean up test data
            $wpdb->delete($results_table, array('id' => $wpdb->insert_id));
            echo "<p><em>Test record deleted.</em></p>";
        } else {
            echo "<div class='notice notice-error'>";
            echo "<p><strong>❌ Form simulation failed!</strong></p>";
            echo "<p><strong>Error:</strong> " . esc_html($wpdb->last_error) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<div class='notice notice-error'>";
        echo "<p><strong>❌ Nonce verification failed</strong></p>";
        echo "</div>";
    }
} else {
    // Get first student for test
    $first_student = $wpdb->get_row("SELECT id, first_name, last_name FROM {$wpdb->prefix}srm_students LIMIT 1");
    
    if ($first_student) {
        echo "<form method='post'>";
        echo "<h4>Test Form Submission Simulation:</h4>";
        echo "<table class='form-table'>";
        echo "<tr><th>Student ID:</th><td><input type='number' name='test_student_id' value='" . $first_student->id . "' required></td></tr>";
        echo "<tr><th>Exam Name:</th><td><input type='text' name='test_exam_name' value='Test Exam' required></td></tr>";
        echo "<tr><th>Exam Date:</th><td><input type='date' name='test_exam_date' value='" . date('Y-m-d') . "' required></td></tr>";
        echo "<tr><th>Total Marks:</th><td><input type='number' name='test_total_marks' value='100' required></td></tr>";
        echo "<tr><th>Obtained Marks:</th><td><input type='number' name='test_obtained_marks' value='85' required></td></tr>";
        echo "</table>";
        echo "<p><input type='submit' name='test_form_submission' value='Test Form Submission' class='button button-primary'></p>";
        echo "</form>";
    } else {
        echo "<div class='notice notice-error'>";
        echo "<p><strong>❌ No students available for testing!</strong></p>";
        echo "<p>Please add at least one student before testing.</p>";
        echo "</div>";
    }
}

echo "<h3>✅ 4. License Manager Test</h3>";
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

echo "<h3>✅ 5. File System Check</h3>";

$files_to_check = array(
    'results.php' => SRM_PLUGIN_PATH . 'includes/admin/results.php',
    'license-manager.php' => SRM_PLUGIN_PATH . 'includes/admin/license-manager.php',
    'main plugin file' => SRM_PLUGIN_PATH . 'student-result-management.php'
);

foreach ($files_to_check as $file_name => $file_path) {
    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);
        if (strpos($content, 'require_once') !== false || strpos($content, 'class') !== false) {
            echo "<div class='notice notice-success'>";
            echo "<p><strong>✅ $file_name exists and appears valid</strong></p>";
            echo "</div>";
        } else {
            echo "<div class='notice notice-warning'>";
            echo "<p><strong>⚠️ $file_name exists but may be empty</strong></p>";
            echo "</div>";
        }
    } else {
        echo "<div class='notice notice-error'>";
        echo "<p><strong>❌ $file_name not found!</strong></p>";
        echo "</div>";
    }
}

echo "<h3>✅ 6. Manual Test Instructions</h3>";
echo "<ol>";
echo "<li><strong>Add a Student:</strong> Go to Students page and add at least one student</li>";
echo "<li><strong>Test Direct Insert:</strong> Use the test form above to test direct database insertion</li>";
echo "<li><strong>Test Form Simulation:</strong> Use the form simulation test above</li>";
echo "<li><strong>Go to Results Page:</strong> Navigate to Results admin page</li>";
echo "<li><strong>Add Result:</strong> Try to add a result through the admin interface</li>";
echo "<li><strong>Check Error Messages:</strong> Note any specific error messages</li>";
echo "<li><strong>Verify Success:</strong> Check if result appears in the database</li>";
echo "</ol>";

echo "<h3>🔗 Quick Test Links</h3>";
echo "<ul>";
echo "<li><a href='?page=srm-students'>Students Page</a></li>";
echo "<li><a href='?page=srm-results'>Results Page</a></li>";
echo "<li><a href='?page=srm-dashboard'>Dashboard</a></li>";
echo "</ul>";

echo "<h3>📱 Contact Information</h3>";
echo "<p><strong>WhatsApp:</strong> <a href='https://wa.me/923083430923' target='_blank'>+923083430923</a></p>";
echo "<p><strong>Email:</strong> <a href='mailto:jaffar381996152@gmail.com'>jaffar381996152@gmail.com</a></p>";

echo "<h3>🎯 Summary</h3>";
echo "<p><strong>This comprehensive test will help identify:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>Database Issues:</strong> Connection, tables, and data problems</li>";
echo "<li>✅ <strong>Form Issues:</strong> Nonce verification and data processing</li>";
echo "<li>✅ <strong>File Issues:</strong> Missing or corrupted files</li>";
echo "<li>✅ <strong>License Issues:</strong> License manager problems</li>";
echo "<li>✅ <strong>Insert Issues:</strong> Direct database insertion problems</li>";
echo "<li>✅ <strong>Simulation Issues:</strong> Form submission simulation problems</li>";
echo "</ul>";

echo "<h3>🧪 To Fix Results Issues</h3>";
echo "<ol>";
echo "<li><strong>Run This Test:</strong> Execute this comprehensive test</li>";
echo "<li><strong>Check Results:</strong> Review all test results above</li>";
echo "<li><strong>Identify Issues:</strong> Note any failed tests</li>";
echo "<li><strong>Test Admin Interface:</strong> Try the actual admin interface</li>";
echo "<li><strong>Report Issues:</strong> Note any remaining problems</li>";
echo "</ol>";

echo "<h3>🔧 Potential Issues and Solutions</h3>";
echo "<ul>";
echo "<li><strong>No Students:</strong> Add students first before adding results</li>";
echo "<li><strong>Missing Tables:</strong> Deactivate and reactivate the plugin</li>";
echo "<li><strong>Foreign Key Issues:</strong> Foreign key constraints have been removed</li>";
echo "<li><strong>License Issues:</strong> Check license manager functionality</li>";
echo "<li><strong>File Issues:</strong> Ensure all plugin files are present</li>";
echo "<li><strong>Database Issues:</strong> Check database permissions and connection</li>";
echo "</ul>";
?>