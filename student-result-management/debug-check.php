<?php
/**
 * Debug script to check if Student Result Management plugin tables exist
 * Place this file in the plugin directory and access it via browser
 */

// Load WordPress
require_once('../../../wp-config.php');

if (!current_user_can('manage_options')) {
    die('Access denied. You need administrator privileges.');
}

global $wpdb;

echo "<h1>Student Result Management - Database Debug</h1>";

// Check if tables exist
$tables = array(
    'students' => $wpdb->prefix . 'srm_students',
    'results' => $wpdb->prefix . 'srm_results',
    'settings' => $wpdb->prefix . 'srm_settings'
);

echo "<h2>Table Status:</h2>";
foreach ($tables as $name => $table) {
    $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
    $status = $exists ? '✅ EXISTS' : '❌ MISSING';
    echo "<p><strong>{$name} table ({$table}):</strong> {$status}</p>";
    
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        echo "<p>&nbsp;&nbsp;&nbsp;Records: {$count}</p>";
        
        if ($name === 'students') {
            $structure = $wpdb->get_results("DESCRIBE {$table}");
            echo "<p>&nbsp;&nbsp;&nbsp;Structure:</p>";
            echo "<ul>";
            foreach ($structure as $column) {
                echo "<li>{$column->Field} ({$column->Type})</li>";
            }
            echo "</ul>";
        }
    }
}

// Test database connection
echo "<h2>Database Connection Test:</h2>";
$test_query = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}%'");
if ($test_query) {
    echo "<p>✅ Database connection working</p>";
    echo "<p>WordPress prefix: {$wpdb->prefix}</p>";
} else {
    echo "<p>❌ Database connection failed</p>";
}

// Check plugin status
echo "<h2>Plugin Status:</h2>";
$active_plugins = get_option('active_plugins');
$is_active = in_array('student-result-management/student-result-management.php', $active_plugins);
echo "<p><strong>Plugin Active:</strong> " . ($is_active ? '✅ YES' : '❌ NO') . "</p>";

// Check plugin settings
$plugin_owner = get_option('srm_plugin_owner');
echo "<p><strong>Plugin Owner ID:</strong> " . ($plugin_owner ? $plugin_owner : 'Not set') . "</p>";

// Current user info
$current_user = wp_get_current_user();
echo "<p><strong>Current User ID:</strong> {$current_user->ID}</p>";
echo "<p><strong>Current User:</strong> {$current_user->user_login}</p>";

echo "<h2>Next Steps:</h2>";
if (!$exists) {
    echo "<p>❌ Tables are missing. Try deactivating and reactivating the plugin.</p>";
} else {
    echo "<p>✅ Tables exist. Try adding a student manually.</p>";
}

// Test insert (if tables exist)
if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $tables['students']))) {
    echo "<h2>Test Insert:</h2>";
    
    // Try a simple insert
    $test_data = array(
        'roll_number' => 'TEST' . time(),
        'first_name' => 'Test',
        'last_name' => 'Student',
        'class' => 'Test Class'
    );
    
    $result = $wpdb->insert($tables['students'], $test_data);
    
    if ($result) {
        echo "<p>✅ Test insert successful (ID: {$wpdb->insert_id})</p>";
        
        // Clean up test data
        $wpdb->delete($tables['students'], array('id' => $wpdb->insert_id));
        echo "<p>Test data cleaned up.</p>";
    } else {
        echo "<p>❌ Test insert failed: " . $wpdb->last_error . "</p>";
    }
}

?>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
p { margin: 5px 0; }
ul { margin: 5px 0; }
</style>