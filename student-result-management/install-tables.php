<?php
/**
 * Manual Database Table Installation for Student Result Management
 * Run this file ONLY if the plugin activation didn't create tables automatically
 */

// Load WordPress
require_once('../../../wp-config.php');

// Security check
if (!current_user_can('manage_options')) {
    die('Access denied. You need administrator privileges to run this script.');
}

global $wpdb;

echo "<h1>Student Result Management - Manual Table Installation</h1>";

$charset_collate = $wpdb->get_charset_collate();

// Define SQL for each table
$tables_sql = array(
    'students' => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}srm_students (
        id int(11) NOT NULL AUTO_INCREMENT,
        roll_number varchar(50) NOT NULL,
        first_name varchar(100) NOT NULL,
        last_name varchar(100) NOT NULL,
        email varchar(100) DEFAULT NULL,
        phone varchar(20) DEFAULT NULL,
        class varchar(50) NOT NULL,
        section varchar(10) DEFAULT NULL,
        date_of_birth date DEFAULT NULL,
        profile_image varchar(255) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY roll_number (roll_number),
        KEY class_section (class, section)
    ) $charset_collate",
    
    'results' => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}srm_results (
        id int(11) NOT NULL AUTO_INCREMENT,
        student_id int(11) NOT NULL,
        exam_name varchar(100) NOT NULL,
        exam_date date DEFAULT NULL,
        total_marks int(11) NOT NULL,
        obtained_marks int(11) NOT NULL,
        percentage decimal(5,2) DEFAULT NULL,
        grade varchar(5) DEFAULT NULL,
        status enum('pass','fail','pending') DEFAULT 'pending',
        subjects text,
        remarks text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY student_id (student_id),
        KEY exam_date (exam_date),
        KEY status (status)
    ) $charset_collate",
    
    'settings' => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}srm_settings (
        id int(11) NOT NULL AUTO_INCREMENT,
        setting_key varchar(100) NOT NULL,
        setting_value longtext,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY setting_key (setting_key)
    ) $charset_collate",
    
    'payments' => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}srm_payments (
        id int(11) NOT NULL AUTO_INCREMENT,
        transaction_id varchar(100) NOT NULL UNIQUE,
        amount decimal(10,2) NOT NULL,
        currency varchar(10) NOT NULL DEFAULT 'USD',
        payment_method varchar(50) NOT NULL,
        customer_email varchar(100) NOT NULL,
        customer_name varchar(100) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY transaction_id (transaction_id),
        KEY status (status),
        KEY customer_email (customer_email)
    ) $charset_collate"
);

echo "<h2>Creating Database Tables...</h2>";

$success_count = 0;
$total_tables = count($tables_sql);

foreach ($tables_sql as $table_name => $sql) {
    echo "<p><strong>Creating {$table_name} table...</strong></p>";
    
    $result = $wpdb->query($sql);
    
    if ($result !== false) {
        echo "<p style='color: green;'>✅ {$table_name} table created successfully!</p>";
        $success_count++;
        
        // Verify table exists
        $table_full_name = $wpdb->prefix . 'srm_' . $table_name;
        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_full_name));
        if ($exists) {
            echo "<p style='color: green;'>&nbsp;&nbsp;&nbsp;✅ Verified: Table exists in database</p>";
        } else {
            echo "<p style='color: red;'>&nbsp;&nbsp;&nbsp;❌ Warning: Table not found after creation</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Failed to create {$table_name} table</p>";
        echo "<p style='color: red;'>&nbsp;&nbsp;&nbsp;Error: " . $wpdb->last_error . "</p>";
    }
    echo "<hr>";
}

echo "<h2>Installation Summary</h2>";
echo "<p><strong>Tables Created: {$success_count} / {$total_tables}</strong></p>";

if ($success_count === $total_tables) {
    echo "<p style='color: green; font-size: 18px;'>🎉 <strong>SUCCESS!</strong> All database tables created successfully!</p>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li>Go to WordPress Admin → Student Results → Students</li>";
    echo "<li>Add your first student</li>";
    echo "<li>Add exam results</li>";
    echo "<li>Use the [student_result_lookup] shortcode on any page</li>";
    echo "</ul>";
    
    // Set plugin owner if not already set
    $plugin_owner = get_option('srm_plugin_owner');
    if (!$plugin_owner) {
        $current_user = wp_get_current_user();
        update_option('srm_plugin_owner', $current_user->ID);
        echo "<p style='color: blue;'>ℹ️ Set you ({$current_user->user_login}) as the plugin owner with full premium access.</p>";
    }
    
    // Clear any activation errors
    delete_option('srm_activation_error');
    
} else {
    echo "<p style='color: red; font-size: 18px;'>❌ <strong>INCOMPLETE INSTALLATION</strong></p>";
    echo "<p>Some tables could not be created. Please:</p>";
    echo "<ul>";
    echo "<li>Check your database permissions</li>";
    echo "<li>Ensure your WordPress user can CREATE tables</li>";
    echo "<li>Contact your hosting provider if needed</li>";
    echo "</ul>";
}

echo "<h2>Database Information</h2>";
echo "<p><strong>WordPress Database Prefix:</strong> {$wpdb->prefix}</p>";
echo "<p><strong>Tables that should exist:</strong></p>";
echo "<ul>";
echo "<li>{$wpdb->prefix}srm_students</li>";
echo "<li>{$wpdb->prefix}srm_results</li>";
echo "<li>{$wpdb->prefix}srm_settings</li>";
echo "</ul>";

echo "<p><a href='" . admin_url('admin.php?page=srm-dashboard') . "' class='button button-primary'>Go to Plugin Dashboard</a></p>";

?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background: #f1f1f1;
}
h1, h2 {
    color: #333;
}
p {
    margin: 5px 0;
}
ul {
    margin: 5px 0;
}
hr {
    margin: 20px 0;
    border: none;
    border-top: 1px solid #ccc;
}
.button {
    display: inline-block;
    padding: 10px 20px;
    background: #0073aa;
    color: white;
    text-decoration: none;
    border-radius: 3px;
}
.button:hover {
    background: #005a87;
}
</style>