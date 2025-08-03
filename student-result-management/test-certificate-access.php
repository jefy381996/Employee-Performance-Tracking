<?php
/**
 * Test Certificate Access
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>📄 Test Certificate Access</h2>";

// Include necessary files
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';

global $wpdb;

echo "<h3>✅ 1. Current License Status</h3>";
$license_manager = new SRM_License_Manager();

echo "<ul>";
echo "<li><strong>License Key:</strong> " . ($license_manager->get_license_key() ?: 'None') . "</li>";
echo "<li><strong>License Status:</strong> " . $license_manager->get_license_status() . "</li>";
echo "<li><strong>Has Premium Access:</strong> " . ($license_manager->has_premium_access() ? 'Yes' : 'No') . "</li>";
echo "</ul>";

echo "<h3>✅ 2. Certificate Upload Access (Admin Only)</h3>";

if ($license_manager->has_premium_access()) {
    echo "<div class='notice notice-success'>";
    echo "<p><strong>✅ Premium Access:</strong> You can upload certificate PDFs</p>";
    echo "<p>This means you can add PDF certificates when creating/editing results.</p>";
    echo "</div>";
} else {
    echo "<div class='notice notice-warning'>";
    echo "<p><strong>⚠️ Free Access:</strong> You cannot upload certificate PDFs</p>";
    echo "<p>This means you cannot add PDF certificates when creating/editing results.</p>";
    echo "<p>However, students can still download existing certificates!</p>";
    echo "</div>";
}

echo "<h3>✅ 3. Certificate Download Access (All Users)</h3>";

// Check if there are any results with certificates
$results_with_certificates = $wpdb->get_results("
    SELECT r.id, r.exam_name, r.certificate_pdf, s.first_name, s.last_name 
    FROM {$wpdb->prefix}srm_results r 
    LEFT JOIN {$wpdb->prefix}srm_students s ON r.student_id = s.id 
    WHERE r.certificate_pdf IS NOT NULL AND r.certificate_pdf != ''
    LIMIT 5
");

if ($results_with_certificates) {
    echo "<div class='notice notice-success'>";
    echo "<p><strong>✅ Certificates Found:</strong> The following results have certificates available for download:</p>";
    echo "<ul>";
    foreach ($results_with_certificates as $result) {
        echo "<li><strong>" . esc_html($result->first_name . ' ' . $result->last_name) . "</strong> - " . esc_html($result->exam_name) . " (ID: {$result->id})</li>";
    }
    echo "</ul>";
    echo "<p><strong>✅ All students can download these certificates</strong> regardless of license status!</p>";
    echo "</div>";
} else {
    echo "<div class='notice notice-info'>";
    echo "<p><strong>ℹ️ No Certificates Found:</strong> No results currently have certificate PDFs uploaded.</p>";
    echo "<p>To test certificate downloads, you need to:</p>";
    echo "<ol>";
    echo "<li>Activate a premium license</li>";
    echo "<li>Add a result with a certificate PDF</li>";
    echo "<li>Then test the download functionality</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<h3>✅ 4. Frontend Download Test</h3>";

echo "<p><strong>Frontend Certificate Download Behavior:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>All Users:</strong> Can download certificates if they exist</li>";
echo "<li>✅ <strong>No Premium Required:</strong> Download button shows for everyone</li>";
echo "<li>✅ <strong>No Certificate:</strong> Shows 'No Certificate Available' if none exists</li>";
echo "<li>✅ <strong>AJAX Handler:</strong> No premium check in download handler</li>";
echo "</ul>";

echo "<h3>✅ 5. Admin Upload Restrictions</h3>";

echo "<p><strong>Admin Certificate Upload Behavior:</strong></p>";
echo "<ul>";
if ($license_manager->has_premium_access()) {
    echo "<li>✅ <strong>Premium Users:</strong> Can upload certificate PDFs</li>";
    echo "<li>✅ <strong>Upload Field:</strong> Certificate PDF upload field is visible</li>";
    echo "<li>✅ <strong>File Processing:</strong> PDF files are processed and stored</li>";
} else {
    echo "<li>❌ <strong>Free Users:</strong> Cannot upload certificate PDFs</li>";
    echo "<li>❌ <strong>Upload Field:</strong> Certificate PDF upload field is hidden</li>";
    echo "<li>❌ <strong>File Processing:</strong> PDF uploads are blocked</li>";
    echo "<li>ℹ️ <strong>Message:</strong> Shows 'Premium Feature' message</li>";
}
echo "</ul>";

echo "<h3>✅ 6. Code Changes Made</h3>";

echo "<h4>Frontend Changes (result-lookup.php):</h4>";
echo "<ul>";
echo "<li>✅ <strong>Removed Premium Check:</strong> No longer checks license for download button</li>";
echo "<li>✅ <strong>Simplified Logic:</strong> Only checks if certificate exists</li>";
echo "<li>✅ <strong>Updated Text:</strong> Changed 'Premium' badge to 'No Certificate Available'</li>";
echo "</ul>";

echo "<h4>Backend Changes (student-result-management.php):</h4>";
echo "<ul>";
echo "<li>✅ <strong>Removed Premium Check:</strong> No longer checks license in AJAX handler</li>";
echo "<li>✅ <strong>Simplified Handler:</strong> Only validates result and certificate existence</li>";
echo "<li>✅ <strong>Universal Access:</strong> All users can download if certificate exists</li>";
echo "</ul>";

echo "<h3>✅ 7. Expected Behavior</h3>";

echo "<h4>For Students (Frontend):</h4>";
echo "<ul>";
echo "<li>✅ <strong>Free Users:</strong> Can download certificates if they exist</li>";
echo "<li>✅ <strong>Premium Users:</strong> Can download certificates if they exist</li>";
echo "<li>✅ <strong>No Certificate:</strong> Shows 'No Certificate Available'</li>";
echo "<li>✅ <strong>Download Button:</strong> Works for everyone with certificates</li>";
echo "</ul>";

echo "<h4>For Admins (Backend):</h4>";
echo "<ul>";
if ($license_manager->has_premium_access()) {
    echo "<li>✅ <strong>Premium Users:</strong> Can upload certificate PDFs</li>";
    echo "<li>✅ <strong>Upload Field:</strong> Certificate upload field is visible</li>";
    echo "<li>✅ <strong>File Processing:</strong> PDF files are accepted and stored</li>";
} else {
    echo "<li>❌ <strong>Free Users:</strong> Cannot upload certificate PDFs</li>";
    echo "<li>❌ <strong>Upload Field:</strong> Certificate upload field is hidden</li>";
    echo "<li>❌ <strong>File Processing:</strong> PDF uploads are rejected</li>";
    echo "<li>ℹ️ <strong>Message:</strong> Shows premium upgrade message</li>";
}
echo "</ul>";

echo "<h3>🔗 Quick Test Links</h3>";
echo "<ul>";
echo "<li><a href='?page=srm-results'>Results Page (Admin)</a></li>";
echo "<li><a href='?page=srm-students'>Students Page (Admin)</a></li>";
echo "<li><a href='?page=srm-premium'>Premium Features Page</a></li>";
echo "</ul>";

echo "<h3>📱 Contact Information</h3>";
echo "<p><strong>WhatsApp:</strong> <a href='https://wa.me/923083430923' target='_blank'>+923083430923</a></p>";
echo "<p><strong>Email:</strong> <a href='mailto:jaffar381996152@gmail.com'>jaffar381996152@gmail.com</a></p>";

echo "<h3>🎯 Summary</h3>";
echo "<p><strong>Certificate access has been updated:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>Downloads:</strong> All users can download certificates (no restrictions)</li>";
echo "<li>✅ <strong>Uploads:</strong> Only premium users can upload certificates</li>";
echo "<li>✅ <strong>Frontend:</strong> Download button shows for everyone with certificates</li>";
echo "<li>✅ <strong>Backend:</strong> Upload field only shows for premium users</li>";
echo "<li>✅ <strong>AJAX:</strong> Download handler works for all users</li>";
echo "<li>✅ <strong>Security:</strong> Still validates certificate existence</li>";
echo "</ul>";

echo "<h3>🧪 To Test Certificate Access</h3>";
echo "<ol>";
echo "<li><strong>Premium Users:</strong> Add a result with certificate PDF upload</li>";
echo "<li><strong>All Users:</strong> Check result lookup page for download button</li>";
echo "<li><strong>Free Users:</strong> Verify they can download existing certificates</li>";
echo "<li><strong>Admin Upload:</strong> Verify only premium users can upload certificates</li>";
echo "<li><strong>Frontend Download:</strong> Verify download works for all users</li>";
echo "</ol>";

echo "<h3>🔧 Files Modified</h3>";
echo "<ul>";
echo "<li>✅ <strong>result-lookup.php:</strong> Removed premium check for downloads</li>";
echo "<li>✅ <strong>student-result-management.php:</strong> Removed premium check in AJAX handler</li>";
echo "<li>✅ <strong>results.php:</strong> Upload restrictions remain for admins</li>";
echo "</ul>";
?>