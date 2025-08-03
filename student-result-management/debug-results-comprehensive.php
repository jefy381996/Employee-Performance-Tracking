<?php
/**
 * Comprehensive Results Debug
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🔍 Comprehensive Results Debug</h2>";

// Include necessary files
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';

global $wpdb;

echo "<h3>✅ 1. Database Connection Test</h3>";
if ($wpdb->last_error) {
    echo "<div class='notice notice-error'>";
    echo "<p><strong>❌ Database Error:</strong> " . esc_html($wpdb->last_error) . "</p>";
    echo "</div>";
} else {
    echo "<div class='notice notice-success'>";
    echo "<p><strong>✅ Database connection successful</strong></p>";
    echo "</div>";
}

echo "<h3>✅ 2. Tables Check</h3>";
$tables_to_check = array(
    'srm_students' => $wpdb->prefix . 'srm_students',
    'srm_results' => $wpdb->prefix . 'srm_results',
    'srm_settings' => $wpdb->prefix . 'srm_settings'
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

echo "<h3>✅ 3. Results Table Structure</h3>";
$results_table = $wpdb->prefix . 'srm_results';
$columns = $wpdb->get_results("DESCRIBE $results_table");

if ($columns) {
    echo "<table class='widefat'>";
    echo "<thead><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead>";
    echo "<tbody>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . esc_html($column->Field) . "</td>";
        echo "<td>" . esc_html($column->Type) . "</td>";
        echo "<td>" . esc_html($column->Null) . "</td>";
        echo "<td>" . esc_html($column->Key) . "</td>";
        echo "<td>" . esc_html($column->Default) . "</td>";
        echo "<td>" . esc_html($column->Extra) . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<div class='notice notice-error'>";
    echo "<p><strong>❌ Could not get table structure!</strong></p>";
    echo "</div>";
}

echo "<h3>✅ 4. Foreign Key Constraints</h3>";
$foreign_keys = $wpdb->get_results("
    SELECT 
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = '{$wpdb->prefix}srm_results'
    AND REFERENCED_TABLE_NAME IS NOT NULL
");

if ($foreign_keys) {
    echo "<div class='notice notice-info'>";
    echo "<h4>Foreign Key Constraints Found:</h4>";
    echo "<ul>";
    foreach ($foreign_keys as $fk) {
        echo "<li><strong>{$fk->CONSTRAINT_NAME}:</strong> {$fk->COLUMN_NAME} → {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}</li>";
    }
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div class='notice notice-warning'>";
    echo "<p><strong>ℹ️ No foreign key constraints found</strong></p>";
    echo "</div>";
}

echo "<h3>✅ 5. Students Data Check</h3>";
$students_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_students");
echo "<p><strong>Total Students:</strong> $students_count</p>";

if ($students_count == 0) {
    echo "<div class='notice notice-error'>";
    echo "<p><strong>❌ No students found!</strong> You need to add students before adding results.</p>";
    echo "</div>";
} else {
    $first_student = $wpdb->get_row("SELECT id, first_name, last_name FROM {$wpdb->prefix}srm_students LIMIT 1");
    echo "<div class='notice notice-success'>";
    echo "<p><strong>✅ Students available:</strong> " . esc_html($first_student->first_name . ' ' . $first_student->last_name) . " (ID: {$first_student->id})</p>";
    echo "</div>";
}

echo "<h3>✅ 6. License Manager Test</h3>";
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

echo "<h3>✅ 7. Test Result Insert</h3>";

if (isset($_POST['test_insert'])) {
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
    
    echo "<h4>Attempting to insert test data:</h4>";
    echo "<pre>" . print_r($test_data, true) . "</pre>";
    
    $result = $wpdb->insert($results_table, $test_data);
    
    if ($result !== false) {
        echo "<div class='notice notice-success'>";
        echo "<p><strong>✅ Test insert successful!</strong> Insert ID: " . $wpdb->insert_id . "</p>";
        echo "</div>";
        
        // Clean up test data
        $wpdb->delete($results_table, array('id' => $wpdb->insert_id));
        echo "<p><em>Test record deleted.</em></p>";
    } else {
        echo "<div class='notice notice-error'>";
        echo "<p><strong>❌ Test insert failed!</strong></p>";
        echo "<p><strong>Error:</strong> " . esc_html($wpdb->last_error) . "</p>";
        echo "</div>";
    }
} else {
    // Get first student for test
    $first_student = $wpdb->get_row("SELECT id, first_name, last_name FROM {$wpdb->prefix}srm_students LIMIT 1");
    
    if ($first_student) {
        echo "<form method='post'>";
        echo "<h4>Test Result Insert:</h4>";
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
        echo "<p><input type='submit' name='test_insert' value='Test Insert' class='button button-primary'></p>";
        echo "</form>";
    } else {
        echo "<div class='notice notice-error'>";
        echo "<p><strong>❌ No students available for testing!</strong></p>";
        echo "<p>Please add at least one student before testing result insertion.</p>";
        echo "</div>";
    }
}

echo "<h3>✅ 8. File Include Check</h3>";

$files_to_check = array(
    'results.php' => SRM_PLUGIN_PATH . 'includes/admin/results.php',
    'license-manager.php' => SRM_PLUGIN_PATH . 'includes/admin/license-manager.php'
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

echo "<h3>✅ 9. Common Issues Check</h3>";

// Check for common issues
$issues = array();

// Check if students table has data
if ($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_students") == 0) {
    $issues[] = "No students in database - cannot add results without students";
}

// Check if results table exists
if (!$wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->prefix . 'srm_results'))) {
    $issues[] = "Results table does not exist";
}

// Check for foreign key constraint issues
if ($foreign_keys) {
    $issues[] = "Foreign key constraint exists - student_id must reference valid student";
}

// Check for recent errors
$recent_errors = $wpdb->get_results("SHOW WARNINGS");
if ($recent_errors) {
    echo "<p><strong>Recent Database Warnings:</strong></p>";
    echo "<ul>";
    foreach ($recent_errors as $error) {
        echo "<li>Level: {$error->Level}, Code: {$error->Code}, Message: {$error->Message}</li>";
    }
    echo "</ul>";
}

if (empty($issues)) {
    echo "<div class='notice notice-success'>";
    echo "<p><strong>✅ No obvious issues found</strong></p>";
    echo "</div>";
} else {
    echo "<div class='notice notice-error'>";
    echo "<p><strong>❌ Issues Found:</strong></p>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<h3>✅ 10. Manual Test Steps</h3>";
echo "<ol>";
echo "<li><strong>Add a Student:</strong> Go to Students page and add at least one student</li>";
echo "<li><strong>Check Student ID:</strong> Note the student ID from the students list</li>";
echo "<li><strong>Add Result:</strong> Go to Results page and try to add a result</li>";
echo "<li><strong>Check Error:</strong> If error occurs, note the exact error message</li>";
echo "<li><strong>Test Form:</strong> Fill all required fields and submit</li>";
echo "<li><strong>Check Database:</strong> Verify the result appears in the database</li>";
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
echo "<p><strong>This comprehensive debug will help identify:</strong></p>";
echo "<ul>";
echo "<li>✅ Database connection issues</li>";
echo "<li>✅ Missing tables or columns</li>";
echo "<li>✅ Foreign key constraint problems</li>";
echo "<li>✅ License manager problems</li>";
echo "<li>✅ Data insertion problems</li>";
echo "<li>✅ File include issues</li>";
echo "<li>✅ Common configuration issues</li>";
echo "</ul>";
?>