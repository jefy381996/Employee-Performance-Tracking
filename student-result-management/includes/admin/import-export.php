<?php
if (!defined('ABSPATH')) exit;

$current_user_id = get_current_user_id();
$plugin_owner = get_option('srm_plugin_owner');
$is_premium = ($current_user_id == $plugin_owner);

$message = '';
$error = '';

// Handle CSV upload
if ($_POST && isset($_POST['upload_csv']) && $is_premium) {
    if (!wp_verify_nonce($_POST['srm_nonce'], 'srm_import_action')) {
        $error = __('Security check failed.', 'student-result-management');
    } else {
        // CSV upload will be handled via AJAX in the main plugin file
        $message = __('Please use the upload form below to import your CSV file.', 'student-result-management');
    }
}

// Handle export actions
if (isset($_GET['export']) && wp_verify_nonce($_GET['_wpnonce'], 'srm_export')) {
    global $wpdb;
    
    $export_type = sanitize_text_field($_GET['export']);
    $filename = '';
    $headers = array();
    $data = array();
    
    if ($export_type === 'students') {
        $filename = 'students_' . date('Y-m-d') . '.csv';
        $headers = array('Roll Number', 'First Name', 'Last Name', 'Email', 'Phone', 'Class', 'Section', 'Date of Birth', 'Created At');
        $students = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}srm_students ORDER BY created_at DESC");
        
        foreach ($students as $student) {
            $data[] = array(
                $student->roll_number,
                $student->first_name,
                $student->last_name,
                $student->email,
                $student->phone,
                $student->class,
                $student->section,
                $student->date_of_birth,
                $student->created_at
            );
        }
    } elseif ($export_type === 'results' && $is_premium) {
        $filename = 'results_' . date('Y-m-d') . '.csv';
        $headers = array('Student Roll', 'Student Name', 'Exam Name', 'Exam Date', 'Total Marks', 'Obtained Marks', 'Percentage', 'Grade', 'Status', 'Created At');
        $results = $wpdb->get_results("
            SELECT r.*, s.roll_number, s.first_name, s.last_name 
            FROM {$wpdb->prefix}srm_results r 
            LEFT JOIN {$wpdb->prefix}srm_students s ON r.student_id = s.id 
            ORDER BY r.created_at DESC
        ");
        
        foreach ($results as $result) {
            $data[] = array(
                $result->roll_number,
                $result->first_name . ' ' . $result->last_name,
                $result->exam_name,
                $result->exam_date,
                $result->total_marks,
                $result->obtained_marks,
                $result->percentage,
                $result->grade,
                $result->status,
                $result->created_at
            );
        }
    }
    
    if (!empty($data)) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
}
?>

<div class="wrap srm-import-export">
    <h1><?php _e('Import / Export Data', 'student-result-management'); ?></h1>
    
    <?php if ($message): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html($error); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="srm-import-export-container">
        <!-- Import Section -->
        <div class="srm-section">
            <h2><?php _e('Import Data', 'student-result-management'); ?></h2>
            
            <?php if ($is_premium): ?>
                <div class="srm-import-area">
                    <h3><?php _e('Import Students from CSV', 'student-result-management'); ?></h3>
                    <p><?php _e('Upload a CSV file with student information. The file should contain columns: roll_number, first_name, last_name, email, phone, class, section, date_of_birth', 'student-result-management'); ?></p>
                    
                    <div class="srm-csv-upload">
                        <input type="file" id="csv_file" accept=".csv" class="srm-file-input">
                        <div class="srm-upload-area" id="upload-area">
                            <div class="srm-upload-icon">
                                <span class="dashicons dashicons-upload"></span>
                            </div>
                            <p><?php _e('Drag and drop your CSV file here, or click to select', 'student-result-management'); ?></p>
                            <button type="button" class="button button-secondary" id="select-file-btn">
                                <?php _e('Select CSV File', 'student-result-management'); ?>
                            </button>
                        </div>
                        <div class="srm-upload-progress" id="upload-progress" style="display: none;">
                            <div class="srm-progress-bar">
                                <div class="srm-progress-fill"></div>
                            </div>
                            <p class="srm-progress-text"><?php _e('Uploading...', 'student-result-management'); ?></p>
                        </div>
                        <button type="button" class="button button-primary" id="upload-csv-btn" disabled>
                            <?php _e('Import Students', 'student-result-management'); ?>
                        </button>
                    </div>
                    
                    <div class="srm-sample-csv">
                        <h4><?php _e('Sample CSV Format', 'student-result-management'); ?></h4>
                        <pre>roll_number,first_name,last_name,email,phone,class,section,date_of_birth
2023001,John,Doe,john.doe@email.com,1234567890,10,A,2005-01-15
2023002,Jane,Smith,jane.smith@email.com,0987654321,10,B,2005-03-20</pre>
                        <a href="<?php echo SRM_PLUGIN_URL . 'assets/sample.csv'; ?>" class="button button-small" download>
                            <?php _e('Download Sample CSV', 'student-result-management'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="srm-import-results">
                    <h3><?php _e('Bulk Import Results', 'student-result-management'); ?></h3>
                    <p><?php _e('Import multiple student results at once using a CSV file.', 'student-result-management'); ?></p>
                    <div class="srm-feature-coming-soon">
                        <span class="dashicons dashicons-clock"></span>
                        <p><?php _e('This feature is coming soon! Stay tuned for updates.', 'student-result-management'); ?></p>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="srm-premium-notice">
                    <div class="srm-premium-icon">
                        <span class="dashicons dashicons-lock"></span>
                    </div>
                    <h3><?php _e('Premium Feature', 'student-result-management'); ?></h3>
                    <p><?php _e('CSV import functionality is available in the premium version. Upgrade to unlock this and other advanced features!', 'student-result-management'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=srm-premium'); ?>" class="button button-primary">
                        <?php _e('Upgrade to Premium', 'student-result-management'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Export Section -->
        <div class="srm-section">
            <h2><?php _e('Export Data', 'student-result-management'); ?></h2>
            
            <div class="srm-export-options">
                <div class="srm-export-item">
                    <h3><?php _e('Export Students', 'student-result-management'); ?></h3>
                    <p><?php _e('Download all student information as a CSV file.', 'student-result-management'); ?></p>
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=srm-import-export&export=students'), 'srm_export'); ?>" 
                       class="button button-secondary">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Export Students CSV', 'student-result-management'); ?>
                    </a>
                </div>
                
                <div class="srm-export-item">
                    <h3><?php _e('Export Results', 'student-result-management'); ?></h3>
                    <p><?php _e('Download all student results as a CSV file.', 'student-result-management'); ?></p>
                    <?php if ($is_premium): ?>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=srm-import-export&export=results'), 'srm_export'); ?>" 
                           class="button button-secondary">
                            <span class="dashicons dashicons-download"></span>
                            <?php _e('Export Results CSV', 'student-result-management'); ?>
                        </a>
                    <?php else: ?>
                        <button class="button button-secondary" disabled>
                            <span class="dashicons dashicons-lock"></span>
                            <?php _e('Premium Feature', 'student-result-management'); ?>
                        </button>
                    <?php endif; ?>
                </div>
                
                <?php if ($is_premium): ?>
                    <div class="srm-export-item">
                        <h3><?php _e('Export Report Cards', 'student-result-management'); ?></h3>
                        <p><?php _e('Generate and download PDF report cards for all students.', 'student-result-management'); ?></p>
                        <button class="button button-secondary srm-bulk-pdf-export">
                            <span class="dashicons dashicons-media-document"></span>
                            <?php _e('Generate Bulk PDFs', 'student-result-management'); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Data Management -->
        <div class="srm-section">
            <h2><?php _e('Data Management', 'student-result-management'); ?></h2>
            
            <div class="srm-data-tools">
                <div class="srm-tool-item">
                    <h3><?php _e('Database Statistics', 'student-result-management'); ?></h3>
                    <?php
                    global $wpdb;
                    $students_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_students");
                    $results_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_results");
                    $settings_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_settings");
                    ?>
                    <ul>
                        <li><?php printf(__('Students: %d', 'student-result-management'), $students_count); ?></li>
                        <li><?php printf(__('Results: %d', 'student-result-management'), $results_count); ?></li>
                        <li><?php printf(__('Settings: %d', 'student-result-management'), $settings_count); ?></li>
                    </ul>
                </div>
                
                <?php if ($is_premium): ?>
                    <div class="srm-tool-item">
                        <h3><?php _e('Data Backup', 'student-result-management'); ?></h3>
                        <p><?php _e('Create a complete backup of all your student data.', 'student-result-management'); ?></p>
                        <button class="button button-secondary srm-backup-data">
                            <span class="dashicons dashicons-backup"></span>
                            <?php _e('Create Backup', 'student-result-management'); ?>
                        </button>
                    </div>
                    
                    <div class="srm-tool-item">
                        <h3><?php _e('Data Validation', 'student-result-management'); ?></h3>
                        <p><?php _e('Check for and fix any data inconsistencies.', 'student-result-management'); ?></p>
                        <button class="button button-secondary srm-validate-data">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php _e('Validate Data', 'student-result-management'); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // File upload handling
    var selectedFile = null;
    
    $('#select-file-btn, #upload-area').click(function() {
        $('#csv_file').click();
    });
    
    $('#csv_file').change(function() {
        var file = this.files[0];
        if (file) {
            selectedFile = file;
            $('#upload-area p').text('<?php _e('File selected:', 'student-result-management'); ?> ' + file.name);
            $('#upload-csv-btn').prop('disabled', false);
        }
    });
    
    $('#upload-csv-btn').click(function() {
        if (!selectedFile) return;
        
        var formData = new FormData();
        formData.append('action', 'srm_upload_csv');
        formData.append('csv_file', selectedFile);
        formData.append('nonce', srm_ajax.nonce);
        
        $('#upload-progress').show();
        $('#upload-csv-btn').prop('disabled', true);
        
        $.ajax({
            url: srm_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#upload-progress').hide();
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('<?php _e('Error:', 'student-result-management'); ?> ' + response.data);
                }
            },
            error: function() {
                $('#upload-progress').hide();
                alert('<?php _e('Upload failed. Please try again.', 'student-result-management'); ?>');
            },
            complete: function() {
                $('#upload-csv-btn').prop('disabled', false);
            }
        });
    });
    
    // Drag and drop
    $('#upload-area').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });
    
    $('#upload-area').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
    });
    
    $('#upload-area').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            selectedFile = files[0];
            $('#upload-area p').text('<?php _e('File selected:', 'student-result-management'); ?> ' + files[0].name);
            $('#upload-csv-btn').prop('disabled', false);
        }
    });
});
</script>