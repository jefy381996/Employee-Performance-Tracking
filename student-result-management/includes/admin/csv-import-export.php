<?php
if (!defined('ABSPATH')) exit;

require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
$license_manager = new SRM_License_Manager();

$current_user_id = get_current_user_id();
$plugin_owner = get_option('srm_plugin_owner');
$is_owner = ($current_user_id == $plugin_owner);
$has_premium = $license_manager->has_premium_access();

$message = '';
$error = '';

// Handle CSV upload
if ($_POST && isset($_POST['upload_csv']) && $has_premium) {
    if (!wp_verify_nonce($_POST['srm_csv_nonce'], 'srm_csv_import_action')) {
        $error = __('Security check failed.', 'student-result-management');
    } else {
        $message = __('Please use the upload form below to import your CSV file.', 'student-result-management');
    }
}
?>

<div class="wrap srm-csv-import-export">
    <h1><?php _e('CSV Import / Export', 'student-result-management'); ?></h1>
    
    <?php if (!$has_premium): ?>
        <div class="notice notice-warning">
            <h3><?php _e('Premium Feature', 'student-result-management'); ?></h3>
            <p><?php _e('CSV Import/Export is a premium feature. Please upgrade to access this functionality.', 'student-result-management'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=srm-premium'); ?>" class="button button-primary">
                <?php _e('Upgrade to Premium', 'student-result-management'); ?>
            </a>
        </div>
    <?php else: ?>
        
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
        
        <div class="srm-csv-sections">
            <!-- Import Section -->
            <div class="srm-csv-section">
                <h2><?php _e('Import Data', 'student-result-management'); ?></h2>
                <div class="srm-csv-import">
                    <h3><?php _e('Import Students', 'student-result-management'); ?></h3>
                    <p><?php _e('Upload a CSV file to import student data. The CSV should have the following columns:', 'student-result-management'); ?></p>
                    <ul>
                        <li>Roll Number</li>
                        <li>First Name</li>
                        <li>Last Name</li>
                        <li>Email</li>
                        <li>Phone</li>
                        <li>Class</li>
                        <li>Section</li>
                        <li>Date of Birth (YYYY-MM-DD)</li>
                    </ul>
                    
                    <form method="post" enctype="multipart/form-data" id="srm-csv-import-form">
                        <?php wp_nonce_field('srm_csv_import_action', 'srm_csv_nonce'); ?>
                        <input type="file" name="csv_file" accept=".csv" required>
                        <button type="submit" name="upload_csv" class="button button-primary">
                            <?php _e('Import Students', 'student-result-management'); ?>
                        </button>
                    </form>
                    
                    <div id="srm-import-progress" style="display: none;">
                        <div class="srm-progress-bar">
                            <div class="srm-progress-fill"></div>
                        </div>
                        <p id="srm-import-status"><?php _e('Processing...', 'student-result-management'); ?></p>
                    </div>
                </div>
                
                <div class="srm-csv-import">
                    <h3><?php _e('Import Results', 'student-result-management'); ?></h3>
                    <p><?php _e('Upload a CSV file to import result data. The CSV should have the following columns:', 'student-result-management'); ?></p>
                    <ul>
                        <li>Student Roll Number</li>
                        <li>Exam Name</li>
                        <li>Exam Date (YYYY-MM-DD)</li>
                        <li>Total Marks</li>
                        <li>Obtained Marks</li>
                        <li>Grade</li>
                        <li>Status</li>
                        <li>Remarks (optional)</li>
                    </ul>
                    
                    <form method="post" enctype="multipart/form-data" id="srm-results-import-form">
                        <?php wp_nonce_field('srm_csv_import_action', 'srm_csv_nonce'); ?>
                        <input type="file" name="results_csv_file" accept=".csv" required>
                        <button type="submit" name="upload_results_csv" class="button button-primary">
                            <?php _e('Import Results', 'student-result-management'); ?>
                        </button>
                    </form>
                    
                    <div id="srm-results-import-progress" style="display: none;">
                        <div class="srm-progress-bar">
                            <div class="srm-progress-fill"></div>
                        </div>
                        <p id="srm-results-import-status"><?php _e('Processing...', 'student-result-management'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Export Section -->
            <div class="srm-csv-section">
                <h2><?php _e('Export Data', 'student-result-management'); ?></h2>
                <div class="srm-csv-export">
                    <h3><?php _e('Export Students', 'student-result-management'); ?></h3>
                    <p><?php _e('Download all student data as a CSV file.', 'student-result-management'); ?></p>
                    <button type="button" class="button button-secondary srm-export-students" data-type="students">
                        <?php _e('Export Students', 'student-result-management'); ?>
                    </button>
                </div>
                
                <div class="srm-csv-export">
                    <h3><?php _e('Export Results', 'student-result-management'); ?></h3>
                    <p><?php _e('Download all result data as a CSV file.', 'student-result-management'); ?></p>
                    <button type="button" class="button button-secondary srm-export-results" data-type="results">
                        <?php _e('Export Results', 'student-result-management'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <style>
        .srm-csv-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        
        .srm-csv-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        
        .srm-csv-import, .srm-csv-export {
            margin-bottom: 30px;
        }
        
        .srm-csv-import ul, .srm-csv-export ul {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        .srm-csv-import li, .srm-csv-export li {
            margin-bottom: 5px;
        }
        
        .srm-progress-bar {
            width: 100%;
            height: 20px;
            background: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .srm-progress-fill {
            height: 100%;
            background: #0073aa;
            width: 0%;
            transition: width 0.3s ease;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // CSV Import for Students
            $('#srm-csv-import-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                formData.append('action', 'srm_import_students_csv');
                
                $('#srm-import-progress').show();
                $('.srm-progress-fill').css('width', '0%');
                $('#srm-import-status').text('Uploading...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = evt.loaded / evt.total;
                                $('.srm-progress-fill').css('width', percentComplete * 100 + '%');
                                $('#srm-import-status').text('Uploading... ' + Math.round(percentComplete * 100) + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        if (response.success) {
                            $('.srm-progress-fill').css('width', '100%');
                            $('#srm-import-status').text('Import completed! ' + response.data.imported + ' students imported.');
                            setTimeout(function() {
                                $('#srm-import-progress').hide();
                                location.reload();
                            }, 2000);
                        } else {
                            $('#srm-import-status').text('Error: ' + response.data);
                            setTimeout(function() {
                                $('#srm-import-progress').hide();
                            }, 3000);
                        }
                    },
                    error: function() {
                        $('#srm-import-status').text('Upload failed. Please try again.');
                        setTimeout(function() {
                            $('#srm-import-progress').hide();
                        }, 3000);
                    }
                });
            });
            
            // CSV Import for Results
            $('#srm-results-import-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                formData.append('action', 'srm_import_results_csv');
                
                $('#srm-results-import-progress').show();
                $('.srm-progress-fill').css('width', '0%');
                $('#srm-results-import-status').text('Uploading...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = evt.loaded / evt.total;
                                $('.srm-progress-fill').css('width', percentComplete * 100 + '%');
                                $('#srm-results-import-status').text('Uploading... ' + Math.round(percentComplete * 100) + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        if (response.success) {
                            $('.srm-progress-fill').css('width', '100%');
                            $('#srm-results-import-status').text('Import completed! ' + response.data.imported + ' results imported.');
                            setTimeout(function() {
                                $('#srm-results-import-progress').hide();
                                location.reload();
                            }, 2000);
                        } else {
                            $('#srm-results-import-status').text('Error: ' + response.data);
                            setTimeout(function() {
                                $('#srm-results-import-progress').hide();
                            }, 3000);
                        }
                    },
                    error: function() {
                        $('#srm-results-import-status').text('Upload failed. Please try again.');
                        setTimeout(function() {
                            $('#srm-results-import-progress').hide();
                        }, 3000);
                    }
                });
            });
            
            // CSV Export functionality
            $('.srm-export-students, .srm-export-results').on('click', function() {
                var $btn = $(this);
                var exportType = $btn.data('type');
                var originalText = $btn.text();
                
                // Disable button and show loading
                $btn.prop('disabled', true).text('Preparing export...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'srm_export_csv',
                        export_type: exportType,
                        nonce: '<?php echo wp_create_nonce('srm_export_csv'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Create download link
                            var link = document.createElement('a');
                            link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(response.data.csv_content);
                            link.download = response.data.filename;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            
                            $btn.text('Export completed!');
                            setTimeout(function() {
                                $btn.prop('disabled', false).text(originalText);
                            }, 2000);
                        } else {
                            alert('Export failed: ' + response.data);
                            $btn.prop('disabled', false).text(originalText);
                        }
                    },
                    error: function() {
                        alert('Export failed. Please try again.');
                        $btn.prop('disabled', false).text(originalText);
                    }
                });
            });
        });
        </script>
        
    <?php endif; ?>
</div>