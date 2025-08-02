<?php
if (!defined('ABSPATH')) exit;

require_once SRM_PLUGIN_PATH . 'includes/admin/license-manager.php';
$license_manager = new SRM_License_Manager();

$current_user_id = get_current_user_id();
$plugin_owner = get_option('srm_plugin_owner');
$is_owner = ($current_user_id == $plugin_owner);
$has_premium = $license_manager->has_premium_access();

global $wpdb;

// Get analytics data
$total_students = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_students");
$total_results = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_results");
$total_classes = $wpdb->get_var("SELECT COUNT(DISTINCT class) FROM {$wpdb->prefix}srm_students");

// Performance analytics
$performance_data = $wpdb->get_results("
    SELECT 
        r.grade,
        COUNT(*) as count,
        AVG(r.percentage) as avg_percentage
    FROM {$wpdb->prefix}srm_results r
    GROUP BY r.grade
    ORDER BY r.grade
");

// Class-wise performance
$class_performance = $wpdb->get_results("
    SELECT 
        s.class,
        COUNT(DISTINCT s.id) as student_count,
        COUNT(r.id) as result_count,
        AVG(r.percentage) as avg_percentage
    FROM {$wpdb->prefix}srm_students s
    LEFT JOIN {$wpdb->prefix}srm_results r ON s.id = r.student_id
    GROUP BY s.class
    ORDER BY s.class
");

// Recent activity
$recent_results = $wpdb->get_results("
    SELECT r.*, s.first_name, s.last_name, s.roll_number
    FROM {$wpdb->prefix}srm_results r
    LEFT JOIN {$wpdb->prefix}srm_students s ON r.student_id = s.id
    ORDER BY r.created_at DESC
    LIMIT 10
");

// Monthly trends
$monthly_trends = $wpdb->get_results("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count,
        AVG(percentage) as avg_percentage
    FROM {$wpdb->prefix}srm_results
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
");
?>

<div class="wrap srm-advanced-analytics">
    <h1><?php _e('Advanced Analytics', 'student-result-management'); ?></h1>
    
    <?php if (!$has_premium): ?>
        <div class="notice notice-warning">
            <h3><?php _e('Premium Feature', 'student-result-management'); ?></h3>
            <p><?php _e('Advanced Analytics is a premium feature. Please upgrade to access detailed analytics and insights.', 'student-result-management'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=srm-premium'); ?>" class="button button-primary">
                <?php _e('Upgrade to Premium', 'student-result-management'); ?>
            </a>
        </div>
    <?php else: ?>
        
        <!-- Overview Cards -->
        <div class="srm-analytics-overview">
            <div class="srm-analytics-card">
                <div class="srm-card-icon">
                    <span class="dashicons dashicons-groups"></span>
                </div>
                <div class="srm-card-content">
                    <h3><?php echo number_format($total_students); ?></h3>
                    <p><?php _e('Total Students', 'student-result-management'); ?></p>
                </div>
            </div>
            
            <div class="srm-analytics-card">
                <div class="srm-card-icon">
                    <span class="dashicons dashicons-chart-line"></span>
                </div>
                <div class="srm-card-content">
                    <h3><?php echo number_format($total_results); ?></h3>
                    <p><?php _e('Total Results', 'student-result-management'); ?></p>
                </div>
            </div>
            
            <div class="srm-analytics-card">
                <div class="srm-card-icon">
                    <span class="dashicons dashicons-building"></span>
                </div>
                <div class="srm-card-content">
                    <h3><?php echo number_format($total_classes); ?></h3>
                    <p><?php _e('Total Classes', 'student-result-management'); ?></p>
                </div>
            </div>
            
            <div class="srm-analytics-card">
                <div class="srm-card-icon">
                    <span class="dashicons dashicons-chart-pie"></span>
                </div>
                <div class="srm-card-content">
                    <h3><?php echo $total_results > 0 ? number_format($total_results / $total_students, 1) : '0'; ?></h3>
                    <p><?php _e('Avg Results/Student', 'student-result-management'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="srm-analytics-charts">
            <div class="srm-chart-section">
                <h2><?php _e('Performance Distribution', 'student-result-management'); ?></h2>
                <div class="srm-chart-container">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
            
            <div class="srm-chart-section">
                <h2><?php _e('Class-wise Performance', 'student-result-management'); ?></h2>
                <div class="srm-chart-container">
                    <canvas id="classPerformanceChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Detailed Analytics -->
        <div class="srm-analytics-details">
            <div class="srm-analytics-section">
                <h2><?php _e('Recent Activity', 'student-result-management'); ?></h2>
                <div class="srm-recent-activity">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Student', 'student-result-management'); ?></th>
                                <th><?php _e('Exam', 'student-result-management'); ?></th>
                                <th><?php _e('Score', 'student-result-management'); ?></th>
                                <th><?php _e('Grade', 'student-result-management'); ?></th>
                                <th><?php _e('Date', 'student-result-management'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_results as $result): ?>
                                <tr>
                                    <td><?php echo esc_html($result->first_name . ' ' . $result->last_name . ' (' . $result->roll_number . ')'); ?></td>
                                    <td><?php echo esc_html($result->exam_name); ?></td>
                                    <td><?php echo esc_html($result->obtained_marks . '/' . $result->total_marks); ?></td>
                                    <td>
                                        <span class="srm-grade srm-grade-<?php echo strtolower($result->grade); ?>">
                                            <?php echo esc_html($result->grade); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html(date('M j, Y', strtotime($result->created_at))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="srm-analytics-section">
                <h2><?php _e('Monthly Trends', 'student-result-management'); ?></h2>
                <div class="srm-chart-container">
                    <canvas id="monthlyTrendsChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Export Analytics -->
        <div class="srm-analytics-export">
            <h2><?php _e('Export Analytics', 'student-result-management'); ?></h2>
            <div class="srm-export-options">
                <button class="button button-secondary" id="srm-export-performance">
                    <?php _e('Export Performance Report', 'student-result-management'); ?>
                </button>
                <button class="button button-secondary" id="srm-export-trends">
                    <?php _e('Export Trends Report', 'student-result-management'); ?>
                </button>
                <button class="button button-secondary" id="srm-export-summary">
                    <?php _e('Export Summary Report', 'student-result-management'); ?>
                </button>
            </div>
        </div>
        
        <style>
        .srm-analytics-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .srm-analytics-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .srm-card-icon {
            margin-right: 15px;
        }
        
        .srm-card-icon .dashicons {
            font-size: 2.5em;
            color: #0073aa;
        }
        
        .srm-card-content h3 {
            margin: 0 0 5px 0;
            font-size: 2em;
            color: #333;
        }
        
        .srm-card-content p {
            margin: 0;
            color: #666;
        }
        
        .srm-analytics-charts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .srm-chart-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        
        .srm-chart-container {
            height: 300px;
            position: relative;
        }
        
        .srm-analytics-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .srm-analytics-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        
        .srm-grade {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .srm-grade-a { background: #d4edda; color: #155724; }
        .srm-grade-b { background: #fff3cd; color: #856404; }
        .srm-grade-c { background: #f8d7da; color: #721c24; }
        .srm-grade-d { background: #f8d7da; color: #721c24; }
        .srm-grade-f { background: #f8d7da; color: #721c24; }
        
        .srm-analytics-export {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        
        .srm-export-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .srm-analytics-charts,
            .srm-analytics-details {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        jQuery(document).ready(function($) {
            // Performance Distribution Chart
            var performanceCtx = document.getElementById('performanceChart').getContext('2d');
            var performanceData = <?php echo json_encode($performance_data); ?>;
            
            var performanceChart = new Chart(performanceCtx, {
                type: 'doughnut',
                data: {
                    labels: performanceData.map(function(item) { return item.grade; }),
                    datasets: [{
                        data: performanceData.map(function(item) { return item.count; }),
                        backgroundColor: [
                            '#28a745', '#ffc107', '#dc3545', '#6c757d', '#17a2b8'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            
            // Class Performance Chart
            var classCtx = document.getElementById('classPerformanceChart').getContext('2d');
            var classData = <?php echo json_encode($class_performance); ?>;
            
            var classChart = new Chart(classCtx, {
                type: 'bar',
                data: {
                    labels: classData.map(function(item) { return 'Class ' + item.class; }),
                    datasets: [{
                        label: 'Average Percentage',
                        data: classData.map(function(item) { return parseFloat(item.avg_percentage || 0); }),
                        backgroundColor: '#0073aa'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
            
            // Monthly Trends Chart
            var trendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
            var trendsData = <?php echo json_encode($monthly_trends); ?>;
            
            var trendsChart = new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: trendsData.map(function(item) { 
                        var date = new Date(item.month + '-01');
                        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                    }).reverse(),
                    datasets: [{
                        label: 'Number of Results',
                        data: trendsData.map(function(item) { return item.count; }).reverse(),
                        borderColor: '#0073aa',
                        backgroundColor: 'rgba(0, 115, 170, 0.1)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Export Analytics
            $('#srm-export-performance').on('click', function() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'srm_export_analytics',
                        type: 'performance',
                        nonce: '<?php echo wp_create_nonce("srm_analytics_nonce"); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            window.open(response.data.download_url, '_blank');
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }
                });
            });
            
            $('#srm-export-trends').on('click', function() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'srm_export_analytics',
                        type: 'trends',
                        nonce: '<?php echo wp_create_nonce("srm_analytics_nonce"); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            window.open(response.data.download_url, '_blank');
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }
                });
            });
            
            $('#srm-export-summary').on('click', function() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'srm_export_analytics',
                        type: 'summary',
                        nonce: '<?php echo wp_create_nonce("srm_analytics_nonce"); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            window.open(response.data.download_url, '_blank');
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }
                });
            });
        });
        </script>
        
    <?php endif; ?>
</div>