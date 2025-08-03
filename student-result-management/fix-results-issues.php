<?php
/**
 * Fix Results Issues
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🔧 Fix Results Issues</h2>";

// Include necessary files
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';

global $wpdb;

echo "<h3>✅ 1. Database Tables Check</h3>";

// Check and create tables if needed
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
        
        // Try to create the table
        if ($table_name === 'srm_students') {
            $sql = "CREATE TABLE $table (
                id int(11) NOT NULL AUTO_INCREMENT,
                roll_number varchar(50) NOT NULL UNIQUE,
                first_name varchar(100) NOT NULL,
                last_name varchar(100) NOT NULL,
                email varchar(100),
                phone varchar(20),
                class varchar(50) NOT NULL,
                section varchar(10),
                date_of_birth date,
                profile_image varchar(255),
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY roll_number (roll_number)
            ) " . $wpdb->get_charset_collate();
        } elseif ($table_name === 'srm_results') {
            $sql = "CREATE TABLE $table (
                id int(11) NOT NULL AUTO_INCREMENT,
                student_id int(11) NOT NULL,
                exam_name varchar(100) NOT NULL,
                exam_date date,
                total_marks int(11) DEFAULT 0,
                obtained_marks int(11) DEFAULT 0,
                percentage decimal(5,2) DEFAULT 0.00,
                grade varchar(10),
                status enum('pass','fail','pending') DEFAULT 'pending',
                subjects text,
                certificate_pdf varchar(255) DEFAULT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY student_id (student_id)
            ) " . $wpdb->get_charset_collate();
        }
        
        $result = $wpdb->query($sql);
        if ($result !== false) {
            echo "<div class='notice notice-success'>";
            echo "<p><strong>✅ $table_name table created successfully!</strong></p>";
            echo "</div>";
        } else {
            echo "<div class='notice notice-error'>";
            echo "<p><strong>❌ Failed to create $table_name table!</strong></p>";
            echo "<p>Error: " . esc_html($wpdb->last_error) . "</p>";
            echo "</div>";
        }
    }
}

echo "<h3>✅ 2. Add Sample Student (if none exist)</h3>";

$students_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_students");

if ($students_count == 0) {
    echo "<div class='notice notice-warning'>";
    echo "<p><strong>⚠️ No students found!</strong> Adding a sample student for testing.</p>";
    echo "</div>";
    
    $sample_student = array(
        'roll_number' => 'STU001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'phone' => '1234567890',
        'class' => '10th Grade',
        'section' => 'A',
        'date_of_birth' => '2005-01-01'
    );
    
    $result = $wpdb->insert($wpdb->prefix . 'srm_students', $sample_student);
    
    if ($result !== false) {
        echo "<div class='notice notice-success'>";
        echo "<p><strong>✅ Sample student added successfully!</strong></p>";
        echo "<p>Name: John Doe (Roll: STU001)</p>";
        echo "</div>";
    } else {
        echo "<div class='notice notice-error'>";
        echo "<p><strong>❌ Failed to add sample student!</strong></p>";
        echo "<p>Error: " . esc_html($wpdb->last_error) . "</p>";
        echo "</div>";
    }
} else {
    echo "<div class='notice notice-success'>";
    echo "<p><strong>✅ Students exist ($students_count total)</strong></p>";
    echo "</div>";
}

echo "<h3>✅ 3. Test Result Insertion</h3>";

if (isset($_POST['test_result_insert'])) {
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
    
    echo "<h4>Attempting to insert test result:</h4>";
    echo "<pre>" . print_r($test_data, true) . "</pre>";
    
    $results_table = $wpdb->prefix . 'srm_results';
    $result = $wpdb->insert($results_table, $test_data);
    
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
        echo "<p><input type='submit' name='test_result_insert' value='Test Result Insert' class='button button-primary'></p>";
        echo "</form>";
    } else {
        echo "<div class='notice notice-error'>";
        echo "<p><strong>❌ No students available for testing!</strong></p>";
        echo "<p>Please add at least one student before testing result insertion.</p>";
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

echo "<h3>✅ 5. File Include Check</h3>";

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

echo "<h3>✅ 6. Manual Test Steps</h3>";
echo "<ol>";
echo "<li><strong>Check Students:</strong> Verify students exist in the database</li>";
echo "<li><strong>Test Result Insert:</strong> Use the test form above</li>";
echo "<li><strong>Go to Results Page:</strong> Navigate to the results admin page</li>";
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
echo "<p><strong>This fix script addresses:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>Missing Tables:</strong> Creates tables if they don't exist</li>";
echo "<li>✅ <strong>No Students:</strong> Adds sample student if none exist</li>";
echo "<li>✅ <strong>Foreign Key Issues:</strong> Removed foreign key constraints</li>";
echo "<li>✅ <strong>License Manager:</strong> Tests license manager functionality</li>";
echo "<li>✅ <strong>File Includes:</strong> Verifies proper file includes</li>";
echo "<li>✅ <strong>Database Errors:</strong> Shows detailed error messages</li>";
echo "<li>✅ <strong>Test Insertion:</strong> Tests result insertion directly</li>";
echo "</ul>";

echo "<h3>🧪 To Test Results Fix</h3>";
echo "<ol>";
echo "<li><strong>Run This Script:</strong> Execute this fix script first</li>";
echo "<li><strong>Test Insertion:</strong> Use the test form above</li>";
echo "<li><strong>Check Admin Interface:</strong> Go to Results page and try adding</li>";
echo "<li><strong>Verify Success:</strong> Check if results are added successfully</li>";
echo "<li><strong>Check Error Messages:</strong> Note any remaining issues</li>";
echo "</ol>";

echo "<h3>🔧 Files Modified</h3>";
echo "<ul>";
echo "<li>✅ <strong>student-result-management.php:</strong> Removed foreign key constraints</li>";
echo "<li>✅ <strong>results.php:</strong> Added better error messages and debugging</li>";
echo "<li>✅ <strong>Database:</strong> Ensures tables exist and are properly structured</li>";
echo "<li>✅ <strong>Sample Data:</strong> Adds sample student if needed</li>";
echo "</ul>";
?>