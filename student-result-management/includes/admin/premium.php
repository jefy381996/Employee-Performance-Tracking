<?php
if (!defined('ABSPATH')) exit;

$current_user_id = get_current_user_id();
$plugin_owner = get_option('srm_plugin_owner');
$is_owner = ($current_user_id == $plugin_owner);
?>

<div class="wrap srm-premium">
    <h1><?php _e('Premium Features', 'student-result-management'); ?></h1>
    
    <?php if ($is_owner): ?>
        <div class="notice notice-success">
            <p><strong><?php _e('Congratulations!', 'student-result-management'); ?></strong> <?php _e('You have full access to all premium features as the plugin owner.', 'student-result-management'); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="srm-premium-container">
        <!-- Premium Features Overview -->
        <div class="srm-premium-hero">
            <div class="srm-premium-hero-content">
                <h2><?php _e('Unlock the Full Potential of Student Result Management', 'student-result-management'); ?></h2>
                <p><?php _e('Take your student result management to the next level with our premium features designed for educational institutions.', 'student-result-management'); ?></p>
                
                <?php if (!$is_owner): ?>
                    <div class="srm-pricing">
                        <div class="srm-price-tag">
                            <span class="srm-currency">$</span>
                            <span class="srm-amount">49</span>
                            <span class="srm-period">/year</span>
                        </div>
                        <p class="srm-price-description"><?php _e('One-time payment for lifetime access', 'student-result-management'); ?></p>
                        <a href="#" class="button button-primary button-hero srm-upgrade-btn">
                            <?php _e('Upgrade to Premium Now', 'student-result-management'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Feature Categories -->
        <div class="srm-feature-categories">
            <!-- PDF Generation -->
            <div class="srm-feature-category">
                <div class="srm-feature-icon">
                    <span class="dashicons dashicons-media-document"></span>
                </div>
                <h3><?php _e('PDF Result Cards', 'student-result-management'); ?></h3>
                <div class="srm-feature-list">
                    <ul>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Generate beautiful PDF result cards', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Customizable templates and designs', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('School logo and branding', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Bulk PDF generation for all students', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Watermarks and security features', 'student-result-management'); ?></li>
                    </ul>
                </div>
                <?php if ($is_owner): ?>
                    <div class="srm-feature-demo">
                        <button class="button button-secondary srm-demo-pdf">
                            <?php _e('Generate Sample PDF', 'student-result-management'); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Data Management -->
            <div class="srm-feature-category">
                <div class="srm-feature-icon">
                    <span class="dashicons dashicons-database-import"></span>
                </div>
                <h3><?php _e('Advanced Data Management', 'student-result-management'); ?></h3>
                <div class="srm-feature-list">
                    <ul>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('CSV import/export functionality', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Bulk student registration', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Data validation and cleanup tools', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Automated backup and restore', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Data migration assistance', 'student-result-management'); ?></li>
                    </ul>
                </div>
            </div>
            
            <!-- Student Profiles -->
            <div class="srm-feature-category">
                <div class="srm-feature-icon">
                    <span class="dashicons dashicons-id"></span>
                </div>
                <h3><?php _e('Enhanced Student Profiles', 'student-result-management'); ?></h3>
                <div class="srm-feature-list">
                    <ul>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Student profile photo uploads', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Additional student information fields', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Parent/guardian contact details', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Student history and notes', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Custom student categories', 'student-result-management'); ?></li>
                    </ul>
                </div>
            </div>
            
            <!-- Analytics & Reporting -->
            <div class="srm-feature-category">
                <div class="srm-feature-icon">
                    <span class="dashicons dashicons-chart-bar"></span>
                </div>
                <h3><?php _e('Analytics & Reporting', 'student-result-management'); ?></h3>
                <div class="srm-feature-list">
                    <ul>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Detailed performance analytics', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Class and subject-wise reports', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Trend analysis and insights', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Comparative performance charts', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Exportable reports in multiple formats', 'student-result-management'); ?></li>
                    </ul>
                </div>
            </div>
            
            <!-- Communication -->
            <div class="srm-feature-category">
                <div class="srm-feature-icon">
                    <span class="dashicons dashicons-email"></span>
                </div>
                <h3><?php _e('Communication Tools', 'student-result-management'); ?></h3>
                <div class="srm-feature-list">
                    <ul>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Automated email notifications', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('SMS integration for result alerts', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Customizable email templates', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Parent notification system', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Bulk communication tools', 'student-result-management'); ?></li>
                    </ul>
                </div>
            </div>
            
            <!-- Security & Access -->
            <div class="srm-feature-category">
                <div class="srm-feature-icon">
                    <span class="dashicons dashicons-shield-alt"></span>
                </div>
                <h3><?php _e('Security & Access Control', 'student-result-management'); ?></h3>
                <div class="srm-feature-list">
                    <ul>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Role-based access control', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Student login portal', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Data encryption and security', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('Audit logs and activity tracking', 'student-result-management'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e('GDPR compliance tools', 'student-result-management'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Premium vs Free Comparison -->
        <div class="srm-comparison-table">
            <h2><?php _e('Feature Comparison', 'student-result-management'); ?></h2>
            <table class="wp-list-table widefat">
                <thead>
                    <tr>
                        <th><?php _e('Feature', 'student-result-management'); ?></th>
                        <th><?php _e('Free Version', 'student-result-management'); ?></th>
                        <th><?php _e('Premium Version', 'student-result-management'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php _e('Add/Edit Students', 'student-result-management'); ?></td>
                        <td><span class="dashicons dashicons-yes srm-check-yes"></span></td>
                        <td><span class="dashicons dashicons-yes srm-check-yes"></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Add/Edit Results', 'student-result-management'); ?></td>
                        <td><span class="dashicons dashicons-yes srm-check-yes"></span></td>
                        <td><span class="dashicons dashicons-yes srm-check-yes"></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Result Lookup by Roll Number', 'student-result-management'); ?></td>
                        <td><span class="dashicons dashicons-yes srm-check-yes"></span></td>
                        <td><span class="dashicons dashicons-yes srm-check-yes"></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Basic Export (Students)', 'student-result-management'); ?></td>
                        <td><span class="dashicons dashicons-yes srm-check-yes"></span></td>
                        <td><span class="dashicons dashicons-yes srm-check-yes"></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Student Profile Images', 'student-result-management'); ?></td>
                        <td><span class="dashicons dashicons-no srm-check-no"></span></td>
                        <td><span class="dashicons dashicons-yes srm-check-yes"></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('PDF Result Cards', 'student-result-management'); ?></td>
                        <td><span class="dashicons dashicons-no srm-check-no"></span></td>
                        <td><span class="dashicons dashicons-yes srm-check-yes"></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('CSV Import/Export', 'student-result-management'); ?></td>
                        <td><span class="dashicons dashicons-no srm-check-no"></span></td>
                        <td><span class="dashicons dashicons-yes srm-check-yes"></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Email Notifications', 'student-result-management'); ?></td>
                        <td><span class="dashicons dashicons-no srm-check-no"></span></td>
                        <td><span class="dashicons dashicons-yes srm-check-yes"></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Advanced Analytics', 'student-result-management'); ?></td>
                        <td><span class="dashicons dashicons-no srm-check-no"></span></td>
                        <td><span class="dashicons dashicons-yes srm-check-yes"></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Priority Support', 'student-result-management'); ?></td>
                        <td><span class="dashicons dashicons-no srm-check-no"></span></td>
                        <td><span class="dashicons dashicons-yes srm-check-yes"></span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Testimonials -->
        <div class="srm-testimonials">
            <h2><?php _e('What Schools Are Saying', 'student-result-management'); ?></h2>
            <div class="srm-testimonial-grid">
                <div class="srm-testimonial">
                    <div class="srm-testimonial-content">
                        <p>"This plugin has transformed how we manage student results. The PDF generation feature saves us hours of work every semester."</p>
                    </div>
                    <div class="srm-testimonial-author">
                        <strong>Sarah Johnson</strong>
                        <span>Principal, Greenwood High School</span>
                    </div>
                </div>
                
                <div class="srm-testimonial">
                    <div class="srm-testimonial-content">
                        <p>"The CSV import feature made it easy to migrate our existing student data. The analytics help us track performance trends."</p>
                    </div>
                    <div class="srm-testimonial-author">
                        <strong>Michael Chen</strong>
                        <span>IT Administrator, Valley College</span>
                    </div>
                </div>
                
                <div class="srm-testimonial">
                    <div class="srm-testimonial-content">
                        <p>"Parents love the automated email notifications. It keeps them informed about their children's academic progress."</p>
                    </div>
                    <div class="srm-testimonial-author">
                        <strong>Dr. Priya Patel</strong>
                        <span>Academic Director, Elite Academy</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Support & Guarantee -->
        <div class="srm-support-guarantee">
            <div class="srm-support-item">
                <div class="srm-support-icon">
                    <span class="dashicons dashicons-sos"></span>
                </div>
                <h3><?php _e('Premium Support', 'student-result-management'); ?></h3>
                <p><?php _e('Get priority email support with response within 24 hours. We\'re here to help you succeed.', 'student-result-management'); ?></p>
            </div>
            
            <div class="srm-support-item">
                <div class="srm-support-icon">
                    <span class="dashicons dashicons-update"></span>
                </div>
                <h3><?php _e('Free Updates', 'student-result-management'); ?></h3>
                <p><?php _e('Receive all future updates and new features at no additional cost. Your investment is protected.', 'student-result-management'); ?></p>
            </div>
            
            <div class="srm-support-item">
                <div class="srm-support-icon">
                    <span class="dashicons dashicons-shield"></span>
                </div>
                <h3><?php _e('30-Day Money Back', 'student-result-management'); ?></h3>
                <p><?php _e('Not satisfied? Get a full refund within 30 days, no questions asked. Risk-free upgrade.', 'student-result-management'); ?></p>
            </div>
        </div>
        
        <!-- Final CTA -->
        <?php if (!$is_owner): ?>
            <div class="srm-final-cta">
                <h2><?php _e('Ready to Transform Your Student Management?', 'student-result-management'); ?></h2>
                <p><?php _e('Join hundreds of schools already using our premium features to streamline their result management process.', 'student-result-management'); ?></p>
                <a href="#" class="button button-primary button-hero srm-upgrade-btn">
                    <?php _e('Upgrade to Premium Today', 'student-result-management'); ?>
                </a>
                <p class="srm-money-back"><?php _e('30-day money-back guarantee • Instant access • No setup fees', 'student-result-management'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.srm-upgrade-btn').click(function(e) {
        e.preventDefault();
        alert('<?php _e('Thank you for your interest! This is a demo version. In a real implementation, this would redirect to a payment processor like Stripe or PayPal.', 'student-result-management'); ?>');
    });
    
    $('.srm-demo-pdf').click(function() {
        alert('<?php _e('PDF generation demo would be triggered here. This feature is fully functional in the premium version.', 'student-result-management'); ?>');
    });
});
</script>