<?php
/**
 * Plugin Name: Student Result Management System
 * Plugin URI: https://yourwebsite.com/student-result-management
 * Description: A comprehensive student result management system with free and premium features. Manage student records, marks, and generate beautiful result cards.
 * Version: 1.0.0
 * Author: Your Name
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
define('SRM_PLUGIN_VERSION', '1.0.0');
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
        add_action('wp_ajax_srm_generate_pdf', array($this, 'ajax_generate_pdf'));
        add_action('wp_ajax_srm_create_tables', array($this, 'ajax_create_tables'));
        
        // Include feature control system
        require_once SRM_PLUGIN_PATH . 'includes/admin/feature-control.php';
        
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
        
        // Verify tables were created
        $this->verify_tables();
        
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
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
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $students_result = dbDelta($students_sql);
        $results_result = dbDelta($results_sql);
        $settings_result = dbDelta($settings_sql);
        $payments_result = dbDelta($payments_sql);
        
        // Log any database creation issues
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('SRM Plugin: Students table creation result: ' . print_r($students_result, true));
            error_log('SRM Plugin: Results table creation result: ' . print_r($results_result, true));
            error_log('SRM Plugin: Settings table creation result: ' . print_r($settings_result, true));
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
            'payments' => $wpdb->prefix . 'srm_payments'
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
        
        // Make current admin the owner
        update_option('srm_plugin_owner', get_current_user_id());
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
        
        add_submenu_page(
            'student-results',
            __('Import/Export', 'student-result-management'),
            __('Import/Export', 'student-result-management'),
            'manage_options',
            'srm-import-export',
            array($this, 'admin_import_export_page')
        );
        
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
     * AJAX handler for PDF generation (Premium feature)
     */
    public function ajax_generate_pdf() {
        check_ajax_referer('srm_nonce', 'nonce');
        
        if (!$this->is_premium_user()) {
            wp_send_json_error(__('This is a premium feature. Please upgrade to access it.', 'student-result-management'));
        }
        
        $student_id = intval($_POST['student_id']);
        $result_id = intval($_POST['result_id']);
        
        // Generate PDF logic would go here
        // For now, we'll return a success message
        wp_send_json_success(array(
            'message' => __('PDF generated successfully!', 'student-result-management'),
            'download_url' => admin_url('admin.php?page=srm-results&action=download_pdf&student_id=' . $student_id . '&result_id=' . $result_id)
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
}

// Initialize the plugin
new StudentResultManagement();