<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

// Get statistics
$students_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_students");
$results_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_results");
$passed_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_results WHERE status = 'pass'");
$failed_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_results WHERE status = 'fail'");

$current_user_id = get_current_user_id();
$plugin_owner = get_option('srm_plugin_owner');
$is_owner = ($current_user_id == $plugin_owner);

// Recent students
$recent_students = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}srm_students ORDER BY created_at DESC LIMIT 5");

// Recent results
$recent_results = $wpdb->get_results("
    SELECT r.*, s.first_name, s.last_name, s.roll_number 
    FROM {$wpdb->prefix}srm_results r 
    LEFT JOIN {$wpdb->prefix}srm_students s ON r.student_id = s.id 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
?>

<div class="wrap srm-dashboard">
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
        <?php _e('Student Result Management Dashboard', 'student-result-management'); ?>
        <?php if ($is_owner): ?>
            <span class="srm-owner-badge"><?php _e('Owner Access', 'student-result-management'); ?></span>
        <?php endif; ?>
    </h1>
    
    <div class="srm-dashboard-widgets">
        <!-- Statistics Cards -->
        <div class="srm-stats-grid">
            <div class="srm-stat-card students">
                <div class="srm-stat-icon">
                    <span class="dashicons dashicons-groups"></span>
                </div>
                <div class="srm-stat-content">
                    <h3><?php echo number_format($students_count); ?></h3>
                    <p><?php _e('Total Students', 'student-result-management'); ?></p>
                </div>
            </div>
            
            <div class="srm-stat-card results">
                <div class="srm-stat-icon">
                    <span class="dashicons dashicons-awards"></span>
                </div>
                <div class="srm-stat-content">
                    <h3><?php echo number_format($results_count); ?></h3>
                    <p><?php _e('Total Results', 'student-result-management'); ?></p>
                </div>
            </div>
            
            <div class="srm-stat-card passed">
                <div class="srm-stat-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="srm-stat-content">
                    <h3><?php echo number_format($passed_count); ?></h3>
                    <p><?php _e('Students Passed', 'student-result-management'); ?></p>
                </div>
            </div>
            
            <div class="srm-stat-card failed">
                <div class="srm-stat-icon">
                    <span class="dashicons dashicons-dismiss"></span>
                </div>
                <div class="srm-stat-content">
                    <h3><?php echo number_format($failed_count); ?></h3>
                    <p><?php _e('Students Failed', 'student-result-management'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="srm-quick-actions">
            <h2><?php _e('Quick Actions', 'student-result-management'); ?></h2>
            <div class="srm-action-buttons">
                <a href="<?php echo admin_url('admin.php?page=srm-students&action=add'); ?>" class="button button-primary">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php _e('Add New Student', 'student-result-management'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=srm-results&action=add'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-awards"></span>
                    <?php _e('Add Result', 'student-result-management'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=srm-import-export'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-upload"></span>
                    <?php _e('Import Data', 'student-result-management'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=srm-settings'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php _e('Settings', 'student-result-management'); ?>
                </a>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="srm-recent-activity">
            <div class="srm-activity-section">
                <h3><?php _e('Recent Students', 'student-result-management'); ?></h3>
                <?php if (!empty($recent_students)): ?>
                    <div class="srm-recent-list">
                        <?php foreach ($recent_students as $student): ?>
                            <div class="srm-recent-item">
                                <div class="srm-recent-avatar">
                                    <?php if (!empty($student->profile_image)): ?>
                                        <img src="<?php echo esc_url($student->profile_image); ?>" alt="<?php echo esc_attr($student->first_name); ?>">
                                    <?php else: ?>
                                        <span class="srm-avatar-placeholder"><?php echo strtoupper(substr($student->first_name, 0, 1)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="srm-recent-info">
                                    <strong><?php echo esc_html($student->first_name . ' ' . $student->last_name); ?></strong>
                                    <span class="srm-roll-number"><?php echo esc_html($student->roll_number); ?></span>
                                    <span class="srm-class"><?php echo esc_html($student->class); ?></span>
                                </div>
                                <div class="srm-recent-actions">
                                    <a href="<?php echo admin_url('admin.php?page=srm-students&action=edit&id=' . $student->id); ?>" class="button button-small">
                                        <?php _e('Edit', 'student-result-management'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="srm-no-data"><?php _e('No students found. Start by adding your first student!', 'student-result-management'); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="srm-activity-section">
                <h3><?php _e('Recent Results', 'student-result-management'); ?></h3>
                <?php if (!empty($recent_results)): ?>
                    <div class="srm-recent-list">
                        <?php foreach ($recent_results as $result): ?>
                            <div class="srm-recent-item">
                                <div class="srm-recent-info">
                                    <strong><?php echo esc_html($result->first_name . ' ' . $result->last_name); ?></strong>
                                    <span class="srm-exam-name"><?php echo esc_html($result->exam_name); ?></span>
                                    <span class="srm-marks"><?php echo esc_html($result->obtained_marks . '/' . $result->total_marks); ?></span>
                                    <span class="srm-status status-<?php echo esc_attr($result->status); ?>">
                                        <?php echo esc_html(ucfirst($result->status)); ?>
                                    </span>
                                </div>
                                <div class="srm-recent-actions">
                                    <a href="<?php echo admin_url('admin.php?page=srm-results&action=edit&id=' . $result->id); ?>" class="button button-small">
                                        <?php _e('Edit', 'student-result-management'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="srm-no-data"><?php _e('No results found. Start by adding student results!', 'student-result-management'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Premium Features Promotion -->
        <?php if (!$is_owner): ?>
            <div class="srm-premium-promo">
                <h3><?php _e('Unlock Premium Features', 'student-result-management'); ?></h3>
                <p><?php _e('Get access to advanced features like PDF result cards, CSV import/export, student profile images, and more!', 'student-result-management'); ?></p>
                <div class="srm-premium-features">
                    <ul>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('PDF Result Card Generation', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('CSV Import/Export', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Student Profile Images', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Advanced Analytics', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Email Notifications', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Custom Result Templates', 'student-result-management'); ?></li>
                    </ul>
                </div>
                <a href="<?php echo admin_url('admin.php?page=srm-premium'); ?>" class="button button-primary srm-upgrade-btn">
                    <?php _e('Upgrade to Premium', 'student-result-management'); ?>
                </a>
            </div>
        <?php endif; ?>
        
        <!-- System Status -->
        <div class="srm-system-status">
            <h3><?php _e('System Status', 'student-result-management'); ?></h3>
            <div class="srm-status-items">
                <div class="srm-status-item">
                    <span class="srm-status-label"><?php _e('Plugin Version:', 'student-result-management'); ?></span>
                    <span class="srm-status-value"><?php echo SRM_PLUGIN_VERSION; ?></span>
                </div>
                <div class="srm-status-item">
                    <span class="srm-status-label"><?php _e('WordPress Version:', 'student-result-management'); ?></span>
                    <span class="srm-status-value"><?php echo get_bloginfo('version'); ?></span>
                </div>
                <div class="srm-status-item">
                    <span class="srm-status-label"><?php _e('PHP Version:', 'student-result-management'); ?></span>
                    <span class="srm-status-value"><?php echo PHP_VERSION; ?></span>
                </div>
                <div class="srm-status-item">
                    <span class="srm-status-label"><?php _e('License Status:', 'student-result-management'); ?></span>
                    <span class="srm-status-value <?php echo $is_owner ? 'status-premium' : 'status-free'; ?>">
                        <?php echo $is_owner ? __('Owner (Full Access)', 'student-result-management') : __('Free Version', 'student-result-management'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>