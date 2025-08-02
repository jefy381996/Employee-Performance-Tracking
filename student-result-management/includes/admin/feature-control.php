<?php
/**
 * Feature Control System for Student Result Management
 * Controls access to premium features based on license status
 */

if (!defined('ABSPATH')) exit;

class SRM_Feature_Control {
    
    private $license_manager;
    
    public function __construct() {
        require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
        $this->license_manager = new SRM_License_Manager();
        
        // Hook into various features to control access
        add_action('admin_init', array($this, 'check_premium_features'));
        add_action('wp_ajax_srm_generate_pdf', array($this, 'check_pdf_access'));
        add_action('wp_ajax_srm_upload_csv', array($this, 'check_csv_access'));
        add_action('admin_notices', array($this, 'premium_feature_notices'));
        add_filter('srm_feature_access', array($this, 'check_feature_access'), 10, 2);
    }
    
    /**
     * Check if user has access to a specific feature
     */
    public function has_feature_access($feature) {
        // Plugin owner always has access
        if ($this->license_manager->is_plugin_owner()) {
            return true;
        }
        
        // Check if user has premium access
        if (!$this->license_manager->has_premium_access()) {
            return false;
        }
        
        // Define premium features
        $premium_features = array(
            'pdf_generation',
            'csv_import_export',
            'student_images',
            'advanced_analytics',
            'email_notifications',
            'data_backup_restore',
            'custom_templates',
            'priority_support'
        );
        
        return in_array($feature, $premium_features);
    }
    
    /**
     * Check premium features on admin init
     */
    public function check_premium_features() {
        // Only check on SRM admin pages
        if (!isset($_GET['page']) || strpos($_GET['page'], 'srm') === false) {
            return;
        }
        
        // Check if user is trying to access premium features
        $current_page = sanitize_text_field($_GET['page']);
        
        switch ($current_page) {
            case 'srm-import-export':
                if (!$this->has_feature_access('csv_import_export')) {
                    wp_redirect(admin_url('admin.php?page=srm-premium&feature=csv_import_export'));
                    exit;
                }
                break;
        }
    }
    
    /**
     * Check PDF generation access
     */
    public function check_pdf_access() {
        if (!$this->has_feature_access('pdf_generation')) {
            wp_send_json_error(array(
                'message' => 'PDF generation is a premium feature. Please upgrade to access this feature.',
                'redirect' => admin_url('admin.php?page=srm-premium&feature=pdf_generation')
            ));
        }
    }
    
    /**
     * Check CSV import/export access
     */
    public function check_csv_access() {
        if (!$this->has_feature_access('csv_import_export')) {
            wp_send_json_error(array(
                'message' => 'CSV import/export is a premium feature. Please upgrade to access this feature.',
                'redirect' => admin_url('admin.php?page=srm-premium&feature=csv_import_export')
            ));
        }
    }
    
    /**
     * Show premium feature notices
     */
    public function premium_feature_notices() {
        // Only show on SRM admin pages
        if (!isset($_GET['page']) || strpos($_GET['page'], 'srm') === false) {
            return;
        }
        
        // Check if user is plugin owner
        if ($this->license_manager->is_plugin_owner()) {
            return;
        }
        
        // Show upgrade notice for non-premium users
        if (!$this->license_manager->has_premium_access()) {
            $current_page = sanitize_text_field($_GET['page']);
            
            // Don't show notice on premium page itself
            if ($current_page === 'srm-premium') {
                return;
            }
            
            echo '<div class="notice notice-warning">';
            echo '<p><strong>' . __('Premium Features Available!', 'student-result-management') . '</strong> ';
            echo __('You are currently using the free version. ', 'student-result-management');
            echo '<a href="' . admin_url('admin.php?page=srm-premium') . '" class="button button-primary">';
            echo __('Upgrade to Premium', 'student-result-management');
            echo '</a></p>';
            echo '</div>';
        }
    }
    
    /**
     * Filter for feature access
     */
    public function check_feature_access($has_access, $feature) {
        return $this->has_feature_access($feature);
    }
    
    /**
     * Get feature status for display
     */
    public function get_feature_status($feature) {
        if ($this->license_manager->is_plugin_owner()) {
            return array(
                'status' => 'available',
                'message' => 'Available (Plugin Owner)',
                'icon' => 'dashicons-yes-alt'
            );
        }
        
        if ($this->has_feature_access($feature)) {
            return array(
                'status' => 'available',
                'message' => 'Available (Premium)',
                'icon' => 'dashicons-yes'
            );
        }
        
        return array(
            'status' => 'locked',
            'message' => 'Premium Feature',
            'icon' => 'dashicons-lock'
        );
    }
    
    /**
     * Get all features with their status
     */
    public function get_all_features_status() {
        $features = array(
            'student_management' => array(
                'name' => __('Student Management', 'student-result-management'),
                'description' => __('Add, edit, and manage student details', 'student-result-management'),
                'free' => true
            ),
            'result_management' => array(
                'name' => __('Result Management', 'student-result-management'),
                'description' => __('Add and manage exam results', 'student-result-management'),
                'free' => true
            ),
            'result_lookup' => array(
                'name' => __('Result Lookup', 'student-result-management'),
                'description' => __('Public result lookup by roll number', 'student-result-management'),
                'free' => true
            ),
            'basic_export' => array(
                'name' => __('Basic Export', 'student-result-management'),
                'description' => __('Export student data as CSV', 'student-result-management'),
                'free' => true
            ),
            'dashboard_analytics' => array(
                'name' => __('Dashboard Analytics', 'student-result-management'),
                'description' => __('View statistics and recent activity', 'student-result-management'),
                'free' => true
            ),
            'pdf_generation' => array(
                'name' => __('PDF Result Cards', 'student-result-management'),
                'description' => __('Generate beautiful PDF result cards', 'student-result-management'),
                'free' => false
            ),
            'csv_import_export' => array(
                'name' => __('CSV Import/Export', 'student-result-management'),
                'description' => __('Bulk import students and results via CSV', 'student-result-management'),
                'free' => false
            ),
            'student_images' => array(
                'name' => __('Student Profile Images', 'student-result-management'),
                'description' => __('Upload and manage student photos', 'student-result-management'),
                'free' => false
            ),
            'advanced_analytics' => array(
                'name' => __('Advanced Analytics', 'student-result-management'),
                'description' => __('Detailed performance reports and charts', 'student-result-management'),
                'free' => false
            ),
            'email_notifications' => array(
                'name' => __('Email Notifications', 'student-result-management'),
                'description' => __('Automated result notifications', 'student-result-management'),
                'free' => false
            ),
            'data_backup_restore' => array(
                'name' => __('Data Backup & Restore', 'student-result-management'),
                'description' => __('Complete data management tools', 'student-result-management'),
                'free' => false
            ),
            'custom_templates' => array(
                'name' => __('Custom Templates', 'student-result-management'),
                'description' => __('Multiple result card templates', 'student-result-management'),
                'free' => false
            ),
            'priority_support' => array(
                'name' => __('Priority Support', 'student-result-management'),
                'description' => __('Advanced user permissions', 'student-result-management'),
                'free' => false
            )
        );
        
        $features_with_status = array();
        foreach ($features as $key => $feature) {
            $features_with_status[$key] = array_merge($feature, $this->get_feature_status($key));
        }
        
        return $features_with_status;
    }
    
    /**
     * Show feature upgrade prompt
     */
    public function show_upgrade_prompt($feature) {
        $feature_names = array(
            'pdf_generation' => __('PDF Result Cards', 'student-result-management'),
            'csv_import_export' => __('CSV Import/Export', 'student-result-management'),
            'student_images' => __('Student Profile Images', 'student-result-management'),
            'advanced_analytics' => __('Advanced Analytics', 'student-result-management'),
            'email_notifications' => __('Email Notifications', 'student-result-management'),
            'data_backup_restore' => __('Data Backup & Restore', 'student-result-management'),
            'custom_templates' => __('Custom Templates', 'student-result-management'),
            'priority_support' => __('Priority Support', 'student-result-management')
        );
        
        $feature_name = isset($feature_names[$feature]) ? $feature_names[$feature] : $feature;
        
        echo '<div class="srm-upgrade-prompt">';
        echo '<div class="srm-upgrade-content">';
        echo '<h3>' . sprintf(__('Upgrade Required for %s', 'student-result-management'), $feature_name) . '</h3>';
        echo '<p>' . __('This feature is only available with a premium license. Upgrade now to unlock all premium features.', 'student-result-management') . '</p>';
        echo '<div class="srm-upgrade-actions">';
        echo '<a href="' . admin_url('admin.php?page=srm-premium') . '" class="button button-primary">';
        echo __('Upgrade to Premium', 'student-result-management');
        echo '</a>';
        echo '<a href="' . admin_url('admin.php?page=student-results') . '" class="button button-secondary">';
        echo __('Go Back', 'student-result-management');
        echo '</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<style>
        .srm-upgrade-prompt {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            margin: 20px 0;
        }
        .srm-upgrade-content h3 {
            color: #0073aa;
            margin-bottom: 20px;
        }
        .srm-upgrade-actions {
            margin-top: 30px;
        }
        .srm-upgrade-actions .button {
            margin: 0 10px;
        }
        </style>';
    }
}

// Initialize feature control
$srm_feature_control = new SRM_Feature_Control();