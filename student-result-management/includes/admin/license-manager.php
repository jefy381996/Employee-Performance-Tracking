<?php
/**
 * Domain-Bound License Manager
 * Implements secure domain-bound licensing with license files
 */

class SRM_License_Manager {
    private $license_file_path;
    private $owner_key = 'Bismillah^512';
    
    public function __construct() {
        $this->license_file_path = SRM_PLUGIN_PATH . 'license.key';
    }
    
    /**
     * Get the current domain name
     */
    public function get_current_domain() {
        $domain = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        // Remove port number if present
        $domain = preg_replace('/:\d+$/', '', $domain);
        // Remove www. prefix for consistency
        $domain = preg_replace('/^www\./', '', $domain);
        
        // Handle localhost development
        if ($domain === 'localhost' || strpos($domain, '127.0.0.1') !== false) {
            // For localhost, use 'localhost.dev' as the domain
            $domain = 'localhost.dev';
        }
        
        return strtolower($domain);
    }
    
    /**
     * Get the license key from file
     */
    public function get_license_key() {
        if (!file_exists($this->license_file_path)) {
            return '';
        }
        
        $license_key = trim(file_get_contents($this->license_file_path));
        return $license_key;
    }
    
    /**
     * Get license status
     */
    public function get_license_status() {
        $license_key = $this->get_license_key();
        
        if (empty($license_key)) {
            return 'free';
        }
        
        if ($license_key === $this->owner_key) {
            return 'owner';
        }
        
        if ($this->is_valid_domain_license($license_key)) {
            return 'premium';
        }
        
        return 'invalid';
    }
    
    /**
     * Check if license key is valid for current domain
     */
    public function is_valid_domain_license($license_key) {
        if (empty($license_key)) {
            return false;
        }
        
        // Owner key is always valid
        if ($license_key === $this->owner_key) {
            return true;
        }
        
        // First validate the license key format
        if (!$this->validate_license_format($license_key)) {
            return false;
        }
        
        // Check if it's a domain-bound license
        if (strpos($license_key, '.') !== false) {
            $parts = explode('.', $license_key);
            if (count($parts) >= 2) {
                // Extract domain part (everything after the first dot)
                $domain_part = implode('.', array_slice($parts, 1));
                $current_domain = $this->get_current_domain();
                
                // Check if domain matches
                if ($domain_part === $current_domain) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if user is plugin owner
     */
    public function is_plugin_owner() {
        $license_key = $this->get_license_key();
        return ($license_key === $this->owner_key);
    }
    
    /**
     * Check if user has premium access
     */
    public function has_premium_access() {
        $license_status = $this->get_license_status();
        return in_array($license_status, ['owner', 'premium']);
    }
    
    /**
     * Activate license
     */
    public function activate_license($license_key) {
        $license_key = trim($license_key);
        
        if (empty($license_key)) {
            return array('success' => false, 'message' => 'License key cannot be empty.');
        }
        
        // Check if it's the owner key
        if ($license_key === $this->owner_key) {
            $this->save_license_key($license_key);
            // Record activation time
            update_option('srm_license_activated_at', current_time('mysql'));
            return array('success' => true, 'message' => 'Owner license activated successfully!');
        }
        
        // Check if it's a valid domain-bound license
        if ($this->is_valid_domain_license($license_key)) {
            $this->save_license_key($license_key);
            // Record activation time
            update_option('srm_license_activated_at', current_time('mysql'));
            return array('success' => true, 'message' => 'Premium license activated successfully for this domain!');
        }
        
        // Check if it's a domain-bound license for wrong domain
        if (strpos($license_key, '.') !== false) {
            $parts = explode('.', $license_key);
            if (count($parts) >= 2) {
                // Extract domain part (everything after the first dot)
                $domain_part = implode('.', array_slice($parts, 1));
                $current_domain = $this->get_current_domain();
                
                if ($domain_part !== $current_domain) {
                    return array(
                        'success' => false, 
                        'message' => "This license key is bound to domain '$domain_part' but you're trying to activate it on '$current_domain'. Each license key is domain-specific."
                    );
                }
                
                // If domain matches but we got here, it means format validation failed
                if (!$this->validate_license_format($license_key)) {
                    $key_part = $parts[0];
                    $error_details = array();
                    
                    if (strlen($key_part) !== 13) {
                        $error_details[] = "Key part must be exactly 13 characters (found " . strlen($key_part) . ")";
                    }
                    
                    if (strlen($key_part) >= 1) {
                        $first_letter = strtoupper($key_part[0]);
                        if (!in_array($first_letter, array('B', 'J', 'N', 'F', 'A', 'T'))) {
                            $error_details[] = "First letter must be B, J, N, F, A, or T (found '$first_letter')";
                        }
                    }
                    
                    if (strlen($key_part) >= 4) {
                        $fourth_letter = strtoupper($key_part[3]);
                        if (!in_array($fourth_letter, array('H', 'L', 'M', 'A', 'S'))) {
                            $error_details[] = "4th letter must be H, L, M, A, or S (found '$fourth_letter')";
                        }
                    }
                    
                    if (strlen($key_part) >= 13) {
                        $last_char = $key_part[12];
                        if (!ctype_digit($last_char)) {
                            $error_details[] = "13th character must be a number 0-9 (found '$last_char')";
                        }
                    }
                    
                    // Check special character
                    if (strlen($key_part) >= 10) {
                        $special_chars = array('!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '+', '=', '[', ']', '{', '}', '|', '\\', ':', ';', '"', '\'', '<', '>', ',', '.', '?', '/');
                        $has_special = false;
                        for ($i = 7; $i <= 9; $i++) {
                            if (isset($key_part[$i]) && in_array($key_part[$i], $special_chars)) {
                                $has_special = true;
                                break;
                            }
                        }
                        if (!$has_special) {
                            $error_details[] = "Positions 8, 9, or 10 must contain a special character (*, &, #, etc.)";
                        }
                    }
                    
                    $message = "License key format is invalid for domain '$domain_part':\n" . implode("\n", $error_details);
                    return array('success' => false, 'message' => $message);
                }
            }
        }
        
        return array('success' => false, 'message' => 'Invalid license key format. Please check and try again.');
    }
    
    /**
     * Deactivate license
     */
    public function deactivate_license() {
        if (file_exists($this->license_file_path)) {
            unlink($this->license_file_path);
        }
        // Clear activation time
        delete_option('srm_license_activated_at');
        return array('success' => true, 'message' => 'License deactivated successfully!');
    }
    
    /**
     * Save license key to file
     */
    private function save_license_key($license_key) {
        file_put_contents($this->license_file_path, $license_key);
    }
    
    /**
     * Get current domain info
     */
    public function get_domain_info() {
        return array(
            'domain' => $this->get_current_domain(),
            'full_url' => get_site_url(),
            'server_name' => $_SERVER['SERVER_NAME'] ?? '',
            'http_host' => $_SERVER['HTTP_HOST'] ?? ''
        );
    }
    
    /**
     * Check if license file exists
     */
    public function has_license_file() {
        return file_exists($this->license_file_path);
    }
    
    /**
     * Get license file path (for admin info)
     */
    public function get_license_file_path() {
        return $this->license_file_path;
    }
    
    /**
     * Validate license key format
     */
    public function validate_license_format($license_key) {
        if (empty($license_key)) {
            return false;
        }
        
        // Owner key is always valid
        if ($license_key === $this->owner_key) {
            return true;
        }
        
        // Check if it's a domain-bound license key
        if (strpos($license_key, '.') !== false) {
            $parts = explode('.', $license_key);
            if (count($parts) >= 2) {
                $key_part = $parts[0]; // The part before the domain
                $domain_part = implode('.', array_slice($parts, 1)); // The domain part
                
                // Validate the key part follows 13-digit format
                if (strlen($key_part) === 13) {
                    // Check first letter (B, J, N, F, A, or T)
                    $first_letter = strtoupper($key_part[0]);
                    $valid_first_letters = array('B', 'J', 'N', 'F', 'A', 'T');
                    
                    // Check 4th letter (H, L, M, A, or S)
                    $fourth_letter = strtoupper($key_part[3]);
                    $valid_fourth_letters = array('H', 'L', 'M', 'A', 'S');
                    
                    // Check 8th, 9th, or 10th letter (special character)
                    $special_chars = array('!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '+', '=', '[', ']', '{', '}', '|', '\\', ':', ';', '"', '\'', '<', '>', ',', '.', '?', '/');
                    
                    // Check 13th letter (must be a number 0-9)
                    $last_char = $key_part[12];
                    $is_last_number = ctype_digit($last_char);
                    
                    // Validate all conditions
                    if (in_array($first_letter, $valid_first_letters) &&
                        in_array($fourth_letter, $valid_fourth_letters) &&
                        $is_last_number) {
                        
                        // Check if any of the 8th, 9th, or 10th positions has a special character
                        $has_special_char = false;
                        for ($i = 7; $i <= 9; $i++) {
                            if (in_array($key_part[$i], $special_chars)) {
                                $has_special_char = true;
                                break;
                            }
                        }
                        
                        if ($has_special_char) {
                            return true;
                        }
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if license key is valid (for backward compatibility)
     */
    public function is_valid_license_key($license_key) {
        return $this->validate_license_format($license_key);
    }
    
    /**
     * Get license information for display
     */
    public function get_license_info() {
        $license_key = $this->get_license_key();
        $license_status = $this->get_license_status();
        $domain_info = $this->get_domain_info();
        
        return array(
            'key_present' => !empty($license_key),
            'status' => $license_status,
            'domain' => $domain_info['domain'],
            'has_file' => $this->has_license_file(),
            'is_owner' => $this->is_plugin_owner(),
            'has_premium' => $this->has_premium_access()
        );
    }
    
    /**
     * Check if license is bound to current domain
     */
    public function is_license_bound_to_current_domain() {
        $license_key = $this->get_license_key();
        
        if (empty($license_key) || $license_key === $this->owner_key) {
            return true; // Owner key is always valid
        }
        
        return $this->is_valid_domain_license($license_key);
    }
    
    /**
     * Get license key domain (for admin info)
     */
    public function get_license_domain() {
        $license_key = $this->get_license_key();
        
        if (empty($license_key) || $license_key === $this->owner_key) {
            return '';
        }
        
        if (strpos($license_key, '.') !== false) {
            $parts = explode('.', $license_key);
            if (count($parts) >= 2) {
                // Return the domain part (everything after the first dot)
                return implode('.', array_slice($parts, 1));
            }
        }
        
        return '';
    }
    
    /**
     * Check if license is valid for current installation
     */
    public function is_license_valid_for_current_installation() {
        $license_status = $this->get_license_status();
        return in_array($license_status, ['owner', 'premium']);
    }
    
    /**
     * Get student count (for free users limit)
     */
    public function get_student_count() {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}srm_students");
    }
    
    /**
     * Get remaining student slots for free users
     */
    public function get_remaining_student_slots() {
        if ($this->has_premium_access()) {
            return 'unlimited';
        }
        
        $current_count = $this->get_student_count();
        $limit = 20;
        return max(0, $limit - $current_count);
    }
    
    /**
     * Check if user has any license activated (for backward compatibility)
     */
    public function has_any_license() {
        $license_key = $this->get_license_key();
        return !empty($license_key);
    }
    
    /**
     * Check if user can add more students
     */
    public function can_add_student() {
        if ($this->has_premium_access()) {
            return true;
        }
        
        $current_count = $this->get_student_count();
        return $current_count < 20;
    }
    
    /**
     * Get current license usage information
     */
    public function get_current_license_usage() {
        $license_key = $this->get_license_key();
        
        if (empty($license_key) || $license_key === $this->owner_key) {
            return array();
        }
        
        // Get current user info
        $current_user = wp_get_current_user();
        
        // Get activation time (from option or estimate)
        $activated_at = get_option('srm_license_activated_at');
        if (empty($activated_at)) {
            // If not set, use a default or current time
            $activated_at = current_time('mysql');
            update_option('srm_license_activated_at', $activated_at);
        }
        
        return array(
            'site_url' => get_site_url(),
            'domain' => $this->get_current_domain(),
            'activated_at' => $activated_at,
            'user_email' => $current_user->user_email,
            'user_name' => $current_user->display_name,
            'license_domain' => $this->get_license_domain()
        );
    }
}