<?php
/**
 * Uninstall Student Result Management Plugin
 * 
 * This file is executed when the plugin is uninstalled.
 * It removes all plugin data including database tables and options.
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Include WordPress database functions
global $wpdb;

echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
echo "<div style='display: flex; align-items: center; gap: 15px;'>";
echo "<div style='background: #f39c12; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 20px;'>⚠️</div>";
echo "<div>";
echo "<h4 style='margin: 0 0 8px 0; color: #856404; font-size: 16px;'>⚠️ WARNING: Data Loss</h4>";
echo "<p style='margin: 0; color: #856404; font-size: 14px;'>This will permanently delete ALL your students, results, and settings data. Make sure you have backed up your data before proceeding!</p>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "<h2>🧹 Uninstalling Student Result Management Plugin</h2>";
echo "<p>Cleaning up all plugin data...</p>";

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
    $wpdb->query("DROP TABLE IF EXISTS $table");
    echo "<p>✅ Removed table: $table</p>";
}

// 2. Remove all plugin options from wp_options table
$options_to_remove = array(
    'srm_license_key',
    'srm_license_status',
    'srm_license_activated_at',
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
    delete_option($option);
    echo "<p>✅ Removed option: $option</p>";
}

// 3. Remove all plugin transients
$transients_to_remove = array(
    'srm_license_check',
    'srm_analytics_cache',
    'srm_notifications_cache'
);

foreach ($transients_to_remove as $transient) {
    delete_transient($transient);
    echo "<p>✅ Removed transient: $transient</p>";
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
                unlink($file);
                echo "<p>✅ Removed file: $file</p>";
            }
        }
        // Remove directory
        rmdir($dir);
        echo "<p>✅ Removed directory: $dir</p>";
    }
}

// 5. Remove any remaining plugin data from wp_options (wildcard search)
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'srm_%'");
echo "<p>✅ Removed all remaining plugin options</p>";

// 6. Remove any remaining plugin data from wp_usermeta (if any)
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'srm_%'");
echo "<p>✅ Removed all plugin user meta data</p>";

// 7. Remove any remaining plugin data from wp_postmeta (if any)
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'srm_%'");
echo "<p>✅ Removed all plugin post meta data</p>";

// 8. Clean up any orphaned data
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%srm_%'");
echo "<p>✅ Removed any orphaned plugin data</p>";

echo "<h3>✅ Uninstallation Complete!</h3>";
echo "<p><strong>All plugin data has been completely removed:</strong></p>";
echo "<ul>";
echo "<li>✅ Database tables removed</li>";
echo "<li>✅ Plugin options removed</li>";
echo "<li>✅ Transients cleared</li>";
echo "<li>✅ Uploaded files removed</li>";
echo "<li>✅ Upload directories removed</li>";
echo "<li>✅ All plugin data cleaned up</li>";
echo "</ul>";

echo "<p><strong>The plugin is now completely uninstalled with no data remaining.</strong></p>";
echo "<p><strong>If you reinstall the plugin, it will start fresh with no previous data.</strong></p>";

// Force WordPress to refresh its cache
wp_cache_flush();
echo "<p>✅ WordPress cache cleared</p>";

echo "<h3>🎯 Summary</h3>";
echo "<p>The plugin has been completely removed from your WordPress installation. All data including:</p>";
echo "<ul>";
echo "<li>✅ Student records</li>";
echo "<li>✅ Result records</li>";
echo "<li>✅ Plugin settings</li>";
echo "<li>✅ License information</li>";
echo "<li>✅ Uploaded files</li>";
echo "<li>✅ All plugin data</li>";
echo "</ul>";
echo "<p><strong>If you reinstall the plugin, it will start completely fresh with no previous data.</strong></p>";
?>