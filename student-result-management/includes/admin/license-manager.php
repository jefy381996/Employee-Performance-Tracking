<?php
/**
 * License Manager for Student Result Management System
 * Simple license key system for premium feature access
 */

if (!defined('ABSPATH')) exit;

class SRM_License_Manager {
    
    private $owner_key = 'Bismillah^512';
    
    public function __construct() {
        add_action('admin_init', array($this, 'init_license_manager'));
        add_action('wp_ajax_srm_activate_license', array($this, 'ajax_activate_license'));
        add_action('wp_ajax_srm_deactivate_license', array($this, 'ajax_deactivate_license'));
        add_action('wp_ajax_srm_check_license_status', array($this, 'ajax_check_license_status'));

    }
    
    /**
     * Initialize license manager
     */
    public function init_license_manager() {
        // Check license status periodically
        if (!get_transient('srm_license_check')) {
            $this->check_license_status();
            set_transient('srm_license_check', true, DAY_IN_SECONDS);
        }
    }
    
    /**
     * Check if current user is plugin owner
     */
    public function is_plugin_owner() {
        $current_user_id = get_current_user_id();
        $plugin_owner = get_option('srm_plugin_owner');
        
        // Check if current user has the owner key activated
        $license_key = $this->get_license_key();
        if ($license_key === $this->owner_key) {
            // Set this user as owner if not already set
            if (empty($plugin_owner)) {
                update_option('srm_plugin_owner', $current_user_id);
            }
            return ($current_user_id == $plugin_owner);
        }
        
        // Return true if user is set as owner (for backward compatibility)
        return ($current_user_id == $plugin_owner);
    }
    
    /**
     * Check if user has premium access
     */
    public function has_premium_access() {
        // Check if user has a valid license key (including owner key)
        $license_key = $this->get_license_key();
        return !empty($license_key) && $this->is_valid_license_key($license_key);
    }
    
    /**
     * Check if user has any license activated (for admin pages)
     */
    public function has_any_license() {
        $license_key = $this->get_license_key();
        return !empty($license_key);
    }
    
    /**
     * Force license check on admin pages
     */
    public function force_license_check() {
        // Only check on plugin admin pages
        if (!isset($_GET['page']) || strpos($_GET['page'], 'srm-') === false) {
            return;
        }
        
        // Skip license check on the premium features page itself
        if (isset($_GET['page']) && $_GET['page'] === 'srm-premium') {
            return;
        }
        
        // Skip license check for free features (dashboard, students, results)
        $free_pages = array('srm-dashboard', 'srm-students', 'srm-results');
        if (isset($_GET['page']) && in_array($_GET['page'], $free_pages)) {
            return;
        }
        
        // If no license is activated, redirect to premium features page
        if (!$this->has_any_license()) {
            wp_redirect(admin_url('admin.php?page=srm-premium&license_required=1'));
            exit;
        }
    }
    
    /**
     * Check if user can add more students (free users limited to 20)
     */
    public function can_add_student() {
        // Plugin owner can always add students
        if ($this->is_plugin_owner()) {
            return true;
        }
        
        // Premium users can always add students
        if ($this->has_premium_access()) {
            return true;
        }
        
        // Free users are limited to 20 students
        $student_count = $this->get_student_count();
        return $student_count < 20;
    }
    
    /**
     * Get current student count
     */
    public function get_student_count() {
        global $wpdb;
        $students_table = $wpdb->prefix . 'srm_students';
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $students_table");
    }
    
    /**
     * Get remaining student slots for free users
     */
    public function get_remaining_student_slots() {
        if ($this->has_premium_access()) {
            return 'unlimited';
        }
        
        $student_count = $this->get_student_count();
        $remaining = 20 - $student_count;
        return max(0, $remaining);
    }
    
    /**
     * Get current license status
     */
    public function get_license_status() {
        if ($this->is_plugin_owner()) {
            return 'owner';
        }
        
        $license_key = $this->get_license_key();
        if (!empty($license_key)) {
            if ($license_key === $this->owner_key) {
                return 'owner';
            } elseif ($this->is_valid_license_key($license_key)) {
                return 'premium';
            } else {
                return 'invalid';
            }
        }
        
        return 'free';
    }
    
    /**
     * Get license key
     */
    public function get_license_key() {
        return get_option('srm_license_key', '');
    }
    
    /**
     * Activate license
     */
    public function activate_license($license_key) {
        $license_key = sanitize_text_field($license_key);
        
        // Check if it's the owner key
        if ($license_key === $this->owner_key) {
            update_option('srm_plugin_owner', get_current_user_id());
            update_option('srm_license_key', $license_key);
            update_option('srm_license_status', 'owner');
            return array('success' => true, 'message' => 'Owner access activated successfully!');
        }
        
        // Check if it's a valid license key
        if (!$this->is_valid_license_key($license_key)) {
            return array('success' => false, 'message' => 'Invalid license key format. Please check and try again.');
        }
        
        // Check if this license key is already in use on another installation
        $current_site_id = $this->get_site_identifier();
        $license_usage = get_option('srm_license_usage_' . $license_key, array());
        
        if (!empty($license_usage) && $license_usage['site_id'] !== $current_site_id) {
            return array(
                'success' => false, 
                'message' => 'This license key is already activated on another website. Each license key can only be used on one installation. Please purchase a new license key.'
            );
        }
        
        // Check if this site already has a different license activated
        $current_license = get_option('srm_license_key');
        if (!empty($current_license) && $current_license !== $license_key) {
            // Remove the old license usage
            $old_license_usage = get_option('srm_license_usage_' . $current_license, array());
            if (!empty($old_license_usage) && $old_license_usage['site_id'] === $current_site_id) {
                delete_option('srm_license_usage_' . $current_license);
            }
        }
        
        // Activate the license
        update_option('srm_license_key', $license_key);
        update_option('srm_license_status', 'premium');
        
        // Record this license usage
        $usage_data = array(
            'site_id' => $current_site_id,
            'site_url' => get_site_url(),
            'activated_at' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'user_email' => wp_get_current_user()->user_email
        );
        update_option('srm_license_usage_' . $license_key, $usage_data);
        
        return array('success' => true, 'message' => 'Premium license activated successfully! This license is now bound to this installation.');
    }
    
    /**
     * Deactivate license
     */
    public function deactivate_license() {
        $current_license = get_option('srm_license_key');
        
        if (!empty($current_license)) {
            // Remove the license usage record
            delete_option('srm_license_usage_' . $current_license);
        }
        
        delete_option('srm_license_key');
        delete_option('srm_license_status');
        
        // Don't remove plugin owner status
        return array('success' => true, 'message' => 'License deactivated successfully! This license key is now available for use on another installation.');
    }
    
    /**
     * Get unique site identifier
     */
    private function get_site_identifier() {
        $site_url = get_site_url();
        $site_name = get_bloginfo('name');
        $site_description = get_bloginfo('description');
        
        // Create a unique identifier based on site characteristics
        $identifier_data = $site_url . '|' . $site_name . '|' . $site_description;
        return md5($identifier_data);
    }
    
    /**
     * Check license status
     */
    public function check_license_status() {
        $status = $this->get_license_status();
        return array('success' => true, 'status' => $status);
    }
    
    /**
     * Check if a license key is already in use
     */
    public function is_license_in_use($license_key) {
        $usage_data = get_option('srm_license_usage_' . $license_key, array());
        return !empty($usage_data);
    }
    
    /**
     * Get license usage information
     */
    public function get_license_usage_info($license_key) {
        return get_option('srm_license_usage_' . $license_key, array());
    }
    
    /**
     * Get current license usage information
     */
    public function get_current_license_usage() {
        $current_license = get_option('srm_license_key');
        if (empty($current_license)) {
            return array();
        }
        return $this->get_license_usage_info($current_license);
    }
    
    /**
     * Validate license key format
     */
    private function is_valid_license_key($key) {
        // Check if it's the owner key
        if ($key === $this->owner_key) {
            return true; // Owner key is valid
        }
        
        // Check if it's exactly 13 characters
        if (strlen($key) !== 13) {
            return false;
        }
        
        // Check first letter (must be B, J, N, A, F, or T)
        $first_letter = strtoupper($key[0]);
        $valid_first_letters = array('B', 'J', 'N', 'A', 'F', 'T');
        if (!in_array($first_letter, $valid_first_letters)) {
            return false;
        }
        
        // Check 4th letter (must be X, G, K, D, E, or P)
        $fourth_letter = strtoupper($key[3]);
        $valid_fourth_letters = array('X', 'G', 'K', 'D', 'E', 'P');
        if (!in_array($fourth_letter, $valid_fourth_letters)) {
            return false;
        }
        
        // Check 8th, 9th, or 10th letter (must be special character)
        $special_chars = array('!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '+', '=', '[', ']', '{', '}', '|', '\\', ':', ';', '"', '\'', '<', '>', ',', '.', '?', '/');
        $has_special_char = false;
        for ($i = 7; $i <= 9; $i++) {
            if (in_array($key[$i], $special_chars)) {
                $has_special_char = true;
                break;
            }
        }
        if (!$has_special_char) {
            return false;
        }
        
        // Check 13th letter (must be B, G, N, K, F, or P)
        $last_letter = strtoupper($key[12]);
        $valid_last_letters = array('B', 'G', 'N', 'K', 'F', 'P');
        if (!in_array($last_letter, $valid_last_letters)) {
            return false;
        }
        
        return true;
    }
    

    
    /**
     * AJAX handler for license activation
     */
    public function ajax_activate_license() {
        check_ajax_referer('srm_license_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $license_key = sanitize_text_field($_POST['license_key']);
        
        if (empty($license_key)) {
            wp_send_json_error('Please enter a license key');
        }
        
        $result = $this->activate_license($license_key);
        
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * AJAX handler for license deactivation
     */
    public function ajax_deactivate_license() {
        check_ajax_referer('srm_license_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $result = $this->deactivate_license();
        
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * AJAX handler for license status check
     */
    public function ajax_check_license_status() {
        check_ajax_referer('srm_license_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $result = $this->check_license_status();
        
        if ($result['success']) {
            wp_send_json_success(array('status' => $result['status']));
        } else {
            wp_send_json_error('Failed to check license status');
        }
    }
    

}