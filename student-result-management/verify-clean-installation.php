<?php
/**
 * Verify Clean Installation Process
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🧪 Verify Clean Installation Process</h2>";

global $wpdb;

echo "<h3>✅ 1. Current Installation Status</h3>";

// Check if this is a fresh installation
$has_data = false;
$data_sources = array();

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
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        echo "<p>" . ($exists ? "✅" : "❌") . " $table: " . ($exists ? "Exists ($count records)" : "Does not exist") . "</p>";
        if ($count > 0) {
            $has_data = true;
            $data_sources[] = "$table ($count records)";
        }
    } else {
        echo "<p>❌ $table: Does not exist</p>";
    }
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
    if ($value !== false && !empty($value)) {
        echo "<p>✅ $option: Set (" . (is_string($value) ? $value : 'non-empty') . ")</p>";
        $has_data = true;
        $data_sources[] = "$option option";
    } else {
        echo "<p>❌ $option: Not set or empty</p>";
    }
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
    if ($exists) {
        $files = glob($dir . '*');
        $file_count = count($files);
        echo "<p>✅ $dir: Exists ($file_count files)</p>";
        if ($file_count > 0) {
            $has_data = true;
            $data_sources[] = "$dir ($file_count files)";
        }
    } else {
        echo "<p>❌ $dir: Does not exist</p>";
    }
}

echo "<h3>✅ 2. Installation Status Assessment</h3>";

if ($has_data) {
    echo "<div class='notice notice-warning'>";
    echo "<p><strong>⚠️ This installation contains previous data:</strong></p>";
    echo "<ul>";
    foreach ($data_sources as $source) {
        echo "<li>$source</li>";
    }
    echo "</ul>";
    echo "<p><strong>This means the plugin was not properly uninstalled or contains data from a previous installation.</strong></p>";
    echo "</div>";
    
    echo "<h4>To achieve a clean installation:</h4>";
    echo "<ol>";
    echo "<li>Go to WordPress Admin > Plugins</li>";
    echo "<li>Deactivate the Student Result Management plugin</li>";
    echo "<li>Click 'Delete' to completely remove the plugin</li>";
    echo "<li>Upload and install the plugin again</li>";
    echo "<li>It should start completely fresh with no previous data</li>";
    echo "</ol>";
    
} else {
    echo "<div class='notice notice-success'>";
    echo "<p><strong>✅ This is a clean installation!</strong></p>";
    echo "<p>No previous data found. The plugin is ready for distribution.</p>";
    echo "</div>";
}

echo "<h3>✅ 3. Clean Installation Checklist</h3>";
echo "<p><strong>For a completely clean installation, verify:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>No Database Tables:</strong> All plugin tables should be empty or non-existent</li>";
echo "<li>✅ <strong>No Plugin Options:</strong> All plugin options should be unset</li>";
echo "<li>✅ <strong>No Uploaded Files:</strong> All plugin upload directories should be empty</li>";
echo "<li>✅ <strong>No License Data:</strong> License should be unset and status 'free'</li>";
echo "<li>✅ <strong>No Owner Data:</strong> Plugin owner should not be set</li>";
echo "<li>✅ <strong>Fresh Start:</strong> Plugin should behave like first-time installation</li>";
echo "</ul>";

echo "<h3>✅ 4. Distribution Readiness Test</h3>";

if (!$has_data) {
    echo "<div class='notice notice-success'>";
    echo "<p><strong>✅ Plugin is ready for distribution!</strong></p>";
    echo "<p>This installation is clean and will start fresh for each user.</p>";
    echo "</div>";
    
    echo "<h4>Expected behavior for end users:</h4>";
    echo "<ul>";
    echo "<li>✅ <strong>Fresh Start:</strong> No previous data will appear</li>";
    echo "<li>✅ <strong>Free Status:</strong> Plugin starts with free license</li>";
    echo "<li>✅ <strong>Contact Notice:</strong> Prominent contact information displayed</li>";
    echo "<li>✅ <strong>Upload Restrictions:</strong> All premium features restricted</li>";
    echo "<li>✅ <strong>Student Limit:</strong> Limited to 20 students</li>";
    echo "<li>✅ <strong>License Required:</strong> Users must activate license for premium</li>";
    echo "</ul>";
    
} else {
    echo "<div class='notice notice-error'>";
    echo "<p><strong>❌ Plugin is NOT ready for distribution!</strong></p>";
    echo "<p>Previous data exists and will appear for end users.</p>";
    echo "</div>";
    
    echo "<h4>Required actions:</h4>";
    echo "<ol>";
    echo "<li><strong>Uninstall Plugin:</strong> Completely remove the plugin</li>";
    echo "<li><strong>Clean Database:</strong> Ensure all plugin data is removed</li>";
    echo "<li><strong>Reinstall Plugin:</strong> Install fresh copy</li>";
    echo "<li><strong>Verify Clean State:</strong> Run this test again</li>";
    echo "</ol>";
}

echo "<h3>✅ 5. Manual Clean Installation Process</h3>";
echo "<p><strong>To ensure a completely clean installation:</strong></p>";
echo "<ol>";
echo "<li><strong>Backup Data:</strong> If you need to keep any data, backup first</li>";
echo "<li><strong>Deactivate Plugin:</strong> Go to Plugins > Deactivate</li>";
echo "<li><strong>Delete Plugin:</strong> Click 'Delete' to completely remove</li>";
echo "<li><strong>Clean Database:</strong> Use the uninstall test script if needed</li>";
echo "<li><strong>Upload Fresh:</strong> Upload the clean plugin files</li>";
echo "<li><strong>Activate Plugin:</strong> Activate the fresh installation</li>";
echo "<li><strong>Verify Clean State:</strong> Run this verification script</li>";
echo "</ol>";

echo "<h3>🔗 Quick Test Links</h3>";
echo "<ul>";
echo "<li><a href='?page=srm-dashboard'>Dashboard</a></li>";
echo "<li><a href='?page=srm-students'>Students</a></li>";
echo "<li><a href='?page=srm-results'>Results</a></li>";
echo "<li><a href='?page=srm-settings'>Settings</a></li>";
echo "<li><a href='?page=srm-premium'>Premium Features</a></li>";
echo "<li><a href='?page=test-uninstall'>Test Uninstall</a></li>";
echo "<li><a href='?page=clear-license-and-test'>Clear License Data</a></li>";
echo "</ul>";

echo "<h3>📱 Contact Information</h3>";
echo "<p><strong>WhatsApp:</strong> <a href='https://wa.me/923083430923' target='_blank'>+923083430923</a></p>";
echo "<p><strong>Email:</strong> <a href='mailto:jaffar381996152@gmail.com'>jaffar381996152@gmail.com</a></p>";

echo "<h3>🎯 Summary</h3>";
echo "<p><strong>Clean Installation Requirements:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>No Previous Data:</strong> All tables empty, options unset</li>";
echo "<li>✅ <strong>No Uploaded Files:</strong> All plugin directories empty</li>";
echo "<li>✅ <strong>No License Data:</strong> License unset, status 'free'</li>";
echo "<li>✅ <strong>Fresh Start:</strong> Plugin behaves like first installation</li>";
echo "<li>✅ <strong>Distribution Ready:</strong> Safe to distribute to users</li>";
echo "</ul>";

if ($has_data) {
    echo "<p><strong>⚠️ Current Status: NOT READY FOR DISTRIBUTION</strong></p>";
    echo "<p>Previous data exists and will appear for end users.</p>";
} else {
    echo "<p><strong>✅ Current Status: READY FOR DISTRIBUTION</strong></p>";
    echo "<p>Plugin is clean and will start fresh for each user.</p>";
}
?>