<?php
/**
 * Test Contact Banner Design
 * This file can be deleted after testing
 */

if (!defined('ABSPATH')) exit;

echo "<h2>🎨 Test Contact Banner Design</h2>";

// Include license manager
require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
$license_manager = new SRM_License_Manager();

echo "<h3>✅ 1. Current License Status</h3>";
echo "<ul>";
echo "<li><strong>License Key:</strong> " . ($license_manager->get_license_key() ?: 'None') . "</li>";
echo "<li><strong>License Status:</strong> " . $license_manager->get_license_status() . "</li>";
echo "<li><strong>Has Premium Access:</strong> " . ($license_manager->has_premium_access() ? 'Yes' : 'No') . "</li>";
echo "</ul>";

echo "<h3>✅ 2. Contact Banner Display</h3>";

if (!$license_manager->has_premium_access()) {
    echo "<div class='notice notice-info'>";
    echo "<p><strong>✅ Contact banner should appear below for free users</strong></p>";
    echo "<p>The banner will show a beautiful gradient design with contact information.</p>";
    echo "</div>";
    
    // Display the actual banner
    echo '<div class="srm-contact-banner" style="
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px 30px;
        margin: 20px 0;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
        position: relative;
        overflow: hidden;
        border: none;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    ">';
    
    // Background pattern
    echo '<div style="
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 20px 20px;
        opacity: 0.3;
        pointer-events: none;
    "></div>';
    
    // Content
    echo '<div style="position: relative; z-index: 2;">';
    echo '<h2 style="
        margin: 0 0 20px 0;
        color: white;
        font-size: 28px;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    ">📞 Contact for Premium Version</h2>';
    
    echo '<p style="
        margin: 0 0 25px 0;
        font-size: 18px;
        font-weight: 500;
        opacity: 0.95;
    "><strong>To buy the full Version, Contact Jaffar Abbas:</strong></p>';
    
    echo '<div style="
        display: flex;
        justify-content: center;
        gap: 40px;
        margin-top: 20px;
        flex-wrap: wrap;
    ">';
    
    // WhatsApp
    echo '<div style="
        text-align: center;
        background: rgba(255,255,255,0.15);
        padding: 15px 25px;
        border-radius: 10px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        transition: all 0.3s ease;
    ">';
    echo '<div style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">📱 WhatsApp</div>';
    echo '<a href="https://wa.me/923083430923" target="_blank" style="
        font-size: 20px;
        color: white;
        text-decoration: none;
        font-weight: 700;
        display: block;
        padding: 8px 0;
        border-radius: 6px;
        transition: all 0.3s ease;
    ">+923083430923</a>';
    echo '</div>';
    
    // Email
    echo '<div style="
        text-align: center;
        background: rgba(255,255,255,0.15);
        padding: 15px 25px;
        border-radius: 10px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        transition: all 0.3s ease;
    ">';
    echo '<div style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">📧 Email</div>';
    echo '<a href="mailto:jaffar381996152@gmail.com" style="
        font-size: 18px;
        color: white;
        text-decoration: none;
        font-weight: 700;
        display: block;
        padding: 8px 0;
        border-radius: 6px;
        transition: all 0.3s ease;
        word-break: break-all;
    ">jaffar381996152@gmail.com</a>';
    echo '</div>';
    
    echo '</div>';
    
    // Close button
    echo '<button onclick="this.parentElement.style.display=\'none\'" style="
        position: absolute;
        top: 15px;
        right: 20px;
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        padding: 5px 10px;
        border-radius: 50%;
        transition: all 0.3s ease;
        z-index: 3;
    " title="Close banner">×</button>';
    
    echo '</div>';
    echo '</div>';
    
    // Add hover effects via CSS
    echo '<style>
    .srm-contact-banner:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 40px rgba(102, 126, 234, 0.4);
    }
    .srm-contact-banner > div > div > div:hover {
        background: rgba(255,255,255,0.25) !important;
        transform: translateY(-2px);
    }
    .srm-contact-banner button:hover {
        background: rgba(255,255,255,0.3) !important;
    }
    @media (max-width: 768px) {
        .srm-contact-banner {
            padding: 20px 15px !important;
        }
        .srm-contact-banner h2 {
            font-size: 24px !important;
        }
        .srm-contact-banner > div > div {
            gap: 20px !important;
        }
        .srm-contact-banner > div > div > div {
            padding: 12px 15px !important;
        }
    }
    </style>';
    
} else {
    echo "<div class='notice notice-success'>";
    echo "<p><strong>ℹ️ You have premium access, so contact banner is hidden</strong></p>";
    echo "<p>To see the banner, deactivate your license in the Premium Features page.</p>";
    echo "</div>";
}

echo "<h3>✅ 3. Banner Design Features</h3>";
echo "<ul>";
echo "<li>✅ <strong>Beautiful Gradient:</strong> Purple to blue gradient background</li>";
echo "<li>✅ <strong>Glass Morphism:</strong> Semi-transparent contact cards with blur effect</li>";
echo "<li>✅ <strong>Hover Effects:</strong> Subtle animations on hover</li>";
echo "<li>✅ <strong>Close Button:</strong> X button to dismiss the banner</li>";
echo "<li>✅ <strong>Responsive Design:</strong> Adapts to mobile screens</li>";
echo "<li>✅ <strong>No Overlap:</strong> Single banner that doesn't interfere with content</li>";
echo "<li>✅ <strong>Professional Typography:</strong> Modern font stack</li>";
echo "<li>✅ <strong>Subtle Patterns:</strong> Background dot pattern for texture</li>";
echo "</ul>";

echo "<h3>✅ 4. Banner Behavior</h3>";
echo "<ul>";
echo "<li>✅ <strong>Free Users:</strong> Banner appears on all plugin admin pages</li>";
echo "<li>✅ <strong>Premium Users:</strong> Banner is hidden completely</li>";
echo "<li>✅ <strong>Closeable:</strong> Users can dismiss the banner</li>";
echo "<li>✅ <strong>Non-Intrusive:</strong> Doesn't block or overlap content</li>";
echo "<li>✅ <strong>Eye-Catching:</strong> Prominent but not annoying</li>";
echo "</ul>";

echo "<h3>✅ 5. Design Improvements</h3>";
echo "<ul>";
echo "<li>✅ <strong>Single Banner:</strong> Removed overlapping banners</li>";
echo "<li>✅ <strong>Better Positioning:</strong> Appears in WordPress admin notices area</li>";
echo "<li>✅ <strong>Modern Design:</strong> Gradient background with glass morphism</li>";
echo "<li>✅ <strong>Interactive Elements:</strong> Hover effects and close button</li>";
echo "<li>✅ <strong>Mobile Friendly:</strong> Responsive design for all screen sizes</li>";
echo "<li>✅ <strong>Accessibility:</strong> Clear contrast and readable text</li>";
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
echo "<p><strong>The contact banner has been redesigned with:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>Single Banner:</strong> No more overlapping banners</li>";
echo "<li>✅ <strong>Beautiful Design:</strong> Modern gradient with glass morphism</li>";
echo "<li>✅ <strong>Non-Intrusive:</strong> Doesn't hide or overlap content</li>";
echo "<li>✅ <strong>Interactive:</strong> Hover effects and close button</li>";
echo "<li>✅ <strong>Responsive:</strong> Works on all screen sizes</li>";
echo "<li>✅ <strong>Professional:</strong> Clean, modern appearance</li>";
echo "</ul>";

echo "<h3>🧪 To Test Banner</h3>";
echo "<ol>";
echo "<li><strong>Free Users:</strong> Banner should appear on all plugin pages</li>";
echo "<li><strong>Premium Users:</strong> Deactivate license to see banner</li>";
echo "<li><strong>Close Button:</strong> Click × to dismiss banner</li>";
echo "<li><strong>Hover Effects:</strong> Hover over contact cards to see animations</li>";
echo "<li><strong>Mobile Test:</strong> Resize browser to test responsive design</li>";
echo "</ol>";
?>