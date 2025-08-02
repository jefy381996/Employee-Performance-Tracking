<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

$message = '';
$error = '';

$current_user_id = get_current_user_id();
$plugin_owner = get_option('srm_plugin_owner');
$is_owner = ($current_user_id == $plugin_owner);

// Handle form submission
if ($_POST && wp_verify_nonce($_POST['srm_nonce'], 'srm_settings_action')) {
    $settings_table = $wpdb->prefix . 'srm_settings';
    
    $settings = array(
        'school_name' => sanitize_text_field($_POST['school_name']),
        'admin_email' => sanitize_email($_POST['admin_email']),
        'result_template' => sanitize_text_field($_POST['result_template']),
        'grade_system' => sanitize_text_field($_POST['grade_system']),
        'passing_marks' => intval($_POST['passing_marks'])
    );
    
    $updated = 0;
    foreach ($settings as $name => $value) {
        $result = $wpdb->replace($settings_table, array(
            'setting_name' => $name,
            'setting_value' => $value
        ));
        if ($result) $updated++;
    }
    
    if ($updated > 0) {
        $message = __('Settings saved successfully!', 'student-result-management');
    } else {
        $error = __('Error saving settings.', 'student-result-management');
    }
}

// Get current settings
$settings_table = $wpdb->prefix . 'srm_settings';
$current_settings = array();
$settings_data = $wpdb->get_results("SELECT setting_name, setting_value FROM $settings_table");

foreach ($settings_data as $setting) {
    $current_settings[$setting->setting_name] = $setting->setting_value;
}

// Set defaults if not exists
$defaults = array(
    'school_name' => get_bloginfo('name'),
    'admin_email' => get_option('admin_email'),
    'result_template' => 'default',
    'grade_system' => 'letter',
    'passing_marks' => '40'
);

foreach ($defaults as $key => $default_value) {
    if (!isset($current_settings[$key])) {
        $current_settings[$key] = $default_value;
    }
}
?>

<div class="wrap srm-settings">
    <h1><?php _e('Settings', 'student-result-management'); ?></h1>
    
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
    
    <div class="srm-settings-container">
        <form method="post" class="srm-settings-form">
            <?php wp_nonce_field('srm_settings_action', 'srm_nonce'); ?>
            
            <!-- School Information -->
            <div class="srm-settings-section">
                <h2><?php _e('School Information', 'student-result-management'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="school_name"><?php _e('School Name', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="school_name" id="school_name" class="regular-text" 
                                   value="<?php echo esc_attr($current_settings['school_name']); ?>" required>
                            <p class="description"><?php _e('Name of your school/institution that will appear on result cards.', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="admin_email"><?php _e('Admin Email', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="email" name="admin_email" id="admin_email" class="regular-text" 
                                   value="<?php echo esc_attr($current_settings['admin_email']); ?>" required>
                            <p class="description"><?php _e('Email address for administrative notifications.', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Grading System -->
            <div class="srm-settings-section">
                <h2><?php _e('Grading System', 'student-result-management'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="grade_system"><?php _e('Grade System', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <select name="grade_system" id="grade_system" class="regular-text">
                                <option value="letter" <?php selected($current_settings['grade_system'], 'letter'); ?>><?php _e('Letter Grades (A, B, C, D, F)', 'student-result-management'); ?></option>
                                <option value="percentage" <?php selected($current_settings['grade_system'], 'percentage'); ?>><?php _e('Percentage Only', 'student-result-management'); ?></option>
                                <option value="points" <?php selected($current_settings['grade_system'], 'points'); ?>><?php _e('Grade Points (4.0 Scale)', 'student-result-management'); ?></option>
                            </select>
                            <p class="description"><?php _e('Choose how grades should be calculated and displayed.', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="passing_marks"><?php _e('Passing Percentage', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="passing_marks" id="passing_marks" class="small-text" 
                                   value="<?php echo esc_attr($current_settings['passing_marks']); ?>" min="0" max="100" required>
                            <span>%</span>
                            <p class="description"><?php _e('Minimum percentage required to pass an exam.', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <!-- Grade Scale Reference -->
                <div class="srm-grade-scale">
                    <h3><?php _e('Grade Scale Reference', 'student-result-management'); ?></h3>
                    <div class="srm-grade-table">
                        <table class="wp-list-table widefat">
                            <thead>
                                <tr>
                                    <th><?php _e('Grade', 'student-result-management'); ?></th>
                                    <th><?php _e('Percentage Range', 'student-result-management'); ?></th>
                                    <th><?php _e('Description', 'student-result-management'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="grade-badge grade-a">A+</span></td>
                                    <td>90% - 100%</td>
                                    <td><?php _e('Excellent', 'student-result-management'); ?></td>
                                </tr>
                                <tr>
                                    <td><span class="grade-badge grade-a">A</span></td>
                                    <td>80% - 89%</td>
                                    <td><?php _e('Very Good', 'student-result-management'); ?></td>
                                </tr>
                                <tr>
                                    <td><span class="grade-badge grade-b">B+</span></td>
                                    <td>70% - 79%</td>
                                    <td><?php _e('Good', 'student-result-management'); ?></td>
                                </tr>
                                <tr>
                                    <td><span class="grade-badge grade-b">B</span></td>
                                    <td>60% - 69%</td>
                                    <td><?php _e('Above Average', 'student-result-management'); ?></td>
                                </tr>
                                <tr>
                                    <td><span class="grade-badge grade-c">C+</span></td>
                                    <td>50% - 59%</td>
                                    <td><?php _e('Average', 'student-result-management'); ?></td>
                                </tr>
                                <tr>
                                    <td><span class="grade-badge grade-c">C</span></td>
                                    <td>40% - 49%</td>
                                    <td><?php _e('Below Average', 'student-result-management'); ?></td>
                                </tr>
                                <tr>
                                    <td><span class="grade-badge grade-f">F</span></td>
                                    <td>0% - 39%</td>
                                    <td><?php _e('Fail', 'student-result-management'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Result Display -->
            <div class="srm-settings-section">
                <h2><?php _e('Result Display', 'student-result-management'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="result_template"><?php _e('Result Template', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <select name="result_template" id="result_template" class="regular-text">
                                <option value="default" <?php selected($current_settings['result_template'], 'default'); ?>><?php _e('Default Template', 'student-result-management'); ?></option>
                                <option value="modern" <?php selected($current_settings['result_template'], 'modern'); ?>><?php _e('Modern Template', 'student-result-management'); ?></option>
                                <option value="classic" <?php selected($current_settings['result_template'], 'classic'); ?>><?php _e('Classic Template', 'student-result-management'); ?></option>
                            </select>
                            <p class="description"><?php _e('Choose the template style for displaying student results.', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Plugin Information -->
            <div class="srm-settings-section">
                <h2><?php _e('Plugin Information', 'student-result-management'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Plugin Version', 'student-result-management'); ?></th>
                        <td>
                            <strong><?php echo SRM_PLUGIN_VERSION; ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('License Status', 'student-result-management'); ?></th>
                        <td>
                            <?php if ($is_owner): ?>
                                <span class="srm-status-badge status-premium"><?php _e('Owner (Full Access)', 'student-result-management'); ?></span>
                            <?php else: ?>
                                <span class="srm-status-badge status-free"><?php _e('Free Version', 'student-result-management'); ?></span>
                                <a href="<?php echo admin_url('admin.php?page=srm-premium'); ?>" class="button button-small">
                                    <?php _e('Upgrade to Premium', 'student-result-management'); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Database Tables', 'student-result-management'); ?></th>
                        <td>
                            <?php
                            $tables = array(
                                $wpdb->prefix . 'srm_students' => __('Students', 'student-result-management'),
                                $wpdb->prefix . 'srm_results' => __('Results', 'student-result-management'),
                                $wpdb->prefix . 'srm_settings' => __('Settings', 'student-result-management')
                            );
                            
                            foreach ($tables as $table => $label) {
                                $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
                                echo '<span class="srm-table-status ' . ($exists ? 'exists' : 'missing') . '">';
                                echo $label . ' (' . $table . ') - ';
                                echo $exists ? __('OK', 'student-result-management') : __('Missing', 'student-result-management');
                                echo '</span><br>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Shortcode', 'student-result-management'); ?></th>
                        <td>
                            <code>[student_result_lookup]</code>
                            <p class="description"><?php _e('Use this shortcode on any page or post to display the result lookup form.', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Advanced Settings (Premium) -->
            <?php if ($is_owner): ?>
                <div class="srm-settings-section">
                    <h2><?php _e('Advanced Settings', 'student-result-management'); ?> <span class="srm-premium-badge"><?php _e('Premium', 'student-result-management'); ?></span></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Auto Email Results', 'student-result-management'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="auto_email_results" value="1">
                                    <?php _e('Automatically email results to students when published', 'student-result-management'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Result Visibility', 'student-result-management'); ?></th>
                            <td>
                                <select name="result_visibility" class="regular-text">
                                    <option value="public"><?php _e('Public (Anyone can search)', 'student-result-management'); ?></option>
                                    <option value="private"><?php _e('Private (Login required)', 'student-result-management'); ?></option>
                                    <option value="restricted"><?php _e('Restricted (Specific users only)', 'student-result-management'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Data Retention', 'student-result-management'); ?></th>
                            <td>
                                <select name="data_retention" class="regular-text">
                                    <option value="forever"><?php _e('Keep Forever', 'student-result-management'); ?></option>
                                    <option value="1year"><?php _e('Delete after 1 year', 'student-result-management'); ?></option>
                                    <option value="2years"><?php _e('Delete after 2 years', 'student-result-management'); ?></option>
                                    <option value="5years"><?php _e('Delete after 5 years', 'student-result-management'); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>
            
            <p class="submit">
                <input type="submit" name="submit" class="button button-primary" value="<?php _e('Save Settings', 'student-result-management'); ?>">
            </p>
        </form>
        
        <!-- Reset Section -->
        <div class="srm-settings-section srm-danger-zone">
            <h2><?php _e('Danger Zone', 'student-result-management'); ?></h2>
            <p><?php _e('These actions cannot be undone. Please be careful.', 'student-result-management'); ?></p>
            
            <div class="srm-danger-actions">
                <button type="button" class="button srm-reset-settings" data-action="reset_settings">
                    <?php _e('Reset Settings to Default', 'student-result-management'); ?>
                </button>
                
                <?php if ($is_owner): ?>
                    <button type="button" class="button srm-clear-data" data-action="clear_all_data">
                        <?php _e('Clear All Data', 'student-result-management'); ?>
                    </button>
                    
                    <button type="button" class="button srm-export-settings" data-action="export_settings">
                        <?php _e('Export Settings', 'student-result-management'); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle danger zone actions
    $('.srm-danger-actions button').click(function() {
        var action = $(this).data('action');
        var confirmMessage = '';
        
        switch (action) {
            case 'reset_settings':
                confirmMessage = '<?php _e('Are you sure you want to reset all settings to default? This cannot be undone.', 'student-result-management'); ?>';
                break;
            case 'clear_all_data':
                confirmMessage = '<?php _e('Are you sure you want to clear ALL student and result data? This will permanently delete everything and cannot be undone!', 'student-result-management'); ?>';
                break;
            case 'export_settings':
                // No confirmation needed for export
                break;
        }
        
        if (confirmMessage && !confirm(confirmMessage)) {
            return;
        }
        
        // Perform action via AJAX
        $.ajax({
            url: srm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'srm_danger_action',
                danger_action: action,
                nonce: srm_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    if (action === 'reset_settings' || action === 'clear_all_data') {
                        location.reload();
                    }
                } else {
                    alert('<?php _e('Error:', 'student-result-management'); ?> ' + response.data);
                }
            },
            error: function() {
                alert('<?php _e('Action failed. Please try again.', 'student-result-management'); ?>');
            }
        });
    });
});
</script>