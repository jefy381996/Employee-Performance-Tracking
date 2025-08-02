<?php
if (!defined('ABSPATH')) exit;

// Remove output buffering - not needed

global $wpdb;

$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$result_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (!wp_verify_nonce($_POST['srm_nonce'], 'srm_result_action')) {
        $error = __('Security check failed.', 'student-result-management');
    } else {
        $results_table = $wpdb->prefix . 'srm_results';
        
        // Calculate grade and status
        $total_marks = intval($_POST['total_marks']);
        $obtained_marks = intval($_POST['obtained_marks']);
        $percentage = ($total_marks > 0) ? round(($obtained_marks / $total_marks) * 100, 2) : 0;
        
        // Get passing marks from settings
        $passing_marks = intval(get_option('srm_passing_marks', 40));
        $status = ($percentage >= $passing_marks) ? 'pass' : 'fail';
        
        // Calculate grade
        $grade = '';
        if ($percentage >= 90) $grade = 'A+';
        elseif ($percentage >= 80) $grade = 'A';
        elseif ($percentage >= 70) $grade = 'B+';
        elseif ($percentage >= 60) $grade = 'B';
        elseif ($percentage >= 50) $grade = 'C+';
        elseif ($percentage >= 40) $grade = 'C';
        else $grade = 'F';
        
        // Handle subjects data
        $subjects = array();
        if (isset($_POST['subjects']) && is_array($_POST['subjects'])) {
            foreach ($_POST['subjects'] as $subject) {
                if (!empty($subject['name']) && !empty($subject['marks'])) {
                    $subjects[] = array(
                        'name' => sanitize_text_field($subject['name']),
                        'total_marks' => intval($subject['total_marks']),
                        'obtained_marks' => intval($subject['marks']),
                        'grade' => sanitize_text_field($subject['grade'])
                    );
                }
            }
        }
        
        // Handle certificate PDF upload (premium feature)
        $certificate_pdf = '';
        if (isset($_FILES['certificate_pdf']) && $_FILES['certificate_pdf']['error'] === UPLOAD_ERR_OK) {
            $license_manager = new SRM_License_Manager();
            if ($license_manager->has_premium_access()) {
                $upload_dir = wp_upload_dir();
                $target_dir = $upload_dir['basedir'] . '/srm-certificates/';
                wp_mkdir_p($target_dir);
                
                $file_extension = strtolower(pathinfo($_FILES['certificate_pdf']['name'], PATHINFO_EXTENSION));
                if ($file_extension === 'pdf') {
                    $filename = 'certificate_' . time() . '_' . sanitize_file_name($_FILES['certificate_pdf']['name']);
                    $target_file = $target_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['certificate_pdf']['tmp_name'], $target_file)) {
                        $certificate_pdf = $upload_dir['baseurl'] . '/srm-certificates/' . $filename;
                    }
                }
            }
        }
        
        $result_data = array(
            'student_id' => intval($_POST['student_id']),
            'exam_name' => sanitize_text_field($_POST['exam_name']),
            'exam_date' => sanitize_text_field($_POST['exam_date']),
            'total_marks' => $total_marks,
            'obtained_marks' => $obtained_marks,
            'percentage' => $percentage,
            'grade' => $grade,
            'status' => $status,
            'subjects' => json_encode($subjects),
            'certificate_pdf' => $certificate_pdf
        );
        
        if ($action === 'add') {
            $result = $wpdb->insert($results_table, $result_data);
            if ($result) {
                $message = __('Result added successfully!', 'student-result-management');
                $action = 'list';
            } else {
                $error = __('Error adding result.', 'student-result-management');
            }
        } elseif ($action === 'edit' && $result_id) {
            $result = $wpdb->update($results_table, $result_data, array('id' => $result_id));
            if ($result !== false) {
                $message = __('Result updated successfully!', 'student-result-management');
                $action = 'list';
            } else {
                $error = __('Error updating result.', 'student-result-management');
            }
        }
    }
}

// Handle delete action
if ($action === 'delete' && $result_id && wp_verify_nonce($_GET['_wpnonce'], 'delete_result_' . $result_id)) {
    $result = $wpdb->delete($wpdb->prefix . 'srm_results', array('id' => $result_id));
    if ($result) {
        $message = __('Result deleted successfully!', 'student-result-management');
    } else {
        $error = __('Error deleting result.', 'student-result-management');
    }
    $action = 'list';
}

// Get result data for editing
$result = null;
$selected_student = null;
if ($action === 'edit' && $result_id) {
    $result = $wpdb->get_row($wpdb->prepare("
        SELECT r.*, s.first_name, s.last_name, s.roll_number 
        FROM {$wpdb->prefix}srm_results r 
        LEFT JOIN {$wpdb->prefix}srm_students s ON r.student_id = s.id 
        WHERE r.id = %d", $result_id));
    if (!$result) {
        $error = __('Result not found.', 'student-result-management');
        $action = 'list';
    }
}

// Get selected student for add form
if ($action === 'add' && $student_id) {
    $selected_student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}srm_students WHERE id = %d", $student_id));
}

// Get all students for dropdown
$students = $wpdb->get_results("SELECT id, roll_number, first_name, last_name FROM {$wpdb->prefix}srm_students ORDER BY first_name ASC");
?>

<div class="wrap srm-results">
    <?php 
    // Display prominent contact notice for free users
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
    ?>
    <h1 class="wp-heading-inline">
        <?php 
        if ($action === 'add') {
            _e('Add New Result', 'student-result-management');
        } elseif ($action === 'edit') {
            _e('Edit Result', 'student-result-management');
        } else {
            _e('Student Results', 'student-result-management');
        }
        ?>
    </h1>
    
    <?php if ($action === 'list'): ?>
        <a href="<?php echo admin_url('admin.php?page=srm-results&action=add'); ?>" class="page-title-action">
            <?php _e('Add New', 'student-result-management'); ?>
        </a>
    <?php else: ?>
        <a href="<?php echo admin_url('admin.php?page=srm-results'); ?>" class="page-title-action">
            <?php _e('Back to List', 'student-result-management'); ?>
        </a>
    <?php endif; ?>
    
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
    
    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit Form -->
        <div class="srm-form-container">
            <form method="post" class="srm-result-form" enctype="multipart/form-data">
                <?php wp_nonce_field('srm_result_action', 'srm_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="student_id"><?php _e('Student', 'student-result-management'); ?> *</label>
                        </th>
                        <td>
                            <select name="student_id" id="student_id" class="regular-text" required <?php echo $result ? 'disabled' : ''; ?>>
                                <option value=""><?php _e('Select Student', 'student-result-management'); ?></option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student->id; ?>" 
                                            <?php selected($result ? $result->student_id : ($selected_student ? $selected_student->id : ''), $student->id); ?>>
                                        <?php echo esc_html($student->roll_number . ' - ' . $student->first_name . ' ' . $student->last_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($result): ?>
                                <input type="hidden" name="student_id" value="<?php echo $result->student_id; ?>">
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="exam_name"><?php _e('Exam Name', 'student-result-management'); ?> *</label>
                        </th>
                        <td>
                            <input type="text" name="exam_name" id="exam_name" class="regular-text" 
                                   value="<?php echo $result ? esc_attr($result->exam_name) : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="exam_date"><?php _e('Exam Date', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="date" name="exam_date" id="exam_date" class="regular-text" 
                                   value="<?php echo $result ? esc_attr($result->exam_date) : ''; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="total_marks"><?php _e('Total Marks', 'student-result-management'); ?> *</label>
                        </th>
                        <td>
                            <input type="number" name="total_marks" id="total_marks" class="regular-text" 
                                   value="<?php echo $result ? esc_attr($result->total_marks) : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="obtained_marks"><?php _e('Obtained Marks', 'student-result-management'); ?> *</label>
                        </th>
                        <td>
                            <input type="number" name="obtained_marks" id="obtained_marks" class="regular-text" 
                                   value="<?php echo $result ? esc_attr($result->obtained_marks) : ''; ?>" required>
                        </td>
                    </tr>
                    <?php 
                    // Check if user has premium access for certificate PDF upload
                    $license_manager = new SRM_License_Manager();
                    $has_premium = $license_manager->has_premium_access();
                    ?>
                    <tr>
                        <th scope="row">
                            <label for="certificate_pdf"><?php _e('Certificate PDF', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <?php if ($has_premium): ?>
                                <input type="file" name="certificate_pdf" id="certificate_pdf" accept=".pdf">
                                <?php if ($result && !empty($result->certificate_pdf)): ?>
                                    <br><br>
                                    <a href="<?php echo esc_url($result->certificate_pdf); ?>" target="_blank" class="button">
                                        <?php _e('View Current Certificate', 'student-result-management'); ?>
                                    </a>
                                    <p class="description"><?php _e('Current certificate PDF. Upload a new PDF to replace it.', 'student-result-management'); ?></p>
                                <?php endif; ?>
                                <p class="description"><?php _e('Upload a certificate PDF for this result (Premium feature). Students can download this certificate when checking their results.', 'student-result-management'); ?></p>
                            <?php else: ?>
                                <p class="description" style="color: #d63638;">
                                    <strong><?php _e('Premium Feature:', 'student-result-management'); ?></strong> 
                                    <?php _e('Certificate PDF upload is available with premium license. ', 'student-result-management'); ?>
                                    <a href="<?php echo admin_url('admin.php?page=srm-premium'); ?>"><?php _e('Upgrade to Premium', 'student-result-management'); ?></a>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <!-- Subject-wise Marks -->
                <h3><?php _e('Subject-wise Marks', 'student-result-management'); ?></h3>
                <div id="subjects-container">
                    <?php
                    $subjects = array();
                    if ($result && !empty($result->subjects)) {
                        $subjects = json_decode($result->subjects, true) ?: array();
                    }
                    
                    if (empty($subjects)) {
                        $subjects = array(array('name' => '', 'total_marks' => '', 'obtained_marks' => '', 'grade' => ''));
                    }
                    
                    foreach ($subjects as $index => $subject):
                    ?>
                        <div class="subject-row">
                            <input type="text" name="subjects[<?php echo $index; ?>][name]" placeholder="<?php _e('Subject Name', 'student-result-management'); ?>" 
                                   value="<?php echo esc_attr($subject['name']); ?>" class="regular-text">
                            <input type="number" name="subjects[<?php echo $index; ?>][total_marks]" placeholder="<?php _e('Total', 'student-result-management'); ?>" 
                                   value="<?php echo esc_attr($subject['total_marks']); ?>" class="small-text">
                            <input type="number" name="subjects[<?php echo $index; ?>][marks]" placeholder="<?php _e('Obtained', 'student-result-management'); ?>" 
                                   value="<?php echo esc_attr($subject['obtained_marks']); ?>" class="small-text">
                            <input type="text" name="subjects[<?php echo $index; ?>][grade]" placeholder="<?php _e('Grade', 'student-result-management'); ?>" 
                                   value="<?php echo esc_attr($subject['grade']); ?>" class="small-text">
                            <button type="button" class="button remove-subject"><?php _e('Remove', 'student-result-management'); ?></button>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <p>
                    <button type="button" id="add-subject" class="button"><?php _e('Add Subject', 'student-result-management'); ?></button>
                </p>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button button-primary" 
                           value="<?php echo $action === 'edit' ? __('Update Result', 'student-result-management') : __('Add Result', 'student-result-management'); ?>">
                </p>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var subjectIndex = <?php echo count($subjects); ?>;
            
            $('#add-subject').click(function() {
                var html = '<div class="subject-row">' +
                    '<input type="text" name="subjects[' + subjectIndex + '][name]" placeholder="<?php _e('Subject Name', 'student-result-management'); ?>" class="regular-text">' +
                    '<input type="number" name="subjects[' + subjectIndex + '][total_marks]" placeholder="<?php _e('Total', 'student-result-management'); ?>" class="small-text">' +
                    '<input type="number" name="subjects[' + subjectIndex + '][marks]" placeholder="<?php _e('Obtained', 'student-result-management'); ?>" class="small-text">' +
                    '<input type="text" name="subjects[' + subjectIndex + '][grade]" placeholder="<?php _e('Grade', 'student-result-management'); ?>" class="small-text">' +
                    '<button type="button" class="button remove-subject"><?php _e('Remove', 'student-result-management'); ?></button>' +
                    '</div>';
                $('#subjects-container').append(html);
                subjectIndex++;
            });
            
            $(document).on('click', '.remove-subject', function() {
                $(this).closest('.subject-row').remove();
            });
        });
        </script>
        
    <?php else: ?>
        <!-- Results List -->
        <?php
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $student_filter = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
        
        $where_conditions = array();
        $search_params = array();
        
        if ($search) {
            $where_conditions[] = "(s.first_name LIKE %s OR s.last_name LIKE %s OR s.roll_number LIKE %s OR r.exam_name LIKE %s)";
            $search_params = array_merge($search_params, array("%$search%", "%$search%", "%$search%", "%$search%"));
        }
        
        if ($status_filter) {
            $where_conditions[] = "r.status = %s";
            $search_params[] = $status_filter;
        }
        
        if ($student_filter) {
            $where_conditions[] = "r.student_id = %d";
            $search_params[] = $student_filter;
        }
        
        $where_clause = !empty($where_conditions) ? " WHERE " . implode(" AND ", $where_conditions) : "";
        
        $total_query = "SELECT COUNT(*) FROM {$wpdb->prefix}srm_results r LEFT JOIN {$wpdb->prefix}srm_students s ON r.student_id = s.id" . $where_clause;
        $results_query = "SELECT r.*, s.first_name, s.last_name, s.roll_number FROM {$wpdb->prefix}srm_results r LEFT JOIN {$wpdb->prefix}srm_students s ON r.student_id = s.id" . $where_clause . " ORDER BY r.created_at DESC LIMIT %d OFFSET %d";
        
        if (!empty($search_params)) {
            $total_results = $wpdb->get_var($wpdb->prepare($total_query, $search_params));
            $results = $wpdb->get_results($wpdb->prepare($results_query, array_merge($search_params, array($per_page, $offset))));
        } else {
            $total_results = $wpdb->get_var($total_query);
            $results = $wpdb->get_results($wpdb->prepare($results_query, array($per_page, $offset)));
        }
        
        $total_pages = ceil($total_results / $per_page);
        ?>
        
        <!-- Filters -->
        <div class="srm-filters">
            <form method="get">
                <input type="hidden" name="page" value="srm-results">
                
                <select name="status">
                    <option value=""><?php _e('All Status', 'student-result-management'); ?></option>
                    <option value="pass" <?php selected($status_filter, 'pass'); ?>><?php _e('Pass', 'student-result-management'); ?></option>
                    <option value="fail" <?php selected($status_filter, 'fail'); ?>><?php _e('Fail', 'student-result-management'); ?></option>
                    <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php _e('Pending', 'student-result-management'); ?></option>
                </select>
                
                <select name="student_id">
                    <option value=""><?php _e('All Students', 'student-result-management'); ?></option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student->id; ?>" <?php selected($student_filter, $student->id); ?>>
                            <?php echo esc_html($student->roll_number . ' - ' . $student->first_name . ' ' . $student->last_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search results...', 'student-result-management'); ?>">
                <input type="submit" class="button" value="<?php _e('Filter', 'student-result-management'); ?>">
                
                <?php if ($search || $status_filter || $student_filter): ?>
                    <a href="<?php echo admin_url('admin.php?page=srm-results'); ?>" class="button"><?php _e('Clear Filters', 'student-result-management'); ?></a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Results Table -->
        <div class="srm-table-container">
            <?php if (!empty($results)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col"><?php _e('Student', 'student-result-management'); ?></th>
                            <th scope="col"><?php _e('Exam', 'student-result-management'); ?></th>
                            <th scope="col"><?php _e('Marks', 'student-result-management'); ?></th>
                            <th scope="col"><?php _e('Percentage', 'student-result-management'); ?></th>
                            <th scope="col"><?php _e('Grade', 'student-result-management'); ?></th>
                            <th scope="col"><?php _e('Status', 'student-result-management'); ?></th>
                            <th scope="col"><?php _e('Date', 'student-result-management'); ?></th>
                            <th scope="col"><?php _e('Actions', 'student-result-management'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result_item): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($result_item->first_name . ' ' . $result_item->last_name); ?></strong>
                                    <br><small><?php echo esc_html($result_item->roll_number); ?></small>
                                </td>
                                <td><?php echo esc_html($result_item->exam_name); ?></td>
                                <td><?php echo esc_html($result_item->obtained_marks . '/' . $result_item->total_marks); ?></td>
                                <td><?php echo esc_html($result_item->percentage . '%'); ?></td>
                                <td><span class="grade-badge grade-<?php echo strtolower($result_item->grade); ?>"><?php echo esc_html($result_item->grade); ?></span></td>
                                <td><span class="status-badge status-<?php echo esc_attr($result_item->status); ?>"><?php echo esc_html(ucfirst($result_item->status)); ?></span></td>
                                <td><?php echo $result_item->exam_date ? date_i18n(get_option('date_format'), strtotime($result_item->exam_date)) : '-'; ?></td>
                                <td>
                                    <div class="row-actions">
                                        <span class="edit">
                                            <a href="<?php echo admin_url('admin.php?page=srm-results&action=edit&id=' . $result_item->id); ?>">
                                                <?php _e('Edit', 'student-result-management'); ?>
                                            </a>
                                        </span>
                                        |
                                        <span class="view">
                                            <a href="#" class="view-result" data-id="<?php echo $result_item->id; ?>">
                                                <?php _e('View', 'student-result-management'); ?>
                                            </a>
                                        </span>
                                        |
                                        <span class="delete">
                                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=srm-results&action=delete&id=' . $result_item->id), 'delete_result_' . $result_item->id); ?>" 
                                               onclick="return confirm('<?php _e('Are you sure you want to delete this result?', 'student-result-management'); ?>')">
                                                <?php _e('Delete', 'student-result-management'); ?>
                                            </a>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <span class="displaying-num">
                                <?php printf(__('%s items', 'student-result-management'), number_format_i18n($total_results)); ?>
                            </span>
                            <?php
                            $pagination_args = array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => __('&laquo;', 'student-result-management'),
                                'next_text' => __('&raquo;', 'student-result-management'),
                                'total' => $total_pages,
                                'current' => $current_page
                            );
                            echo paginate_links($pagination_args);
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="srm-no-data">
                    <p><?php _e('No results found.', 'student-result-management'); ?></p>
                    <?php if ($search || $status_filter || $student_filter): ?>
                        <p><a href="<?php echo admin_url('admin.php?page=srm-results'); ?>"><?php _e('Show all results', 'student-result-management'); ?></a></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>