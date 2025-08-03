<?php
/**
 * Test Uninstall Functionality
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🧪 Test Uninstall Functionality</h2>";

global $wpdb;

echo "<h3>✅ 1. Current Plugin Data Status</h3>";

// Check database tables
$tables_to_check = array(
    $wpdb->prefix . 'srm_students',
    $wpdb->prefix . 'srm_results', 
    $wpdb->prefix . 'srm_settings',
    $wpdb->prefix . 'srm_payments',
    $wpdb->prefix . 'srm_notifications',
    $wpdb->prefix . 'srm_templates',
    $wpdb->prefix . 'srm_analytics'
);

echo "<h4>Database Tables:</h4>";
foreach ($tables_to_check as $table) {
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
    echo "<p>" . ($exists ? "✅" : "❌") . " $table: " . ($exists ? "Exists" : "Does not exist") . "</p>";
}

// Check plugin options
$options_to_check = array(
    'srm_license_key',
    'srm_license_status', 
    'srm_plugin_owner',
    'srm_school_name',
    'srm_school_logo',
    'srm_admin_email',
    'srm_result_template',
    'srm_grade_system',
    'srm_passing_marks'
);

echo "<h4>Plugin Options:</h4>";
foreach ($options_to_check as $option) {
    $value = get_option($option);
    echo "<p>" . ($value !== false ? "✅" : "❌") . " $option: " . ($value !== false ? "Set" : "Not set") . "</p>";
}

// Check uploaded files
$upload_dir = wp_upload_dir();
$plugin_upload_dirs = array(
    $upload_dir['basedir'] . '/srm-profiles/',
    $upload_dir['basedir'] . '/srm-certificates/',
    $upload_dir['basedir'] . '/srm-exports/',
    $upload_dir['basedir'] . '/srm-templates/'
);

echo "<h4>Upload Directories:</h4>";
foreach ($plugin_upload_dirs as $dir) {
    $exists = is_dir($dir);
    echo "<p>" . ($exists ? "✅" : "❌") . " $dir: " . ($exists ? "Exists" : "Does not exist") . "</p>";
}

echo "<h3>✅ 2. Uninstall Process</h3>";
echo "<p><strong>When the plugin is uninstalled, the following will happen:</strong></p>";
echo "<ol>";
echo "<li><strong>Database Tables Removed:</strong> All plugin tables will be dropped</li>";
echo "<li><strong>Plugin Options Removed:</strong> All plugin options will be deleted</li>";
echo "<li><strong>Transients Cleared:</strong> All plugin transients will be removed</li>";
echo "<li><strong>Uploaded Files Removed:</strong> All plugin upload directories and files will be deleted</li>";
echo "<li><strong>Orphaned Data Cleaned:</strong> Any remaining plugin data will be removed</li>";
echo "<li><strong>WordPress Cache Cleared:</strong> Cache will be flushed</li>";
echo "</ol>";

echo "<h3>✅ 3. Manual Uninstall Test</h3>";

if (isset($_POST['test_uninstall'])) {
    echo "<div class='notice notice-warning'>";
    echo "<p><strong>⚠️ WARNING: This will permanently delete all plugin data!</strong></p>";
    echo "<p>This is a test of the uninstall process. All your plugin data will be permanently deleted.</p>";
    echo "</div>";
    
    echo "<form method='post'>";
    echo "<p><strong>Are you sure you want to test the uninstall process?</strong></p>";
    echo "<p>This will permanently delete:</p>";
    echo "<ul>";
    echo "<li>All student records</li>";
    echo "<li>All result records</li>";
    echo "<li>All plugin settings</li>";
    echo "<li>All uploaded files</li>";
    echo "<li>All license information</li>";
    echo "</ul>";
    echo "<input type='submit' name='confirm_uninstall' value='Yes, Delete All Plugin Data' class='button button-danger'>";
    echo "</form>";
    
} elseif (isset($_POST['confirm_uninstall'])) {
    echo "<h3>🧹 Executing Uninstall Process...</h3>";
    
    // 1. Remove all plugin database tables
    $tables_to_remove = array(
        $wpdb->prefix . 'srm_students',
        $wpdb->prefix . 'srm_results', 
        $wpdb->prefix . 'srm_settings',
        $wpdb->prefix . 'srm_payments',
        $wpdb->prefix . 'srm_notifications',
        $wpdb->prefix . 'srm_templates',
        $wpdb->prefix . 'srm_analytics'
    );

    foreach ($tables_to_remove as $table) {
        $result = $wpdb->query("DROP TABLE IF EXISTS $table");
        echo "<p>" . ($result !== false ? "✅" : "❌") . " Removed table: $table</p>";
    }

    // 2. Remove all plugin options
    $options_to_remove = array(
        'srm_license_key',
        'srm_license_status', 
        'srm_plugin_owner',
        'srm_school_name',
        'srm_school_logo',
        'srm_admin_email',
        'srm_result_template',
        'srm_grade_system',
        'srm_passing_marks',
        'srm_stripe_secret_key',
        'srm_stripe_publishable_key',
        'srm_paypal_client_id',
        'srm_paypal_secret',
        'srm_premium_price',
        'srm_currency',
        'srm_activation_error',
        'srm_plugin_version'
    );

    foreach ($options_to_remove as $option) {
        $result = delete_option($option);
        echo "<p>" . ($result ? "✅" : "❌") . " Removed option: $option</p>";
    }

    // 3. Remove all plugin transients
    $transients_to_remove = array(
        'srm_license_check',
        'srm_analytics_cache',
        'srm_notifications_cache'
    );

    foreach ($transients_to_remove as $transient) {
        $result = delete_transient($transient);
        echo "<p>" . ($result ? "✅" : "❌") . " Removed transient: $transient</p>";
    }

    // 4. Remove uploaded files and directories
    $upload_dir = wp_upload_dir();
    $plugin_upload_dirs = array(
        $upload_dir['basedir'] . '/srm-profiles/',
        $upload_dir['basedir'] . '/srm-certificates/',
        $upload_dir['basedir'] . '/srm-exports/',
        $upload_dir['basedir'] . '/srm-templates/'
    );

    foreach ($plugin_upload_dirs as $dir) {
        if (is_dir($dir)) {
            // Remove all files in directory
            $files = glob($dir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    $result = unlink($file);
                    echo "<p>" . ($result ? "✅" : "❌") . " Removed file: $file</p>";
                }
            }
            // Remove directory
            $result = rmdir($dir);
            echo "<p>" . ($result ? "✅" : "❌") . " Removed directory: $dir</p>";
        }
    }

    // 5. Remove any remaining plugin data
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'srm_%'");
    $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'srm_%'");
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'srm_%'");
    
    echo "<p>✅ Removed all remaining plugin data</p>";

    // 6. Clear cache
    wp_cache_flush();
    echo "<p>✅ WordPress cache cleared</p>";

    echo "<h3>✅ Uninstall Test Complete!</h3>";
    echo "<p><strong>All plugin data has been permanently deleted.</strong></p>";
    echo "<p><strong>If you reinstall the plugin now, it will start completely fresh with no previous data.</strong></p>";
    
} else {
    echo "<form method='post'>";
    echo "<p><strong>Click the button below to test the uninstall process:</strong></p>";
    echo "<input type='submit' name='test_uninstall' value='Test Uninstall Process' class='button button-primary'>";
    echo "</form>";
    
    echo "<h3>✅ 4. What This Test Will Do</h3>";
    echo "<ul>";
    echo "<li>✅ <strong>Show Current Data:</strong> Display all existing plugin data</li>";
    echo "<li>✅ <strong>Explain Uninstall Process:</strong> Show what will be removed</li>";
    echo "<li>✅ <strong>Test Data Removal:</strong> Actually remove all plugin data</li>";
    echo "<li>✅ <strong>Verify Clean State:</strong> Confirm plugin starts fresh</li>";
    echo "</ul>";
}

echo "<h3>✅ 5. Expected Behavior After Uninstall</h3>";
echo "<ul>";
echo "<li>✅ <strong>Fresh Installation:</strong> Plugin will start with no data</li>";
echo "<li>✅ <strong>No Students:</strong> Student table will be empty</li>";
echo "<li>✅ <strong>No Results:</strong> Results table will be empty</li>";
echo "<li>✅ <strong>No Settings:</strong> All settings will be reset to defaults</li>";
echo "<li>✅ <strong>No License:</strong> License will need to be activated again</li>";
echo "<li>✅ <strong>No Files:</strong> All uploaded files will be removed</li>";
echo "</ul>";

echo "<h3>🔗 Quick Test Links</h3>";
echo "<ul>";
echo "<li><a href='?page=srm-dashboard'>Dashboard</a></li>";
echo "<li><a href='?page=srm-students'>Students</a></li>";
echo "<li><a href='?page=srm-results'>Results</a></li>";
echo "<li><a href='?page=srm-settings'>Settings</a></li>";
echo "<li><a href='?page=srm-premium'>Premium Features</a></li>";
echo "</ul>";

echo "<h3>📱 Contact Information</h3>";
echo "<p><strong>WhatsApp:</strong> <a href='https://wa.me/923083430923' target='_blank'>+923083430923</a></p>";
echo "<p><strong>Email:</strong> <a href='mailto:jaffar381996152@gmail.com'>jaffar381996152@gmail.com</a></p>";

echo "<h3>🎯 Summary</h3>";
echo "<p><strong>The uninstall process will completely remove all plugin data:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>Database Tables:</strong> All plugin tables dropped</li>";
echo "<li>✅ <strong>Plugin Options:</strong> All options deleted</li>";
echo "<li>✅ <strong>Uploaded Files:</strong> All files and directories removed</li>";
echo "<li>✅ <strong>Transients:</strong> All cached data cleared</li>";
echo "<li>✅ <strong>Orphaned Data:</strong> Any remaining data cleaned up</li>";
echo "</ul>";
echo "<p><strong>This ensures that when you distribute the plugin, it will start completely fresh for each user.</strong></p>";
?>