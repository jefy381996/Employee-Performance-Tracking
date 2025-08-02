<?php
/**
 * License Manager for Student Result Management System
 * Handles premium feature access, license validation, and payment processing
 */

if (!defined('ABSPATH')) exit;

class SRM_License_Manager {
    
    private $license_api_url = 'https://your-license-server.com/api/';
    private $plugin_slug = 'student-result-management';
    private $plugin_version = '1.0.0';
    
    public function __construct() {
        add_action('admin_init', array($this, 'init_license_manager'));
        add_action('wp_ajax_srm_activate_license', array($this, 'ajax_activate_license'));
        add_action('wp_ajax_srm_deactivate_license', array($this, 'ajax_deactivate_license'));
        add_action('wp_ajax_srm_check_license_status', array($this, 'ajax_check_license_status'));
        add_action('wp_ajax_srm_process_payment', array($this, 'ajax_process_payment'));
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
        
        // Check license status
        $license_status = $this->get_license_status();
        return ($license_status === 'premium' || $license_status === 'active');
    }
    
    /**
     * Get current license status
     */
    public function get_license_status() {
        global $wpdb;
        $settings_table = $wpdb->prefix . 'srm_settings';
        $status = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM $settings_table WHERE setting_name = %s",
            'license_status'
        ));
        
        return $status ?: 'free';
    }
    
    /**
     * Get license key
     */
    public function get_license_key() {
        global $wpdb;
        $settings_table = $wpdb->prefix . 'srm_settings';
        $key = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM $settings_table WHERE setting_name = %s",
            'license_key'
        ));
        
        return $key ?: '';
    }
    
    /**
     * Activate license
     */
    public function activate_license($license_key) {
        $response = wp_remote_post($this->license_api_url . 'activate', array(
            'body' => array(
                'license_key' => $license_key,
                'site_url' => get_site_url(),
                'plugin_slug' => $this->plugin_slug,
                'plugin_version' => $this->plugin_version
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'message' => 'Connection error: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($data && isset($data['success'])) {
            if ($data['success']) {
                $this->update_license_status('premium');
                $this->update_license_key($license_key);
                return array('success' => true, 'message' => 'License activated successfully!');
            } else {
                return array('success' => false, 'message' => $data['message'] ?? 'Invalid license key');
            }
        }
        
        return array('success' => false, 'message' => 'Invalid response from license server');
    }
    
    /**
     * Deactivate license
     */
    public function deactivate_license() {
        $license_key = $this->get_license_key();
        
        if (empty($license_key)) {
            return array('success' => false, 'message' => 'No license key to deactivate');
        }
        
        $response = wp_remote_post($this->license_api_url . 'deactivate', array(
            'body' => array(
                'license_key' => $license_key,
                'site_url' => get_site_url()
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'message' => 'Connection error: ' . $response->get_error_message());
        }
        
        $this->update_license_status('free');
        $this->update_license_key('');
        
        return array('success' => true, 'message' => 'License deactivated successfully!');
    }
    
    /**
     * Check license status with server
     */
    public function check_license_status() {
        $license_key = $this->get_license_key();
        
        if (empty($license_key)) {
            return array('success' => false, 'message' => 'No license key found');
        }
        
        $response = wp_remote_post($this->license_api_url . 'check', array(
            'body' => array(
                'license_key' => $license_key,
                'site_url' => get_site_url()
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'message' => 'Connection error: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($data && isset($data['success'])) {
            if ($data['success']) {
                $status = $data['status'] ?? 'free';
                $this->update_license_status($status);
                return array('success' => true, 'status' => $status);
            } else {
                $this->update_license_status('free');
                return array('success' => false, 'message' => $data['message'] ?? 'License check failed');
            }
        }
        
        return array('success' => false, 'message' => 'Invalid response from license server');
    }
    
    /**
     * Process payment for premium upgrade
     */
    public function process_payment($payment_data) {
        // This would integrate with your payment processor (Stripe, PayPal, etc.)
        // For demo purposes, we'll simulate a successful payment
        
        $payment_processor = new SRM_Payment_Processor();
        $result = $payment_processor->process_payment($payment_data);
        
        if ($result['success']) {
            // Generate license key
            $license_key = $this->generate_license_key();
            
            // Activate license
            $activation_result = $this->activate_license($license_key);
            
            if ($activation_result['success']) {
                return array(
                    'success' => true,
                    'message' => 'Payment processed and license activated successfully!',
                    'license_key' => $license_key
                );
            } else {
                return array(
                    'success' => false,
                    'message' => 'Payment successful but license activation failed: ' . $activation_result['message']
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Generate a license key
     */
    public function generate_license_key() {
        $prefix = 'SRM';
        $timestamp = time();
        $random = wp_generate_password(16, false);
        return $prefix . '-' . $timestamp . '-' . $random;
    }
    
    /**
     * Update license status
     */
    private function update_license_status($status) {
        global $wpdb;
        $settings_table = $wpdb->prefix . 'srm_settings';
        
        $wpdb->replace($settings_table, array(
            'setting_name' => 'license_status',
            'setting_value' => $status
        ));
    }
    
    /**
     * Update license key
     */
    private function update_license_key($key) {
        global $wpdb;
        $settings_table = $wpdb->prefix . 'srm_settings';
        
        $wpdb->replace($settings_table, array(
            'setting_name' => 'license_key',
            'setting_value' => $key
        ));
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
            wp_send_json_error('License key is required');
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
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * AJAX handler for payment processing
     */
    public function ajax_process_payment() {
        check_ajax_referer('srm_payment_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $payment_data = array(
            'amount' => sanitize_text_field($_POST['amount']),
            'currency' => sanitize_text_field($_POST['currency']),
            'payment_method' => sanitize_text_field($_POST['payment_method']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'customer_name' => sanitize_text_field($_POST['customer_name'])
        );
        
        $result = $this->process_payment($payment_data);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }
}

// Initialize license manager
$srm_license_manager = new SRM_License_Manager();