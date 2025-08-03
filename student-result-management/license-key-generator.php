<?php
/**
 * License Key Generator
 * This file can be deleted after use
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🔑 License Key Generator</h2>";

if (isset($_POST['generate_license'])) {
    $customer_name = sanitize_text_field($_POST['customer_name']);
    $domain_name = sanitize_text_field($_POST['domain_name']);
    
    // Generate license key following the 13-digit format
    $license_key = generate_13_digit_key($domain_name);
    
    echo "<div style='background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);'>";
    echo "<h3>✅ License Key Generated</h3>";
    echo "<p><strong>Customer:</strong> " . esc_html($customer_name) . "</p>";
    echo "<p><strong>Domain:</strong> " . esc_html($domain_name) . "</p>";
    echo "<p><strong>License Key:</strong> <code style='background: #f0f0f0; padding: 5px; border-radius: 3px;'>" . esc_html($license_key) . "</code></p>";
    echo "</div>";
    
    // Generate license file content
    $license_file_content = $license_key;
    
    echo "<div style='background: #e7f3ff; border-left: 4px solid #0073aa; padding: 20px; margin: 20px 0; border-radius: 4px;'>";
    echo "<h4>📄 License File Content</h4>";
    echo "<p>Create a file named <code>license.key</code> with this content:</p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6;'>" . esc_html($license_file_content) . "</pre>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 4px;'>";
    echo "<h4>📋 Instructions for Customer</h4>";
    echo "<ol>";
    echo "<li>Create a text file named <code>license.key</code></li>";
    echo "<li>Put this content in the file: <code>" . esc_html($license_key) . "</code></li>";
    echo "<li>Upload the file to: <code>/wp-content/plugins/student-result-management/</code></li>";
    echo "<li>Go to WordPress Admin → Student Results → Premium Features</li>";
    echo "<li>Activate the license</li>";
    echo "<li>Premium features will be unlocked</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='background: #d4edda; border-left: 4px solid #28a745; padding: 20px; margin: 20px 0; border-radius: 4px;'>";
    echo "<h4>📧 Email Template for Customer</h4>";
    echo "<p><strong>Subject:</strong> Your Premium License Key - Student Result Management</p>";
    echo "<p><strong>Message:</strong></p>";
    echo "<p>Dear " . esc_html($customer_name) . ",</p>";
    echo "<p>Thank you for your license request. Here is your premium license key:</p>";
    echo "<p><strong>License Key:</strong> <code>" . esc_html($license_key) . "</code></p>";
    echo "<p><strong>Installation Instructions:</strong></p>";
    echo "<ol>";
    echo "<li>Create a text file named <code>license.key</code></li>";
    echo "<li>Put the license key in the file</li>";
    echo "<li>Upload the file to your plugin directory: <code>/wp-content/plugins/student-result-management/</code></li>";
    echo "<li>Go to WordPress Admin → Student Results → Premium Features</li>";
    echo "<li>Activate the license</li>";
    echo "<li>Enjoy your premium features!</li>";
    echo "</ol>";
    echo "<p>If you need any assistance, please contact us.</p>";
    echo "<p>Best regards,<br>Jaffar Abbas</p>";
    echo "</div>";
    
} else {
    echo "<form method='post'>";
    echo "<div style='background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);'>";
    echo "<h3>🔑 Generate License Key</h3>";
    echo "<table class='form-table'>";
    echo "<tr><th>Customer Name:</th><td><input type='text' name='customer_name' required style='width: 100%;'></td></tr>";
    echo "<tr><th>Domain Name:</th><td><input type='text' name='domain_name' placeholder='example.com' required style='width: 100%;'></td></tr>";
    echo "</table>";
    echo "<p><input type='submit' name='generate_license' value='Generate License Key' class='button button-primary'></p>";
    echo "</div>";
    echo "</form>";
}

echo "<h3>📋 License Key Format (13-Digit)</h3>";
echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 20px; margin: 20px 0;'>";
echo "<h4>Format: <code>XXXXXXXXXXXXX.DOMAIN.COM</code></h4>";
echo "<ul>";
echo "<li><strong>First letter:</strong> B, J, N, A, F, or T</li>";
echo "<li><strong>4th letter:</strong> X, G, K, D, E, or P</li>";
echo "<li><strong>8th, 9th, or 10th letter:</strong> Special character (!@#$%^&* etc.)</li>";
echo "<li><strong>13th letter:</strong> B, G, N, K, F, or P</li>";
echo "<li><strong>Domain:</strong> Customer's domain name</li>";
echo "<li><strong>Example:</strong> <code>BJKmNpQrStU*example.com</code></li>";
echo "</ul>";
echo "</div>";

echo "<h3>🔒 Security Features</h3>";
echo "<div style='background: #e7f3ff; border-left: 4px solid #0073aa; padding: 20px; margin: 20px 0; border-radius: 4px;'>";
echo "<ul>";
echo "<li><strong>Domain-Bound:</strong> Each license works only on the specified domain</li>";
echo "<li><strong>Unique Keys:</strong> Each customer gets a unique license key</li>";
echo "<li><strong>File-Based:</strong> License keys are stored in secure files</li>";
echo "<li><strong>No Sharing:</strong> License sharing between domains is prevented</li>";
echo "</ul>";
echo "</div>";

echo "<h3>📱 Contact Information</h3>";
echo "<p><strong>WhatsApp:</strong> <a href='https://wa.me/923083430923' target='_blank'>+923083430923</a></p>";
echo "<p><strong>Email:</strong> <a href='mailto:jaffar381996152@gmail.com'>jaffar381996152@gmail.com</a></p>";

echo "<h3>🎯 Summary</h3>";
echo "<p><strong>License Key Creation Process:</strong></p>";
echo "<ol>";
echo "<li><strong>Receive Request:</strong> Customer submits license request</li>";
echo "<li><strong>Generate Key:</strong> Use this tool to create domain-bound key</li>";
echo "<li><strong>Create File:</strong> Create license.key file with the key</li>";
echo "<li><strong>Send to Customer:</strong> Email the license file and instructions</li>";
echo "<li><strong>Customer Installs:</strong> Customer uploads file and activates</li>";
echo "<li><strong>Premium Unlocked:</strong> All premium features available</li>";
echo "</ol>";

// Function to generate 13-digit license key
function generate_13_digit_key($domain) {
    // Valid characters for different positions
    $first_letters = array('B', 'J', 'N', 'A', 'F', 'T');
    $fourth_letters = array('X', 'G', 'K', 'D', 'E', 'P');
    $special_chars = array('!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '+', '=', '[', ']', '{', '}', '|', '\\', ':', ';', '"', '\'', '<', '>', ',', '.', '?', '/');
    $last_letters = array('B', 'G', 'N', 'K', 'F', 'P');
    $all_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    
    // Generate the key
    $key = '';
    
    // First letter (B, J, N, A, F, or T)
    $key .= $first_letters[array_rand($first_letters)];
    
    // 2nd and 3rd letters (any character)
    $key .= $all_chars[array_rand(str_split($all_chars))];
    $key .= $all_chars[array_rand(str_split($all_chars))];
    
    // 4th letter (X, G, K, D, E, or P)
    $key .= $fourth_letters[array_rand($fourth_letters)];
    
    // 5th, 6th, 7th letters (any character)
    $key .= $all_chars[array_rand(str_split($all_chars))];
    $key .= $all_chars[array_rand(str_split($all_chars))];
    $key .= $all_chars[array_rand(str_split($all_chars))];
    
    // 8th letter (special character)
    $key .= $special_chars[array_rand($special_chars)];
    
    // 9th, 10th, 11th letters (any character)
    $key .= $all_chars[array_rand(str_split($all_chars))];
    $key .= $all_chars[array_rand(str_split($all_chars))];
    $key .= $all_chars[array_rand(str_split($all_chars))];
    
    // 13th letter (B, G, N, K, F, or P)
    $key .= $last_letters[array_rand($last_letters)];
    
    // Add domain
    $key .= '.' . $domain;
    
    return $key;
}
?>