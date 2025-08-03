<?php
/**
 * Test Domain-Bound License System
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🔒 Test Domain-Bound License System</h2>";

// Include necessary files
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';

$license_manager = new SRM_License_Manager();

echo "<h3>✅ 1. Current System Status</h3>";

$license_info = $license_manager->get_license_info();
$domain_info = $license_manager->get_domain_info();

echo "<div style='background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);'>";
echo "<h4>License Information:</h4>";
echo "<ul>";
echo "<li><strong>License Key:</strong> " . ($license_info['key'] ?: 'None') . "</li>";
echo "<li><strong>License Status:</strong> " . ucfirst($license_info['status']) . "</li>";
echo "<li><strong>Has License File:</strong> " . ($license_info['has_file'] ? 'Yes' : 'No') . "</li>";
echo "<li><strong>Is Owner:</strong> " . ($license_info['is_owner'] ? 'Yes' : 'No') . "</li>";
echo "<li><strong>Has Premium Access:</strong> " . ($license_info['has_premium'] ? 'Yes' : 'No') . "</li>";
echo "</ul>";

echo "<h4>Domain Information:</h4>";
echo "<ul>";
echo "<li><strong>Current Domain:</strong> " . esc_html($domain_info['domain']) . "</li>";
echo "<li><strong>Full URL:</strong> " . esc_html($domain_info['full_url']) . "</li>";
echo "<li><strong>Server Name:</strong> " . esc_html($domain_info['server_name']) . "</li>";
echo "<li><strong>HTTP Host:</strong> " . esc_html($domain_info['http_host']) . "</li>";
echo "</ul>";
echo "</div>";

echo "<h3>✅ 2. License File System Test</h3>";

$license_file_path = $license_manager->get_license_file_path();
echo "<p><strong>License File Path:</strong> " . esc_html($license_file_path) . "</p>";

if (file_exists($license_file_path)) {
    $license_content = file_get_contents($license_file_path);
    echo "<div class='notice notice-success'>";
    echo "<p><strong>✅ License file exists</strong></p>";
    echo "<p><strong>Content:</strong> " . esc_html($license_content) . "</p>";
    echo "</div>";
} else {
    echo "<div class='notice notice-warning'>";
    echo "<p><strong>⚠️ License file does not exist</strong></p>";
    echo "<p>This is normal for free users or when no license is activated.</p>";
    echo "</div>";
}

echo "<h3>✅ 3. Domain-Bound License Test</h3>";

if (isset($_POST['test_license'])) {
    $test_license = sanitize_text_field($_POST['test_license']);
    
    echo "<h4>Testing License: " . esc_html($test_license) . "</h4>";
    
    // Test license format validation
    $is_valid_format = $license_manager->validate_license_format($test_license);
    echo "<p><strong>Valid Format:</strong> " . ($is_valid_format ? 'Yes' : 'No') . "</p>";
    
    // Test domain-bound validation
    $is_valid_domain = $license_manager->is_valid_domain_license($test_license);
    echo "<p><strong>Valid for Current Domain:</strong> " . ($is_valid_domain ? 'Yes' : 'No') . "</p>";
    
    // Test activation
    $activation_result = $license_manager->activate_license($test_license);
    echo "<p><strong>Activation Result:</strong> " . esc_html($activation_result['message']) . "</p>";
    
    if ($activation_result['success']) {
        echo "<div class='notice notice-success'>";
        echo "<p><strong>✅ License activated successfully!</strong></p>";
        echo "</div>";
        
        // Clean up - deactivate the test license
        $license_manager->deactivate_license();
        echo "<p><em>Test license deactivated for cleanup.</em></p>";
    } else {
        echo "<div class='notice notice-error'>";
        echo "<p><strong>❌ License activation failed</strong></p>";
        echo "</div>";
    }
} else {
    echo "<form method='post'>";
    echo "<h4>Test Domain-Bound License:</h4>";
    echo "<table class='form-table'>";
    echo "<tr><th>Test License Key:</th><td><input type='text' name='test_license' value='XYGh675*UGTFM." . esc_attr($domain_info['domain']) . "' style='width: 100%;' required></td></tr>";
    echo "</table>";
    echo "<p><input type='submit' value='Test License' class='button button-primary'></p>";
    echo "</form>";
}

echo "<h3>✅ 4. License Key Format Examples</h3>";

echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 20px; margin: 20px 0;'>";
echo "<h4>Valid License Key Formats:</h4>";
echo "<ul>";
echo "<li><strong>Owner Key:</strong> <code>Bismillah^512</code></li>";
echo "<li><strong>Domain-Bound Key:</strong> <code>XYGh675*UGTFM.example.com</code></li>";
echo "<li><strong>Domain-Bound Key:</strong> <code>ABC123!DEF456.mysite.com</code></li>";
echo "<li><strong>Domain-Bound Key:</strong> <code>XYZ789@GHI012.yourdomain.org</code></li>";
echo "</ul>";

echo "<h4>Invalid License Key Formats:</h4>";
echo "<ul>";
echo "<li><strong>Wrong Domain:</strong> <code>XYGh675*UGTFM.wrongdomain.com</code></li>";
echo "<li><strong>No Domain:</strong> <code>XYGh675*UGTFM</code></li>";
echo "<li><strong>Invalid Format:</strong> <code>123456789</code></li>";
echo "<li><strong>Empty Key:</strong> <code></code></li>";
echo "</ul>";
echo "</div>";

echo "<h3>✅ 5. License File Operations</h3>";

if (isset($_POST['create_test_file'])) {
    $test_key = 'XYGh675*UGTFM.' . $domain_info['domain'];
    file_put_contents($license_file_path, $test_key);
    
    echo "<div class='notice notice-success'>";
    echo "<p><strong>✅ Test license file created</strong></p>";
    echo "<p><strong>Content:</strong> " . esc_html($test_key) . "</p>";
    echo "</div>";
} elseif (isset($_POST['delete_test_file'])) {
    if (file_exists($license_file_path)) {
        unlink($license_file_path);
        echo "<div class='notice notice-success'>";
        echo "<p><strong>✅ Test license file deleted</strong></p>";
        echo "</div>";
    } else {
        echo "<div class='notice notice-warning'>";
        echo "<p><strong>⚠️ No license file to delete</strong></p>";
        echo "</div>";
    }
} else {
    echo "<form method='post' style='display: inline; margin-right: 10px;'>";
    echo "<input type='submit' name='create_test_file' value='Create Test License File' class='button button-secondary'>";
    echo "</form>";
    
    echo "<form method='post' style='display: inline;'>";
    echo "<input type='submit' name='delete_test_file' value='Delete License File' class='button button-secondary'>";
    echo "</form>";
}

echo "<h3>✅ 6. Manual Test Steps</h3>";
echo "<ol>";
echo "<li><strong>Test License File:</strong> Create a test license file using the button above</li>";
echo "<li><strong>Test Activation:</strong> Try activating the test license</li>";
echo "<li><strong>Test Domain Binding:</strong> Try activating a license for a different domain</li>";
echo "<li><strong>Test Owner Key:</strong> Try activating the owner key (Bismillah^512)</li>";
echo "<li><strong>Test Deactivation:</strong> Deactivate the license and verify it's removed</li>";
echo "<li><strong>Test License Request:</strong> Go to the Request License page</li>";
echo "</ol>";

echo "<h3>✅ 7. Expected Behavior</h3>";

echo "<div style='background: #e7f3ff; border-left: 4px solid #0073aa; padding: 20px; margin: 20px 0; border-radius: 4px;'>";
echo "<h4>Domain-Bound License System:</h4>";
echo "<ul>";
echo "<li><strong>License File:</strong> License keys are stored in a file (license.key)</li>";
echo "<li><strong>Domain Binding:</strong> Each license key is bound to a specific domain</li>";
echo "<li><strong>Format:</strong> XYGh675*UGTFM.yourdomain.com</li>";
echo "<li><strong>Validation:</strong> License is only valid on the specified domain</li>";
echo "<li><strong>Owner Key:</strong> Bismillah^512 works on any domain</li>";
echo "<li><strong>Security:</strong> License sharing between domains is prevented</li>";
echo "</ul>";
echo "</div>";

echo "<h3>🔗 Quick Test Links</h3>";
echo "<ul>";
echo "<li><a href='?page=srm-enhanced-premium'>Premium Features Page</a></li>";
echo "<li><a href='?page=srm-license-request'>Request License Page</a></li>";
echo "<li><a href='?page=srm-students'>Students Page</a></li>";
echo "<li><a href='?page=srm-results'>Results Page</a></li>";
echo "</ul>";

echo "<h3>📱 Contact Information</h3>";
echo "<p><strong>WhatsApp:</strong> <a href='https://wa.me/923083430923' target='_blank'>+923083430923</a></p>";
echo "<p><strong>Email:</strong> <a href='mailto:jaffar381996152@gmail.com'>jaffar381996152@gmail.com</a></p>";

echo "<h3>🎯 Summary</h3>";
echo "<p><strong>The new domain-bound license system provides:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>Enhanced Security:</strong> License keys are domain-specific</li>";
echo "<li>✅ <strong>License Files:</strong> Keys are stored in license.key files</li>";
echo "<li>✅ <strong>Domain Validation:</strong> Automatic domain checking</li>";
echo "<li>✅ <strong>Owner Access:</strong> Special owner key for full access</li>";
echo "<li>✅ <strong>Request System:</strong> Users can request licenses with domain info</li>";
echo "<li>✅ <strong>Prevent Sharing:</strong> License sharing between domains is blocked</li>";
echo "<li>✅ <strong>Easy Management:</strong> Simple file-based license management</li>";
echo "</ul>";

echo "<h3>🧪 To Test the New System</h3>";
echo "<ol>";
echo "<li><strong>Create Test File:</strong> Use the button above to create a test license file</li>";
echo "<li><strong>Test Activation:</strong> Try activating the test license</li>";
echo "<li><strong>Test Domain Binding:</strong> Try a license for a different domain</li>";
echo "<li><strong>Test Owner Key:</strong> Try the owner key (Bismillah^512)</li>";
echo "<li><strong>Test Request System:</strong> Go to Request License page</li>";
echo "<li><strong>Verify Security:</strong> Confirm licenses are domain-bound</li>";
echo "</ol>";
?>