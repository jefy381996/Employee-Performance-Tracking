<?php
/**
 * Plugin Name: Student Result Management System
 * Plugin URI: https://yourwebsite.com/student-result-management
 * Description: A comprehensive student result management system with free and premium features. Manage student records, marks, and generate beautiful result cards.
 * Version: 2.0
 * Author: M. Jaffar Abbas
 * License: GPL v2 or later
 * Text Domain: student-result-management
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SRM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SRM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SRM_PLUGIN_VERSION', '2.0');
define('SRM_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class StudentResultManagement {
    
    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('wp_ajax_srm_get_result', array($this, 'ajax_get_result'));
        add_action('wp_ajax_nopriv_srm_get_result', array($this, 'ajax_get_result'));
        add_action('wp_ajax_srm_upload_csv', array($this, 'ajax_upload_csv'));
        add_action('wp_ajax_srm_download_pdf', array($this, 'ajax_download_pdf'));
        add_action('wp_ajax_srm_create_tables', array($this, 'ajax_create_tables'));
        add_action('wp_ajax_srm_import_students_csv', array($this, 'ajax_import_students_csv'));
        add_action('wp_ajax_srm_import_results_csv', array($this, 'ajax_import_results_csv'));
        add_action('wp_ajax_srm_export_analytics', array($this, 'ajax_export_analytics'));
        add_action('wp_ajax_srm_preview_template', array($this, 'ajax_preview_template'));

        
        // Include license manager and feature control system
        require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
        require_once SRM_PLUGIN_PATH . 'includes/admin/feature-control.php';
        
        // License check on admin pages
        add_action('admin_init', array($this, 'check_license_on_admin'));
        
        // Display contact notice on all admin pages
        add_action('admin_notices', array($this, 'display_contact_notice'));
        add_action('admin_head', array($this, 'display_prominent_contact_notice'));
        add_action('admin_footer', array($this, 'force_contact_notice'));
        add_action('admin_init', array($this, 'inject_contact_notice'));
        
        // Shortcode for frontend result display
        add_shortcode('student_result_lookup', array($this, 'result_lookup_shortcode'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Force table creation
        $this->create_tables();
        $this->set_default_options();
        
        // Clear any existing license data to ensure fresh start
        delete_option('srm_license_key');
        delete_option('srm_license_status');
        delete_option('srm_plugin_owner');
        
        // Set default license status to free
        update_option('srm_license_key', '');
        update_option('srm_license_status', 'free');
        
        // Verify tables were created
        $this->verify_tables();
        
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear any transients
        delete_transient('srm_license_check');
        delete_transient('srm_analytics_cache');
        delete_transient('srm_notifications_cache');
        
        flush_rewrite_rules();
        
        // Note: Plugin data is retained on deactivation
        // To completely remove all data, uninstall the plugin
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        load_plugin_textdomain('student-result-management', false, dirname(SRM_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Students table
        $students_table = $wpdb->prefix . 'srm_students';
        $students_sql = "CREATE TABLE $students_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            roll_number varchar(50) NOT NULL UNIQUE,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100),
            phone varchar(20),
            class varchar(50) NOT NULL,
            section varchar(10),
            date_of_birth date,
            profile_image varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY roll_number (roll_number)
        ) $charset_collate;";
        
        // Results table
        $results_table = $wpdb->prefix . 'srm_results';
        $results_sql = "CREATE TABLE $results_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            student_id int(11) NOT NULL,
            exam_name varchar(100) NOT NULL,
            exam_date date,
            total_marks int(11) DEFAULT 0,
            obtained_marks int(11) DEFAULT 0,
            percentage decimal(5,2) DEFAULT 0.00,
            grade varchar(10),
            status enum('pass','fail','pending') DEFAULT 'pending',
            subjects text,
            certificate_pdf varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY student_id (student_id),
            FOREIGN KEY (student_id) REFERENCES $students_table(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Plugin settings table
        $settings_table = $wpdb->prefix . 'srm_settings';
        $settings_sql = "CREATE TABLE $settings_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            setting_name varchar(100) NOT NULL UNIQUE,
            setting_value text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Payments table
        $payments_table = $wpdb->prefix . 'srm_payments';
        $payments_sql = "CREATE TABLE $payments_table (
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
        ) $charset_collate;";
        
        // Notifications table
        $notifications_table = $wpdb->prefix . 'srm_notifications';
        $notifications_sql = "CREATE TABLE $notifications_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            student_name varchar(100) NOT NULL,
            notification_type varchar(50) NOT NULL,
            email varchar(100) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY notification_type (notification_type),
            KEY status (status),
            KEY email (email)
        ) $charset_collate;";
        
        // Templates table
        $templates_table = $wpdb->prefix . 'srm_templates';
        $templates_sql = "CREATE TABLE $templates_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            template_name varchar(100) NOT NULL,
            template_type varchar(50) NOT NULL,
            template_content longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY template_type (template_type),
            KEY template_name (template_name)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $students_result = dbDelta($students_sql);
        $results_result = dbDelta($results_sql);
        $settings_result = dbDelta($settings_sql);
        $payments_result = dbDelta($payments_sql);
        $notifications_result = dbDelta($notifications_sql);
        $templates_result = dbDelta($templates_sql);
        
        // Log any database creation issues
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('SRM Plugin: Students table creation result: ' . print_r($students_result, true));
            error_log('SRM Plugin: Results table creation result: ' . print_r($results_result, true));
            error_log('SRM Plugin: Settings table creation result: ' . print_r($settings_result, true));
            error_log('SRM Plugin: Payments table creation result: ' . print_r($payments_result, true));
        }
        
        // Force table creation if dbDelta didn't work
        $this->force_create_tables_if_missing();
    }
    
    /**
     * Force create tables if they don't exist
     */
    private function force_create_tables_if_missing() {
        global $wpdb;
        
        $tables = array(
            'students' => $wpdb->prefix . 'srm_students',
            'results' => $wpdb->prefix . 'srm_results',
            'settings' => $wpdb->prefix . 'srm_settings',
            'payments' => $wpdb->prefix . 'srm_payments',
            'notifications' => $wpdb->prefix . 'srm_notifications',
            'templates' => $wpdb->prefix . 'srm_templates'
        );
        
        foreach ($tables as $name => $table) {
            $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
            if (!$exists) {
                $this->create_individual_table($name);
            }
        }
    }
    
    /**
     * Create individual table with direct SQL
     */
    private function create_individual_table($table_name) {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        switch ($table_name) {
            case 'students':
                $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}srm_students (
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
                ) $charset_collate";
                break;
                
            case 'results':
                $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}srm_results (
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
                ) $charset_collate";
                break;
                
            case 'settings':
                $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}srm_settings (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    setting_key varchar(100) NOT NULL,
                    setting_value longtext,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY setting_key (setting_key)
                ) $charset_collate";
                break;
        }
        
        if (isset($sql)) {
            $result = $wpdb->query($sql);
            if ($result === false) {
                error_log("SRM Plugin: Failed to create {$table_name} table: " . $wpdb->last_error);
            } else {
                error_log("SRM Plugin: Successfully created {$table_name} table");
            }
        }
    }
    
    /**
     * Verify tables exist after creation
     */
    private function verify_tables() {
        global $wpdb;
        
        $required_tables = array(
            $wpdb->prefix . 'srm_students',
            $wpdb->prefix . 'srm_results', 
            $wpdb->prefix . 'srm_settings'
        );
        
        $missing_tables = array();
        
        foreach ($required_tables as $table) {
            $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
            if (!$exists) {
                $missing_tables[] = $table;
            }
        }
        
        if (!empty($missing_tables)) {
            $error_message = 'SRM Plugin: Failed to create tables: ' . implode(', ', $missing_tables);
            error_log($error_message);
            
            // Store error for admin notice
            update_option('srm_activation_error', $error_message);
        } else {
            // Clear any previous errors
            delete_option('srm_activation_error');
            error_log('SRM Plugin: All tables created successfully');
        }
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        global $wpdb;
        $settings_table = $wpdb->prefix . 'srm_settings';
        
        $default_settings = array(
            'license_key' => '',
            'license_status' => 'free',
            'school_name' => get_bloginfo('name'),
            'school_logo' => '',
            'admin_email' => get_option('admin_email'),
            'result_template' => 'default',
            'grade_system' => 'letter', // letter, number, percentage
            'passing_marks' => '40'
        );
        
        foreach ($default_settings as $name => $value) {
            $wpdb->replace($settings_table, array(
                'setting_name' => $name,
                'setting_value' => $value
            ));
        }
        
        // Don't set plugin owner automatically - user must activate license first
        // update_option('srm_plugin_owner', get_current_user_id());
    }
    
    /**
     * Admin menu
     */
    public function admin_menu() {
        $icon = 'dashicons-awards';
        
        add_menu_page(
            __('Student Results', 'student-result-management'),
            __('Student Results', 'student-result-management'),
            'manage_options',
            'student-results',
            array($this, 'admin_dashboard_page'),
            $icon,
            25
        );
        
        add_submenu_page(
            'student-results',
            __('Dashboard', 'student-result-management'),
            __('Dashboard', 'student-result-management'),
            'manage_options',
            'student-results',
            array($this, 'admin_dashboard_page')
        );
        
        add_submenu_page(
            'student-results',
            __('Students', 'student-result-management'),
            __('Students', 'student-result-management'),
            'manage_options',
            'srm-students',
            array($this, 'admin_students_page')
        );
        
        add_submenu_page(
            'student-results',
            __('Results', 'student-result-management'),
            __('Results', 'student-result-management'),
            'manage_options',
            'srm-results',
            array($this, 'admin_results_page')
        );
        
        // Premium Features
        // Premium features - only show to users with premium access
        $license_manager = new SRM_License_Manager();
        if ($license_manager->has_premium_access()) {
            add_submenu_page(
                'student-results',
                __('CSV Import/Export', 'student-result-management'),
                __('CSV Import/Export', 'student-result-management'),
                'manage_options',
                'srm-csv-import-export',
                array($this, 'admin_csv_import_export_page')
            );
            
            add_submenu_page(
                'student-results',
                __('Advanced Analytics', 'student-result-management'),
                __('Advanced Analytics', 'student-result-management'),
                'manage_options',
                'srm-advanced-analytics',
                array($this, 'admin_advanced_analytics_page')
            );
            
            add_submenu_page(
                'student-results',
                __('Email Notifications', 'student-result-management'),
                __('Email Notifications', 'student-result-management'),
                'manage_options',
                'srm-email-notifications',
                array($this, 'admin_email_notifications_page')
            );
            
            add_submenu_page(
                'student-results',
                __('Data Backup & Restore', 'student-result-management'),
                __('Data Backup & Restore', 'student-result-management'),
                'manage_options',
                'srm-data-backup-restore',
                array($this, 'admin_data_backup_restore_page')
            );
            
            add_submenu_page(
                'student-results',
                __('Custom Templates', 'student-result-management'),
                __('Custom Templates', 'student-result-management'),
                'manage_options',
                'srm-custom-templates',
                array($this, 'admin_custom_templates_page')
            );
        }
        

        
        add_submenu_page(
            'student-results',
            __('Settings', 'student-result-management'),
            __('Settings', 'student-result-management'),
            'manage_options',
            'srm-settings',
            array($this, 'admin_settings_page')
        );
        
        add_submenu_page(
            'student-results',
            __('Premium Features', 'student-result-management'),
            __('Premium Features', 'student-result-management'),
            'manage_options',
            'srm-premium',
            array($this, 'admin_premium_page')
        );
        

    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_scripts($hook) {
        if (strpos($hook, 'student-results') === false && strpos($hook, 'srm-') === false) {
            return;
        }
        
        wp_enqueue_style('srm-admin-style', SRM_PLUGIN_URL . 'assets/css/admin.css', array(), SRM_PLUGIN_VERSION);
        wp_enqueue_script('srm-admin-script', SRM_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), SRM_PLUGIN_VERSION, true);
        
        wp_localize_script('srm-admin-script', 'srm_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('srm_nonce'),
            'messages' => array(
                'confirm_delete' => __('Are you sure you want to delete this item?', 'student-result-management'),
                'processing' => __('Processing...', 'student-result-management'),
                'success' => __('Operation completed successfully!', 'student-result-management'),
                'error' => __('An error occurred. Please try again.', 'student-result-management')
            )
        ));
        
        // Enqueue media scripts for image uploads
        if ($this->is_premium_user()) {
            wp_enqueue_media();
        }
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function frontend_scripts() {
        wp_enqueue_style('srm-frontend-style', SRM_PLUGIN_URL . 'assets/css/frontend.css', array(), SRM_PLUGIN_VERSION);
        wp_enqueue_script('srm-frontend-script', SRM_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), SRM_PLUGIN_VERSION, true);
        
        wp_localize_script('srm-frontend-script', 'srm_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('srm_nonce')
        ));
    }
    
    /**
     * Check if current user is premium user or plugin owner
     */
    private function is_premium_user() {
        // Include license manager
        require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
        $license_manager = new SRM_License_Manager();
        return $license_manager->has_premium_access();
    }
    
    /**
     * Check license on admin pages
     */
    public function check_license_on_admin() {
        $license_manager = new SRM_License_Manager();
        $license_manager->force_license_check();
    }
    
    /**
     * Admin dashboard page
     */
    public function admin_dashboard_page() {
        include SRM_PLUGIN_PATH . 'includes/admin/dashboard.php';
    }
    
    /**
     * Admin students page
     */
    public function admin_students_page() {
        include SRM_PLUGIN_PATH . 'includes/admin/students.php';
    }
    
    /**
     * Admin results page
     */
    public function admin_results_page() {
        include SRM_PLUGIN_PATH . 'includes/admin/results.php';
    }
    
    /**
     * Admin import/export page
     */
    public function admin_import_export_page() {
        include SRM_PLUGIN_PATH . 'includes/admin/import-export.php';
    }
    
    /**
     * Admin settings page
     */
    public function admin_settings_page() {
        include SRM_PLUGIN_PATH . 'includes/admin/enhanced-settings.php';
    }
    
    /**
     * Admin premium page
     */
    public function admin_premium_page() {
        include SRM_PLUGIN_PATH . 'includes/admin/enhanced-premium.php';
    }
    
    public function admin_csv_import_export_page() {
        include SRM_PLUGIN_PATH . 'includes/admin/csv-import-export.php';
    }
    
    public function admin_advanced_analytics_page() {
        include SRM_PLUGIN_PATH . 'includes/admin/advanced-analytics.php';
    }
    
    public function admin_email_notifications_page() {
        include SRM_PLUGIN_PATH . 'includes/admin/email-notifications.php';
    }
    
    public function admin_data_backup_restore_page() {
        include SRM_PLUGIN_PATH . 'includes/admin/data-backup-restore.php';
    }
    
    public function admin_custom_templates_page() {
        include SRM_PLUGIN_PATH . 'includes/admin/custom-templates.php';
    }
    

    
    /**
     * AJAX handler for getting student result
     */
    public function ajax_get_result() {
        check_ajax_referer('srm_nonce', 'nonce');
        
        $roll_number = sanitize_text_field($_POST['roll_number']);
        
        if (empty($roll_number)) {
            wp_send_json_error(__('Please enter a roll number.', 'student-result-management'));
        }
        
        global $wpdb;
        
        $student = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}srm_students WHERE roll_number = %s",
            $roll_number
        ));
        
        if (!$student) {
            wp_send_json_error(__('No student found with this roll number.', 'student-result-management'));
        }
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}srm_results WHERE student_id = %d ORDER BY created_at DESC",
            $student->id
        ));
        
        if (empty($results)) {
            wp_send_json_error(__('No results found for this student.', 'student-result-management'));
        }
        
        $response = array(
            'student' => $student,
            'results' => $results
        );
        
        wp_send_json_success($response);
    }
    
    /**
     * AJAX handler for CSV upload (Premium feature)
     */
    public function ajax_upload_csv() {
        check_ajax_referer('srm_nonce', 'nonce');
        
        if (!$this->is_premium_user()) {
            wp_send_json_error(__('This is a premium feature. Please upgrade to access it.', 'student-result-management'));
        }
        
        if (!isset($_FILES['csv_file'])) {
            wp_send_json_error(__('No file uploaded.', 'student-result-management'));
        }
        
        $file = $_FILES['csv_file'];
        
        if ($file['type'] !== 'text/csv' && $file['type'] !== 'application/vnd.ms-excel') {
            wp_send_json_error(__('Please upload a valid CSV file.', 'student-result-management'));
        }
        
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            wp_send_json_error(__('Could not read the uploaded file.', 'student-result-management'));
        }
        
        global $wpdb;
        $students_table = $wpdb->prefix . 'srm_students';
        $results_table = $wpdb->prefix . 'srm_results';
        
        $headers = fgetcsv($handle);
        $imported = 0;
        $errors = 0;
        
        while (($data = fgetcsv($handle)) !== false) {
            try {
                $student_data = array_combine($headers, $data);
                
                // Insert or update student
                $wpdb->replace($students_table, array(
                    'roll_number' => sanitize_text_field($student_data['roll_number']),
                    'first_name' => sanitize_text_field($student_data['first_name']),
                    'last_name' => sanitize_text_field($student_data['last_name']),
                    'email' => sanitize_email($student_data['email']),
                    'class' => sanitize_text_field($student_data['class'])
                ));
                
                $imported++;
            } catch (Exception $e) {
                $errors++;
            }
        }
        
        fclose($handle);
        
        wp_send_json_success(array(
            'imported' => $imported,
            'errors' => $errors,
            'message' => sprintf(__('Successfully imported %d records with %d errors.', 'student-result-management'), $imported, $errors)
        ));
    }
    
    /**
     * AJAX handler for PDF download (Premium feature)
     */
    public function ajax_download_pdf() {
        check_ajax_referer('srm_nonce', 'nonce');
        
        // Check if user has premium access
        $license_manager = new SRM_License_Manager();
        if (!$license_manager->has_premium_access()) {
            wp_send_json_error(__('This is a premium feature. Please upgrade to access it.', 'student-result-management'));
        }
        
        $result_id = intval($_POST['result_id']);
        
        global $wpdb;
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}srm_results WHERE id = %d",
            $result_id
        ));
        
        if (!$result || empty($result->certificate_pdf)) {
            wp_send_json_error(__('No certificate PDF found for this result.', 'student-result-management'));
        }
        
        wp_send_json_success(array(
            'message' => __('PDF download ready!', 'student-result-management'),
            'download_url' => $result->certificate_pdf
        ));
    }
    
    /**
     * Frontend result lookup shortcode
     */
    public function result_lookup_shortcode($atts) {
        $atts = shortcode_atts(array(
            'style' => 'default'
        ), $atts);
        
        ob_start();
        include SRM_PLUGIN_PATH . 'includes/frontend/result-lookup.php';
        return ob_get_clean();
    }
    
    /**
     * Display contact information notice
     */
    public function display_contact_notice() {
        // Only show on plugin admin pages
        if (!isset($_GET['page']) || strpos($_GET['page'], 'srm-') === false) {
            return;
        }
        
        $license_manager = new SRM_License_Manager();
        if (!$license_manager->has_premium_access()) {
            echo '<div class="notice notice-info" style="margin: 20px 0; padding: 20px; background: #f0f8ff; border-left: 4px solid #0073aa; font-size: 16px; text-align: center;">';
            echo '<h2 style="margin: 0 0 15px 0; color: #0073aa; font-size: 20px;">📞 Contact for Premium Version</h2>';
            echo '<p style="margin: 0 0 10px 0; font-size: 16px;"><strong>To buy the full Version, Contact Jaffar Abbas:</strong></p>';
            echo '<div style="display: flex; justify-content: center; gap: 30px; margin-top: 15px;">';
            echo '<div style="text-align: center;"><strong>WhatsApp:</strong><br><a href="https://wa.me/923083430923" target="_blank" style="font-size: 18px; color: #0073aa;">+923083430923</a></div>';
            echo '<div style="text-align: center;"><strong>Email:</strong><br><a href="mailto:jaffar381996152@gmail.com" style="font-size: 18px; color: #0073aa;">jaffar381996152@gmail.com</a></div>';
            echo '</div>';
            echo '</div>';
        }
    }
    
    /**
     * Display prominent contact notice in admin header
     */
    public function display_prominent_contact_notice() {
        // Only show on plugin admin pages
        if (!isset($_GET['page']) || strpos($_GET['page'], 'srm-') === false) {
            return;
        }
        
        $license_manager = new SRM_License_Manager();
        if (!$license_manager->has_premium_access()) {
            echo '<style>
            .srm-contact-banner {
                position: fixed;
                top: 32px;
                left: 0;
                right: 0;
                background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
                color: white;
                padding: 15px 20px;
                text-align: center;
                z-index: 999999;
                box-shadow: 0 2px 10px rgba(0,0,0,0.3);
                font-size: 16px;
            }
            .srm-contact-banner h2 {
                margin: 0 0 10px 0;
                font-size: 20px;
                color: white;
            }
            .srm-contact-banner p {
                margin: 0 0 10px 0;
                font-size: 16px;
            }
            .srm-contact-banner .contact-links {
                display: flex;
                justify-content: center;
                gap: 30px;
                margin-top: 10px;
            }
            .srm-contact-banner .contact-links a {
                color: white;
                text-decoration: none;
                font-weight: bold;
                padding: 8px 16px;
                border: 2px solid white;
                border-radius: 5px;
                transition: all 0.3s ease;
            }
            .srm-contact-banner .contact-links a:hover {
                background: white;
                color: #0073aa;
            }
            .srm-contact-banner .close-banner {
                position: absolute;
                top: 10px;
                right: 15px;
                color: white;
                text-decoration: none;
                font-size: 20px;
                font-weight: bold;
            }
            </style>';
            
            echo '<div class="srm-contact-banner" id="srm-contact-banner">
                <a href="#" class="close-banner" onclick="document.getElementById(\'srm-contact-banner\').style.display=\'none\';">×</a>
                <h2>📞 Contact for Premium Version</h2>
                <p><strong>To buy the full Version, Contact Jaffar Abbas:</strong></p>
                <div class="contact-links">
                    <a href="https://wa.me/923083430923" target="_blank">📱 WhatsApp: +923083430923</a>
                    <a href="mailto:jaffar381996152@gmail.com">📧 Email: jaffar381996152@gmail.com</a>
                </div>
            </div>';
        }
    }
    
    /**
     * Inject contact notice directly into page content
     */
    public function inject_contact_notice() {
        // Only show on plugin admin pages
        if (!isset($_GET['page']) || strpos($_GET['page'], 'srm-') === false) {
            return;
        }
        
        $license_manager = new SRM_License_Manager();
        if (!$license_manager->has_premium_access()) {
            // Add CSS for the notice
            add_action('admin_head', function() {
                echo '<style>
                .srm-injected-notice {
                    background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
                    color: white;
                    padding: 20px;
                    margin: 20px 0;
                    border-radius: 8px;
                    text-align: center;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    position: relative;
                    z-index: 999999;
                }
                .srm-injected-notice h2 {
                    margin: 0 0 15px 0;
                    font-size: 24px;
                    color: white;
                }
                .srm-injected-notice p {
                    margin: 0 0 15px 0;
                    font-size: 18px;
                }
                .srm-injected-notice .contact-links {
                    display: flex;
                    justify-content: center;
                    gap: 30px;
                    margin-top: 20px;
                }
                .srm-injected-notice .contact-links a {
                    color: white;
                    text-decoration: none;
                    font-weight: bold;
                    padding: 12px 24px;
                    border: 2px solid white;
                    border-radius: 6px;
                    transition: all 0.3s ease;
                    font-size: 16px;
                }
                .srm-injected-notice .contact-links a:hover {
                    background: white;
                    color: #0073aa;
                }
                </style>';
            });
            
            // Add JavaScript to inject the notice
            add_action('admin_footer', function() {
                echo '<script>
                jQuery(document).ready(function($) {
                    var contactNotice = \'<div class="srm-injected-notice">\' +
                        \'<h2>📞 Contact for Premium Version</h2>\' +
                        \'<p><strong>To buy the full Version, Contact Jaffar Abbas:</strong></p>\' +
                        \'<div class="contact-links">\' +
                        \'<a href="https://wa.me/923083430923" target="_blank">📱 WhatsApp: +923083430923</a>\' +
                        \'<a href="mailto:jaffar381996152@gmail.com">📧 Email: jaffar381996152@gmail.com</a>\' +
                        \'</div>\' +
                        \'</div>\';
                    
                    // Insert at the very top of the page content
                    if ($("#wpcontent").length) {
                        $("#wpcontent").prepend(contactNotice);
                    } else if ($(".wrap").length) {
                        $(".wrap").prepend(contactNotice);
                    } else if ($("#wpbody").length) {
                        $("#wpbody").prepend(contactNotice);
                    } else {
                        $("body").prepend(contactNotice);
                    }
                });
                </script>';
            });
        }
    }
    
    /**
     * Force contact notice display (guaranteed to show)
     */
    public function force_contact_notice() {
        // Only show on plugin admin pages
        if (!isset($_GET['page']) || strpos($_GET['page'], 'srm-') === false) {
            return;
        }
        
        $license_manager = new SRM_License_Manager();
        if (!$license_manager->has_premium_access()) {
            echo '<script>
            jQuery(document).ready(function($) {
                // Create and insert contact notice
                var contactNotice = \'<div class="notice notice-info" style="margin: 20px 0; padding: 20px; background: #f0f8ff; border-left: 4px solid #0073aa; font-size: 16px; text-align: center; position: relative; z-index: 999999;">\' +
                    \'<h2 style="margin: 0 0 15px 0; color: #0073aa; font-size: 20px;">📞 Contact for Premium Version</h2>\' +
                    \'<p style="margin: 0 0 10px 0; font-size: 16px;"><strong>To buy the full Version, Contact Jaffar Abbas:</strong></p>\' +
                    \'<div style="display: flex; justify-content: center; gap: 30px; margin-top: 15px;">\' +
                    \'<div style="text-align: center;"><strong>WhatsApp:</strong><br><a href="https://wa.me/923083430923" target="_blank" style="font-size: 18px; color: #0073aa;">+923083430923</a></div>\' +
                    \'<div style="text-align: center;"><strong>Email:</strong><br><a href="mailto:jaffar381996152@gmail.com" style="font-size: 18px; color: #0073aa;">jaffar381996152@gmail.com</a></div>\' +
                    \'</div>\' +
                    \'</div>\';
                
                // Insert at the top of the page content
                if ($("#wpcontent").length) {
                    $("#wpcontent").prepend(contactNotice);
                } else if ($(".wrap").length) {
                    $(".wrap").prepend(contactNotice);
                } else {
                    $("body").prepend(contactNotice);
                }
            });
            </script>';
        }
    }
    
    /**
     * Display admin notices
     */
    public function admin_notices() {
        $error = get_option('srm_activation_error');
        if ($error) {
            ?>
            <div class="notice notice-error">
                <p><strong><?php _e('Student Result Management:', 'student-result-management'); ?></strong> <?php echo esc_html($error); ?></p>
                <p>
                    <button type="button" class="button button-primary" id="srm-create-tables">
                        <?php _e('Try Creating Tables Again', 'student-result-management'); ?>
                    </button>
                    <button type="button" class="button" onclick="jQuery(this).closest('.notice').hide();">
                        <?php _e('Dismiss', 'student-result-management'); ?>
                    </button>
                </p>
            </div>
            <script>
            jQuery(document).ready(function($) {
                $('#srm-create-tables').click(function() {
                    var $btn = $(this);
                    $btn.prop('disabled', true).text('<?php _e('Creating tables...', 'student-result-management'); ?>');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'srm_create_tables',
                            nonce: '<?php echo wp_create_nonce('srm_create_tables'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $btn.closest('.notice').removeClass('notice-error').addClass('notice-success');
                                $btn.closest('.notice').find('p:first').html('<strong><?php _e('Success!', 'student-result-management'); ?></strong> ' + response.data.message);
                                $btn.prop('disabled', false).text('<?php _e('Tables Created!', 'student-result-management'); ?>');
                                
                                // Reload page after 2 seconds
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            } else {
                                $btn.prop('disabled', false).text('<?php _e('Try Again', 'student-result-management'); ?>');
                                alert('Error: ' + response.data.message);
                            }
                        },
                        error: function() {
                            $btn.prop('disabled', false).text('<?php _e('Try Again', 'student-result-management'); ?>');
                            alert('<?php _e('Connection error. Please try again.', 'student-result-management'); ?>');
                        }
                    });
                });
            });
            </script>
            <?php
        }
    }
    
    /**
     * AJAX handler to manually create tables
     */
    public function ajax_create_tables() {
        if (!wp_verify_nonce($_POST['nonce'], 'srm_create_tables')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'student-result-management')));
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'student-result-management')));
        }
        
        // Force create tables
        $this->create_tables();
        
        // Verify creation
        global $wpdb;
        $required_tables = array(
            $wpdb->prefix . 'srm_students',
            $wpdb->prefix . 'srm_results', 
            $wpdb->prefix . 'srm_settings'
        );
        
        $missing_tables = array();
        foreach ($required_tables as $table) {
            $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
            if (!$exists) {
                $missing_tables[] = $table;
            }
        }
        
        if (empty($missing_tables)) {
            delete_option('srm_activation_error');
            wp_send_json_success(array('message' => __('All database tables created successfully!', 'student-result-management')));
        } else {
            wp_send_json_error(array('message' => __('Failed to create tables: ', 'student-result-management') . implode(', ', $missing_tables)));
        }
    }
    

    
    public function ajax_import_students_csv() {
        check_ajax_referer('srm_csv_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $license_manager = new SRM_License_Manager();
        if (!$license_manager->has_premium_access()) {
            wp_send_json_error('This is a premium feature. Please upgrade to access it.');
        }
        
        if (!isset($_FILES['csv_file'])) {
            wp_send_json_error('No file uploaded');
        }
        
        $file = $_FILES['csv_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('File upload failed');
        }
        
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            wp_send_json_error('Could not open file');
        }
        
        global $wpdb;
        $imported = 0;
        $row = 1;
        
        while (($data = fgetcsv($handle)) !== false) {
            if ($row === 1) {
                $row++;
                continue; // Skip header row
            }
            
            if (count($data) >= 8) {
                $result = $wpdb->insert(
                    $wpdb->prefix . 'srm_students',
                    array(
                        'roll_number' => sanitize_text_field($data[0]),
                        'first_name' => sanitize_text_field($data[1]),
                        'last_name' => sanitize_text_field($data[2]),
                        'email' => sanitize_email($data[3]),
                        'phone' => sanitize_text_field($data[4]),
                        'class' => sanitize_text_field($data[5]),
                        'section' => sanitize_text_field($data[6]),
                        'date_of_birth' => sanitize_text_field($data[7]),
                        'created_at' => current_time('mysql')
                    ),
                    array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
                );
                
                if ($result !== false) {
                    $imported++;
                }
            }
            $row++;
        }
        
        fclose($handle);
        wp_send_json_success(array('imported' => $imported));
    }
    
    public function ajax_import_results_csv() {
        check_ajax_referer('srm_csv_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $license_manager = new SRM_License_Manager();
        if (!$license_manager->has_premium_access()) {
            wp_send_json_error('This is a premium feature. Please upgrade to access it.');
        }
        
        if (!isset($_FILES['results_csv_file'])) {
            wp_send_json_error('No file uploaded');
        }
        
        $file = $_FILES['results_csv_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('File upload failed');
        }
        
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            wp_send_json_error('Could not open file');
        }
        
        global $wpdb;
        $imported = 0;
        $row = 1;
        
        while (($data = fgetcsv($handle)) !== false) {
            if ($row === 1) {
                $row++;
                continue; // Skip header row
            }
            
            if (count($data) >= 7) {
                // Get student ID by roll number
                $student = $wpdb->get_row($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}srm_students WHERE roll_number = %s",
                    $data[0]
                ));
                
                if ($student) {
                    $percentage = ($data[4] / $data[3]) * 100;
                    $grade = $this->calculate_grade($percentage);
                    $status = ($percentage >= 40) ? 'Pass' : 'Fail';
                    
                    $result = $wpdb->insert(
                        $wpdb->prefix . 'srm_results',
                        array(
                            'student_id' => $student->id,
                            'exam_name' => sanitize_text_field($data[1]),
                            'exam_date' => sanitize_text_field($data[2]),
                            'total_marks' => intval($data[3]),
                            'obtained_marks' => intval($data[4]),
                            'percentage' => $percentage,
                            'grade' => $grade,
                            'status' => $status,
                            'remarks' => isset($data[7]) ? sanitize_text_field($data[7]) : '',
                            'created_at' => current_time('mysql')
                        ),
                        array('%d', '%s', '%s', '%d', '%d', '%f', '%s', '%s', '%s', '%s')
                    );
                    
                    if ($result !== false) {
                        $imported++;
                    }
                }
            }
            $row++;
        }
        
        fclose($handle);
        wp_send_json_success(array('imported' => $imported));
    }
    
    public function ajax_export_analytics() {
        check_ajax_referer('srm_analytics_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $license_manager = new SRM_License_Manager();
        if (!$license_manager->has_premium_access()) {
            wp_send_json_error('This is a premium feature. Please upgrade to access it.');
        }
        
        $type = sanitize_text_field($_POST['type']);
        global $wpdb;
        
        $filename = 'srm_analytics_' . $type . '_' . date('Y-m-d_H-i-s') . '.csv';
        $file_path = WP_CONTENT_DIR . '/srm-exports/' . $filename;
        
        if (!is_dir(WP_CONTENT_DIR . '/srm-exports/')) {
            wp_mkdir_p(WP_CONTENT_DIR . '/srm-exports/');
        }
        
        $output = fopen($file_path, 'w');
        
        switch ($type) {
            case 'performance':
                fputcsv($output, array('Grade', 'Count', 'Average Percentage'));
                $data = $wpdb->get_results("
                    SELECT grade, COUNT(*) as count, AVG(percentage) as avg_percentage
                    FROM {$wpdb->prefix}srm_results
                    GROUP BY grade
                    ORDER BY grade
                ");
                foreach ($data as $row) {
                    fputcsv($output, array($row->grade, $row->count, $row->avg_percentage));
                }
                break;
                
            case 'trends':
                fputcsv($output, array('Month', 'Count', 'Average Percentage'));
                $data = $wpdb->get_results("
                    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count, AVG(percentage) as avg_percentage
                    FROM {$wpdb->prefix}srm_results
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month DESC
                ");
                foreach ($data as $row) {
                    fputcsv($output, array($row->month, $row->count, $row->avg_percentage));
                }
                break;
                
            case 'summary':
                fputcsv($output, array('Metric', 'Value'));
                $total_students = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_students");
                $total_results = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_results");
                $avg_percentage = $wpdb->get_var("SELECT AVG(percentage) FROM {$wpdb->prefix}srm_results");
                
                fputcsv($output, array('Total Students', $total_students));
                fputcsv($output, array('Total Results', $total_results));
                fputcsv($output, array('Average Percentage', round($avg_percentage, 2)));
                break;
        }
        
        fclose($output);
        wp_send_json_success(array('download_url' => content_url('srm-exports/' . $filename)));
    }
    
    /**
     * AJAX handler for preview template
     */
    public function ajax_preview_template() {
        check_ajax_referer('srm_template_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $license_manager = new SRM_License_Manager();
        if (!$license_manager->has_premium_access()) {
            wp_send_json_error('Premium feature access required');
        }
        
        $template_content = sanitize_textarea_field($_POST['template_content']);
        $template_type = sanitize_text_field($_POST['template_type']);
        
        // Replace placeholders with sample data
        $sample_data = $this->get_sample_data_for_template($template_type);
        $preview_content = $this->replace_template_placeholders($template_content, $sample_data);
        
        wp_send_json_success(array('preview' => $preview_content));
    }
    

    
    /**
     * Calculate grade based on percentage
     */
    private function calculate_grade($percentage) {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C+';
        if ($percentage >= 40) return 'C';
        return 'F';
    }
}

// Initialize the plugin
new StudentResultManagement();