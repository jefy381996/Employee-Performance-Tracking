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
        add_action('wp_ajax_srm_add_valid_key', array($this, 'ajax_add_valid_key'));
        add_action('wp_ajax_srm_remove_valid_key', array($this, 'ajax_remove_valid_key'));
        add_action('wp_ajax_srm_get_valid_keys', array($this, 'ajax_get_valid_keys'));
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
        
        // If no plugin owner is set, check if current user has owner key
        if (empty($plugin_owner)) {
            $license_key = $this->get_license_key();
            if ($license_key === $this->owner_key) {
                update_option('srm_plugin_owner', $current_user_id);
                $plugin_owner = $current_user_id;
            }
        }
        
        return ($current_user_id == $plugin_owner);
    }
    
    /**
     * Check if user has premium access
     */
    public function has_premium_access() {
        // Plugin owner always has premium access
        if ($this->is_plugin_owner()) {
            return true;
        }
        
        // Check if user has a valid license key
        $license_key = $this->get_license_key();
        return !empty($license_key) && $this->is_valid_license_key($license_key);
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
        if ($this->is_valid_license_key($license_key)) {
            update_option('srm_license_key', $license_key);
            update_option('srm_license_status', 'premium');
            return array('success' => true, 'message' => 'Premium license activated successfully!');
        }
        
        return array('success' => false, 'message' => 'Invalid license key. Please check and try again.');
    }
    
    /**
     * Deactivate license
     */
    public function deactivate_license() {
        delete_option('srm_license_key');
        delete_option('srm_license_status');
        
        // Don't remove plugin owner status
        return array('success' => true, 'message' => 'License deactivated successfully!');
    }
    
    /**
     * Check license status
     */
    public function check_license_status() {
        $status = $this->get_license_status();
        return array('success' => true, 'status' => $status);
    }
    
    /**
     * Validate license key format
     */
    private function is_valid_license_key($key) {
        // Basic validation - you can customize this
        // License keys should be alphanumeric with some special characters
        // Length between 8 and 32 characters
        if (strlen($key) < 8 || strlen($key) > 32) {
            return false;
        }
        
        // Check if it contains at least one letter and one number
        if (!preg_match('/[A-Za-z]/', $key) || !preg_match('/[0-9]/', $key)) {
            return false;
        }
        
        // Check if it's not the owner key (to prevent confusion)
        if ($key === $this->owner_key) {
            return false; // This should be handled separately
        }
        
        // Check if the key exists in our valid keys list
        return $this->is_key_in_valid_list($key);
    }
    
    /**
     * Check if a key exists in the valid keys list
     */
    private function is_key_in_valid_list($key) {
        $valid_keys = $this->get_valid_license_keys();
        return in_array($key, $valid_keys);
    }
    
    /**
     * Get list of valid license keys
     */
    public function get_valid_license_keys() {
        $keys = get_option('srm_valid_license_keys', array());
        return is_array($keys) ? $keys : array();
    }
    
    /**
     * Add a new valid license key
     */
    public function add_valid_license_key($key) {
        $keys = $this->get_valid_license_keys();
        if (!in_array($key, $keys)) {
            $keys[] = $key;
            update_option('srm_valid_license_keys', $keys);
            return true;
        }
        return false;
    }
    
    /**
     * Remove a license key from valid list
     */
    public function remove_valid_license_key($key) {
        $keys = $this->get_valid_license_keys();
        $keys = array_diff($keys, array($key));
        update_option('srm_valid_license_keys', $keys);
        return true;
    }
    
    /**
     * Check if a license key is currently in use
     */
    public function is_license_key_in_use($key) {
        global $wpdb;
        $sites = $wpdb->get_col($wpdb->prepare(
            "SELECT option_value FROM {$wpdb->options} WHERE option_name = 'srm_license_key' AND option_value = %s",
            $key
        ));
        return !empty($sites);
    }
    
    /**
     * Get license key usage information
     */
    public function get_license_key_usage($key) {
        global $wpdb;
        $sites = $wpdb->get_col($wpdb->prepare(
            "SELECT option_value FROM {$wpdb->options} WHERE option_name = 'srm_license_key' AND option_value = %s",
            $key
        ));
        return array(
            'key' => $key,
            'in_use' => !empty($sites),
            'usage_count' => count($sites),
            'sites' => $sites
        );
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
    
    /**
     * AJAX handler for adding valid license key (owner only)
     */
    public function ajax_add_valid_key() {
        check_ajax_referer('srm_license_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        if (!$this->is_plugin_owner()) {
            wp_send_json_error('Only plugin owner can manage license keys');
        }
        
        $key = sanitize_text_field($_POST['license_key']);
        
        if (empty($key)) {
            wp_send_json_error('License key is required');
        }
        
        if ($key === $this->owner_key) {
            wp_send_json_error('Cannot add owner key to valid keys list');
        }
        
        if ($this->add_valid_license_key($key)) {
            wp_send_json_success('License key added successfully!');
        } else {
            wp_send_json_error('License key already exists in valid list');
        }
    }
    
    /**
     * AJAX handler for removing valid license key (owner only)
     */
    public function ajax_remove_valid_key() {
        check_ajax_referer('srm_license_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        if (!$this->is_plugin_owner()) {
            wp_send_json_error('Only plugin owner can manage license keys');
        }
        
        $key = sanitize_text_field($_POST['license_key']);
        
        if (empty($key)) {
            wp_send_json_error('License key is required');
        }
        
        if ($this->remove_valid_license_key($key)) {
            wp_send_json_success('License key removed successfully!');
        } else {
            wp_send_json_error('Failed to remove license key');
        }
    }
    
    /**
     * AJAX handler for getting valid license keys (owner only)
     */
    public function ajax_get_valid_keys() {
        check_ajax_referer('srm_license_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        if (!$this->is_plugin_owner()) {
            wp_send_json_error('Only plugin owner can view license keys');
        }
        
        $valid_keys = $this->get_valid_license_keys();
        $keys_with_usage = array();
        
        foreach ($valid_keys as $key) {
            $usage = $this->get_license_key_usage($key);
            $keys_with_usage[] = $usage;
        }
        
        wp_send_json_success($keys_with_usage);
    }
}