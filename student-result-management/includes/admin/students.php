<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

// Initialize variables
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$error = '';

// Handle form submissions
if ($_POST && isset($_POST['srm_nonce'])) {
    if (!isset($_POST['srm_nonce']) || !wp_verify_nonce($_POST['srm_nonce'], 'srm_student_action')) {
        $error = __('Security check failed.', 'student-result-management');
    } else {
        $students_table = $wpdb->prefix . 'srm_students';
        
        // Validate required fields
        $required_fields = array('roll_number', 'first_name', 'last_name', 'class');
        $validation_errors = array();
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $validation_errors[] = sprintf(__('%s is required.', 'student-result-management'), ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        if (!empty($validation_errors)) {
            $error = implode(' ', $validation_errors);
        } else {
            // Handle profile image upload (premium feature)
            $profile_image = '';
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $license_manager = new SRM_License_Manager();
                if ($license_manager->has_premium_access()) {
                    $upload_dir = wp_upload_dir();
                    $target_dir = $upload_dir['basedir'] . '/srm-profiles/';
                    wp_mkdir_p($target_dir);
                    
                    $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $filename = 'profile_' . time() . '_' . sanitize_file_name($_FILES['profile_image']['name']);
                        $target_file = $target_dir . $filename;
                        
                        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                            $profile_image = $upload_dir['baseurl'] . '/srm-profiles/' . $filename;
                        }
                    }
                }
            }
            
            // Prepare student data
            $student_data = array(
                'roll_number' => sanitize_text_field($_POST['roll_number']),
                'first_name' => sanitize_text_field($_POST['first_name']),
                'last_name' => sanitize_text_field($_POST['last_name']),
                'email' => !empty($_POST['email']) ? sanitize_email($_POST['email']) : '',
                'phone' => sanitize_text_field($_POST['phone']),
                'class' => sanitize_text_field($_POST['class']),
                'section' => sanitize_text_field($_POST['section']),
                'date_of_birth' => !empty($_POST['date_of_birth']) ? sanitize_text_field($_POST['date_of_birth']) : null,
                'profile_image' => $profile_image
            );
            
            // Remove empty values except for required fields
            $student_data = array_filter($student_data, function($value) {
                return $value !== '';
            });
            
            if ($action === 'add') {
                // Check student limit for free users
                $license_manager = new SRM_License_Manager();
                if (!$license_manager->can_add_student()) {
                    $student_count = $license_manager->get_student_count();
                    $error = sprintf(__('You have reached the limit of 20 students for free users. Current count: %d. Upgrade to premium for unlimited students.', 'student-result-management'), $student_count);
                } else {
                    // Check if roll number already exists
                    $existing = $wpdb->get_var($wpdb->prepare(
                        "SELECT id FROM $students_table WHERE roll_number = %s",
                        $student_data['roll_number']
                    ));
                    
                    if ($existing) {
                        $error = __('A student with this roll number already exists.', 'student-result-management');
                    } else {
                        $result = $wpdb->insert($students_table, $student_data);
                        if ($result) {
                            $message = __('Student added successfully!', 'student-result-management');
                            $action = 'list';
                        } else {
                            $error = __('Error adding student: ', 'student-result-management') . $wpdb->last_error;
                        }
                    }
                }
            } elseif ($action === 'edit' && $student_id) {
                // Check if roll number already exists for other students
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM $students_table WHERE roll_number = %s AND id != %d",
                    $student_data['roll_number'],
                    $student_id
                ));
                
                if ($existing) {
                    $error = __('A student with this roll number already exists.', 'student-result-management');
                } else {
                    $result = $wpdb->update($students_table, $student_data, array('id' => $student_id));
                    if ($result !== false) {
                        $message = __('Student updated successfully!', 'student-result-management');
                        $action = 'list';
                    } else {
                        $error = __('Error updating student: ', 'student-result-management') . $wpdb->last_error;
                    }
                }
            }
        }
    }
}

// Handle delete action
if ($action === 'delete' && $student_id && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_student_' . $student_id)) {
    $result = $wpdb->delete($wpdb->prefix . 'srm_students', array('id' => $student_id));
    if ($result) {
        $message = __('Student deleted successfully!', 'student-result-management');
    } else {
        $error = __('Error deleting student: ', 'student-result-management') . $wpdb->last_error;
    }
    $action = 'list';
}

// Get student data for editing
$student = null;
if ($action === 'edit' && $student_id) {
    $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}srm_students WHERE id = %d", $student_id));
    if (!$student) {
        $error = __('Student not found.', 'student-result-management');
        $action = 'list';
    }
}
?>

<div class="wrap srm-students">
    <h1 class="wp-heading-inline">
        <?php 
        if ($action === 'add') {
            _e('Add New Student', 'student-result-management');
        } elseif ($action === 'edit') {
            _e('Edit Student', 'student-result-management');
        } else {
            _e('Students', 'student-result-management');
        }
        ?>
    </h1>
    
    <?php if ($action === 'list'): ?>
        <?php
        // Display student count and limit information
        $license_manager = new SRM_License_Manager();
        $student_count = $license_manager->get_student_count();
        $remaining_slots = $license_manager->get_remaining_student_slots();
        $can_add = $license_manager->can_add_student();
        ?>
        
        <div class="srm-student-limit-info">
            <div class="srm-limit-card">
                <h3><?php _e('Student Limit Status', 'student-result-management'); ?></h3>
                <div class="srm-limit-details">
                    <p><strong><?php _e('Current Students:', 'student-result-management'); ?></strong> <?php echo $student_count; ?></p>
                    <?php if ($license_manager->has_premium_access()): ?>
                        <p><strong><?php _e('Status:', 'student-result-management'); ?></strong> <span class="srm-status-premium"><?php _e('Premium - Unlimited', 'student-result-management'); ?></span></p>
                    <?php else: ?>
                        <p><strong><?php _e('Remaining Slots:', 'student-result-management'); ?></strong> <?php echo $remaining_slots; ?> / 20</p>
                        <?php if (!$can_add): ?>
                            <p class="srm-limit-warning"><?php _e('⚠️ You have reached the 20 student limit for free users. Upgrade to premium for unlimited students.', 'student-result-management'); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if ($can_add): ?>
            <a href="<?php echo admin_url('admin.php?page=srm-students&action=add'); ?>" class="page-title-action">
                <?php _e('Add New', 'student-result-management'); ?>
            </a>
        <?php else: ?>
            <a href="<?php echo admin_url('admin.php?page=srm-enhanced-premium'); ?>" class="page-title-action button-primary">
                <?php _e('Upgrade to Premium', 'student-result-management'); ?>
            </a>
        <?php endif; ?>
    <?php else: ?>
        <a href="<?php echo admin_url('admin.php?page=srm-students'); ?>" class="page-title-action">
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
            <form method="post" class="srm-student-form" enctype="multipart/form-data">
                <?php wp_nonce_field('srm_student_action', 'srm_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="roll_number"><?php _e('Roll Number', 'student-result-management'); ?> *</label>
                        </th>
                        <td>
                            <input type="text" name="roll_number" id="roll_number" class="regular-text" 
                                   value="<?php echo $student ? esc_attr($student->roll_number) : ''; ?>" required>
                            <p class="description"><?php _e('Unique roll number for the student.', 'student-result-management'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="first_name"><?php _e('First Name', 'student-result-management'); ?> *</label>
                        </th>
                        <td>
                            <input type="text" name="first_name" id="first_name" class="regular-text" 
                                   value="<?php echo $student ? esc_attr($student->first_name) : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="last_name"><?php _e('Last Name', 'student-result-management'); ?> *</label>
                        </th>
                        <td>
                            <input type="text" name="last_name" id="last_name" class="regular-text" 
                                   value="<?php echo $student ? esc_attr($student->last_name) : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="email"><?php _e('Email', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="email" name="email" id="email" class="regular-text" 
                                   value="<?php echo $student ? esc_attr($student->email) : ''; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="phone"><?php _e('Phone', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="phone" id="phone" class="regular-text" 
                                   value="<?php echo $student ? esc_attr($student->phone) : ''; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="class"><?php _e('Class', 'student-result-management'); ?> *</label>
                        </th>
                        <td>
                            <input type="text" name="class" id="class" class="regular-text" 
                                   value="<?php echo $student ? esc_attr($student->class) : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="section"><?php _e('Section', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="section" id="section" class="regular-text" 
                                   value="<?php echo $student ? esc_attr($student->section) : ''; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="date_of_birth"><?php _e('Date of Birth', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <input type="date" name="date_of_birth" id="date_of_birth" class="regular-text" 
                                   value="<?php echo $student ? esc_attr($student->date_of_birth) : ''; ?>">
                        </td>
                    </tr>
                    <?php 
                    // Check if user has premium access for profile image upload
                    $license_manager = new SRM_License_Manager();
                    $has_premium = $license_manager->has_premium_access();
                    ?>
                    <tr>
                        <th scope="row">
                            <label for="profile_image"><?php _e('Profile Image', 'student-result-management'); ?></label>
                        </th>
                        <td>
                            <?php if ($has_premium): ?>
                                <input type="file" name="profile_image" id="profile_image" accept="image/*">
                                <?php if ($student && !empty($student->profile_image)): ?>
                                    <br><br>
                                    <img src="<?php echo esc_url($student->profile_image); ?>" alt="Current profile image" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd;">
                                    <p class="description"><?php _e('Current profile image. Upload a new image to replace it.', 'student-result-management'); ?></p>
                                <?php endif; ?>
                                <p class="description"><?php _e('Upload a profile image for the student (Premium feature).', 'student-result-management'); ?></p>
                            <?php else: ?>
                                <p class="description" style="color: #d63638;">
                                    <strong><?php _e('Premium Feature:', 'student-result-management'); ?></strong> 
                                    <?php _e('Profile image upload is available with premium license. ', 'student-result-management'); ?>
                                    <a href="<?php echo admin_url('admin.php?page=srm-premium'); ?>"><?php _e('Upgrade to Premium', 'student-result-management'); ?></a>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button button-primary" 
                           value="<?php echo $action === 'edit' ? __('Update Student', 'student-result-management') : __('Add Student', 'student-result-management'); ?>">
                </p>
            </form>
        </div>
        
    <?php else: ?>
        <!-- Students List -->
        <?php
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $where_clause = '';
        $search_params = array();
        
        if ($search) {
            $where_clause = " WHERE first_name LIKE %s OR last_name LIKE %s OR roll_number LIKE %s OR email LIKE %s";
            $search_params = array("%$search%", "%$search%", "%$search%", "%$search%");
        }
        
        $total_query = "SELECT COUNT(*) FROM {$wpdb->prefix}srm_students" . $where_clause;
        $students_query = "SELECT * FROM {$wpdb->prefix}srm_students" . $where_clause . " ORDER BY created_at DESC LIMIT %d OFFSET %d";
        
        if ($search) {
            $total_students = $wpdb->get_var($wpdb->prepare($total_query, $search_params));
            $students = $wpdb->get_results($wpdb->prepare($students_query, array_merge($search_params, array($per_page, $offset))));
        } else {
            $total_students = $wpdb->get_var($total_query);
            $students = $wpdb->get_results($wpdb->prepare($students_query, array($per_page, $offset)));
        }
        
        $total_pages = ceil($total_students / $per_page);
        ?>
        
        <!-- Search Form -->
        <div class="srm-search-form">
            <form method="get">
                <input type="hidden" name="page" value="srm-students">
                <p class="search-box">
                    <label class="screen-reader-text" for="student-search-input"><?php _e('Search Students:', 'student-result-management'); ?></label>
                    <input type="search" id="student-search-input" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search students...', 'student-result-management'); ?>">
                    <input type="submit" id="search-submit" class="button" value="<?php _e('Search Students', 'student-result-management'); ?>">
                </p>
            </form>
        </div>
        
        <!-- Students Table -->
        <div class="srm-table-container">
            <?php if (!empty($students)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col"><?php _e('Roll Number', 'student-result-management'); ?></th>
                            <th scope="col"><?php _e('Name', 'student-result-management'); ?></th>
                            <th scope="col"><?php _e('Class', 'student-result-management'); ?></th>
                            <th scope="col"><?php _e('Email', 'student-result-management'); ?></th>
                            <th scope="col"><?php _e('Phone', 'student-result-management'); ?></th>
                            <th scope="col"><?php _e('Added', 'student-result-management'); ?></th>
                            <th scope="col"><?php _e('Actions', 'student-result-management'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><strong><?php echo esc_html($student->roll_number); ?></strong></td>
                                <td>
                                    <?php echo esc_html($student->first_name . ' ' . $student->last_name); ?>
                                    <?php if ($student->section): ?>
                                        <br><small><?php echo esc_html($student->section); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($student->class); ?></td>
                                <td><?php echo esc_html($student->email); ?></td>
                                <td><?php echo esc_html($student->phone); ?></td>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($student->created_at)); ?></td>
                                <td>
                                    <div class="row-actions">
                                        <span class="edit">
                                            <a href="<?php echo admin_url('admin.php?page=srm-students&action=edit&id=' . $student->id); ?>">
                                                <?php _e('Edit', 'student-result-management'); ?>
                                            </a>
                                        </span>
                                        |
                                        <span class="view">
                                            <a href="<?php echo admin_url('admin.php?page=srm-results&student_id=' . $student->id); ?>">
                                                <?php _e('Results', 'student-result-management'); ?>
                                            </a>
                                        </span>
                                        |
                                        <span class="delete">
                                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=srm-students&action=delete&id=' . $student->id), 'delete_student_' . $student->id); ?>" 
                                               onclick="return confirm('<?php _e('Are you sure you want to delete this student?', 'student-result-management'); ?>')">
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
                                <?php printf(__('%s items', 'student-result-management'), number_format_i18n($total_students)); ?>
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
                    <p><?php _e('No students found.', 'student-result-management'); ?></p>
                    <?php if ($search): ?>
                        <p><a href="<?php echo admin_url('admin.php?page=srm-students'); ?>"><?php _e('Show all students', 'student-result-management'); ?></a></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.srm-student-limit-info {
    margin: 20px 0;
}

.srm-limit-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-width: 400px;
}

.srm-limit-card h3 {
    margin: 0 0 15px 0;
    color: #23282d;
    font-size: 16px;
}

.srm-limit-details p {
    margin: 8px 0;
    font-size: 14px;
}

.srm-status-premium {
    background: #d4edda;
    color: #155724;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 12px;
    text-transform: uppercase;
}

.srm-limit-warning {
    color: #721c24;
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    padding: 10px;
    border-radius: 4px;
    margin-top: 10px;
    font-weight: bold;
}

.srm-limit-details strong {
    color: #23282d;
}
</style>

