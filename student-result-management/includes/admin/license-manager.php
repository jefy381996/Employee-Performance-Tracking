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
        
        // You can add more validation rules here
        // For example, check against a database of valid keys
        
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