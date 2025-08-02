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

// Handle template creation/editing
if ($_POST && isset($_POST['save_template']) && $has_premium) {
    if (!wp_verify_nonce($_POST['srm_template_nonce'], 'srm_template_action')) {
        $error = __('Security check failed.', 'student-result-management');
    } else {
        $template_name = sanitize_text_field($_POST['template_name']);
        $template_type = sanitize_text_field($_POST['template_type']);
        $template_content = wp_kses_post($_POST['template_content']);
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
        
        if (empty($template_name)) {
            $error = __('Template name is required.', 'student-result-management');
        } else {
            $result = srm_save_template($template_id, $template_name, $template_type, $template_content);
            if ($result['success']) {
                $message = __('Template saved successfully!', 'student-result-management');
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Handle template deletion
if ($_POST && isset($_POST['delete_template']) && $has_premium) {
    if (!wp_verify_nonce($_POST['srm_template_nonce'], 'srm_template_action')) {
        $error = __('Security check failed.', 'student-result-management');
    } else {
        $template_id = intval($_POST['template_id']);
        $result = srm_delete_template($template_id);
        if ($result['success']) {
            $message = __('Template deleted successfully!', 'student-result-management');
        } else {
            $error = $result['message'];
        }
    }
}

// Get templates
global $wpdb;
$templates = $wpdb->get_results("
    SELECT * FROM {$wpdb->prefix}srm_templates 
    ORDER BY template_type, template_name
");

// Get template for editing
$edit_template = null;
if (isset($_GET['edit']) && $has_premium) {
    $edit_id = intval($_GET['edit']);
    $edit_template = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}srm_templates WHERE id = %d",
        $edit_id
    ));
}

function srm_save_template($template_id, $name, $type, $content) {
    global $wpdb;
    
    try {
        if ($template_id > 0) {
            // Update existing template
            $result = $wpdb->update(
                "{$wpdb->prefix}srm_templates",
                array(
                    'template_name' => $name,
                    'template_type' => $type,
                    'template_content' => $content,
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $template_id),
                array('%s', '%s', '%s', '%s'),
                array('%d')
            );
            
            if ($result === false) {
                return array('success' => false, 'message' => __('Failed to update template.', 'student-result-management'));
            }
        } else {
            // Create new template
            $result = $wpdb->insert(
                "{$wpdb->prefix}srm_templates",
                array(
                    'template_name' => $name,
                    'template_type' => $type,
                    'template_content' => $content,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s', '%s')
            );
            
            if ($result === false) {
                return array('success' => false, 'message' => __('Failed to create template.', 'student-result-management'));
            }
        }
        
        return array('success' => true, 'message' => __('Template saved successfully.', 'student-result-management'));
        
    } catch (Exception $e) {
        return array('success' => false, 'message' => $e->getMessage());
    }
}

function srm_delete_template($template_id) {
    global $wpdb;
    
    try {
        $result = $wpdb->delete(
            "{$wpdb->prefix}srm_templates",
            array('id' => $template_id),
            array('%d')
        );
        
        if ($result === false) {
            return array('success' => false, 'message' => __('Failed to delete template.', 'student-result-management'));
        }
        
        return array('success' => true, 'message' => __('Template deleted successfully.', 'student-result-management'));
        
    } catch (Exception $e) {
        return array('success' => false, 'message' => $e->getMessage());
    }
}
?>

<div class="wrap srm-custom-templates">
    <h1><?php _e('Custom Templates', 'student-result-management'); ?></h1>
    
    <?php if (!$has_premium): ?>
        <div class="notice notice-warning">
            <h3><?php _e('Premium Feature', 'student-result-management'); ?></h3>
            <p><?php _e('Custom Templates is a premium feature. Please upgrade to access template management functionality.', 'student-result-management'); ?></p>
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
        
        <div class="srm-template-sections">
            <!-- Template Form -->
            <div class="srm-template-section">
                <h2><?php echo $edit_template ? __('Edit Template', 'student-result-management') : __('Create New Template', 'student-result-management'); ?></h2>
                
                <form method="post" id="srm-template-form">
                    <?php wp_nonce_field('srm_template_action', 'srm_template_nonce'); ?>
                    
                    <?php if ($edit_template): ?>
                        <input type="hidden" name="template_id" value="<?php echo $edit_template->id; ?>">
                    <?php endif; ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="template_name"><?php _e('Template Name', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="template_name" name="template_name" value="<?php echo $edit_template ? esc_attr($edit_template->template_name) : ''; ?>" class="regular-text" required>
                                <p class="description"><?php _e('Enter a descriptive name for this template', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="template_type"><?php _e('Template Type', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <select id="template_type" name="template_type" required>
                                    <option value=""><?php _e('Select Template Type', 'student-result-management'); ?></option>
                                    <option value="email" <?php selected($edit_template ? $edit_template->template_type : '', 'email'); ?>><?php _e('Email Template', 'student-result-management'); ?></option>
                                    <option value="result_card" <?php selected($edit_template ? $edit_template->template_type : '', 'result_card'); ?>><?php _e('Result Card Template', 'student-result-management'); ?></option>
                                    <option value="certificate" <?php selected($edit_template ? $edit_template->template_type : '', 'certificate'); ?>><?php _e('Certificate Template', 'student-result-management'); ?></option>
                                    <option value="report" <?php selected($edit_template ? $edit_template->template_type : '', 'report'); ?>><?php _e('Report Template', 'student-result-management'); ?></option>
                                </select>
                                <p class="description"><?php _e('Choose the type of template you want to create', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="template_content"><?php _e('Template Content', 'student-result-management'); ?></label>
                            </th>
                            <td>
                                <div class="srm-template-editor">
                                    <textarea id="template_content" name="template_content" rows="15" cols="80" class="large-text code"><?php echo $edit_template ? esc_textarea($edit_template->template_content) : ''; ?></textarea>
                                    
                                    <div class="srm-template-placeholders">
                                        <h4><?php _e('Available Placeholders:', 'student-result-management'); ?></h4>
                                        <div class="srm-placeholder-list">
                                            <div class="srm-placeholder-group">
                                                <strong><?php _e('Student Information:', 'student-result-management'); ?></strong>
                                                <code>{student_name}</code>, <code>{roll_number}</code>, <code>{class}</code>, <code>{section}</code>, <code>{email}</code>, <code>{phone}</code>
                                            </div>
                                            <div class="srm-placeholder-group">
                                                <strong><?php _e('Result Information:', 'student-result-management'); ?></strong>
                                                <code>{exam_name}</code>, <code>{exam_date}</code>, <code>{total_marks}</code>, <code>{obtained_marks}</code>, <code>{percentage}</code>, <code>{grade}</code>, <code>{status}</code>
                                            </div>
                                            <div class="srm-placeholder-group">
                                                <strong><?php _e('System Information:', 'student-result-management'); ?></strong>
                                                <code>{site_name}</code>, <code>{site_url}</code>, <code>{current_date}</code>, <code>{result_url}</code>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="description"><?php _e('Use the placeholders above to create dynamic content', 'student-result-management'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" name="save_template" class="button button-primary">
                            <?php echo $edit_template ? __('Update Template', 'student-result-management') : __('Create Template', 'student-result-management'); ?>
                        </button>
                        
                        <?php if ($edit_template): ?>
                            <a href="<?php echo admin_url('admin.php?page=srm-custom-templates'); ?>" class="button button-secondary">
                                <?php _e('Cancel', 'student-result-management'); ?>
                            </a>
                        <?php endif; ?>
                    </p>
                </form>
            </div>
            
            <!-- Template List -->
            <div class="srm-template-section">
                <h2><?php _e('Existing Templates', 'student-result-management'); ?></h2>
                
                <?php if (!empty($templates)): ?>
                    <div class="srm-template-list">
                        <?php foreach ($templates as $template): ?>
                            <div class="srm-template-item">
                                <div class="srm-template-header">
                                    <h3><?php echo esc_html($template->template_name); ?></h3>
                                    <span class="srm-template-type srm-type-<?php echo $template->template_type; ?>">
                                        <?php echo esc_html(ucfirst($template->template_type)); ?>
                                    </span>
                                </div>
                                
                                <div class="srm-template-content">
                                    <p><?php echo esc_html(wp_trim_words($template->template_content, 20)); ?></p>
                                </div>
                                
                                <div class="srm-template-meta">
                                    <span class="srm-template-date">
                                        <?php echo esc_html(date('M j, Y', strtotime($template->updated_at))); ?>
                                    </span>
                                    
                                    <div class="srm-template-actions">
                                        <a href="<?php echo admin_url('admin.php?page=srm-custom-templates&edit=' . $template->id); ?>" class="button button-small">
                                            <?php _e('Edit', 'student-result-management'); ?>
                                        </a>
                                        
                                        <form method="post" style="display: inline;">
                                            <?php wp_nonce_field('srm_template_action', 'srm_template_nonce'); ?>
                                            <input type="hidden" name="template_id" value="<?php echo $template->id; ?>">
                                            <button type="submit" name="delete_template" class="button button-small button-link-delete" onclick="return confirm('<?php _e('Are you sure you want to delete this template?', 'student-result-management'); ?>')">
                                                <?php _e('Delete', 'student-result-management'); ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p><?php _e('No templates found. Create your first template using the form on the left.', 'student-result-management'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Template Preview -->
        <div class="srm-template-section">
            <h2><?php _e('Template Preview', 'student-result-management'); ?></h2>
            <p><?php _e('Preview how your templates will look with sample data.', 'student-result-management'); ?></p>
            
            <div class="srm-template-preview">
                <div class="srm-preview-controls">
                    <select id="preview_template" class="regular-text">
                        <option value=""><?php _e('Select Template to Preview', 'student-result-management'); ?></option>
                        <?php foreach ($templates as $template): ?>
                            <option value="<?php echo $template->id; ?>"><?php echo esc_html($template->template_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="button" id="preview_button" class="button button-secondary">
                        <?php _e('Generate Preview', 'student-result-management'); ?>
                    </button>
                </div>
                
                <div id="preview_output" class="srm-preview-output" style="display: none;">
                    <h3><?php _e('Preview Output:', 'student-result-management'); ?></h3>
                    <div id="preview_content"></div>
                </div>
            </div>
        </div>
        
        <style>
        .srm-template-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .srm-template-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        
        .srm-template-editor {
            position: relative;
        }
        
        .srm-template-placeholders {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
        }
        
        .srm-placeholder-list {
            margin-top: 10px;
        }
        
        .srm-placeholder-group {
            margin-bottom: 10px;
        }
        
        .srm-placeholder-group code {
            background: #e9ecef;
            padding: 2px 4px;
            border-radius: 3px;
            margin: 0 2px;
        }
        
        .srm-template-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .srm-template-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .srm-template-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .srm-template-header h3 {
            margin: 0;
        }
        
        .srm-template-type {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .srm-type-email { background: #d4edda; color: #155724; }
        .srm-type-result_card { background: #cce5ff; color: #004085; }
        .srm-type-certificate { background: #fff3cd; color: #856404; }
        .srm-type-report { background: #f8d7da; color: #721c24; }
        
        .srm-template-content {
            margin-bottom: 10px;
        }
        
        .srm-template-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #666;
        }
        
        .srm-template-actions {
            display: flex;
            gap: 5px;
        }
        
        .srm-template-preview {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        
        .srm-preview-controls {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .srm-preview-output {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
        }
        
        @media (max-width: 768px) {
            .srm-template-sections {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Template preview functionality
            $('#preview_button').on('click', function() {
                var templateId = $('#preview_template').val();
                if (!templateId) {
                    alert('Please select a template to preview.');
                    return;
                }
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'srm_preview_template',
                        template_id: templateId,
                        nonce: '<?php echo wp_create_nonce("srm_template_nonce"); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#preview_content').html(response.data.preview);
                            $('#preview_output').show();
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('An error occurred while generating preview.');
                    }
                });
            });
            
            // Auto-save template content
            var contentTimeout;
            $('#template_content').on('input', function() {
                clearTimeout(contentTimeout);
                contentTimeout = setTimeout(function() {
                    // Auto-save functionality can be added here
                }, 3000);
            });
        });
        </script>
        
    <?php endif; ?>
</div>