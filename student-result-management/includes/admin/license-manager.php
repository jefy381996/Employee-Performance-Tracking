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
        
        // Check if it's a domain-bound license
        if (strpos($license_key, '.') !== false) {
            $parts = explode('.', $license_key);
            if (count($parts) >= 2) {
                $domain_part = end($parts);
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
            return array('success' => true, 'message' => 'Owner license activated successfully!');
        }
        
        // Check if it's a valid domain-bound license
        if ($this->is_valid_domain_license($license_key)) {
            $this->save_license_key($license_key);
            return array('success' => true, 'message' => 'Premium license activated successfully for this domain!');
        }
        
        // Check if it's a domain-bound license for wrong domain
        if (strpos($license_key, '.') !== false) {
            $parts = explode('.', $license_key);
            if (count($parts) >= 2) {
                $domain_part = end($parts);
                $current_domain = $this->get_current_domain();
                
                if ($domain_part !== $current_domain) {
                    return array(
                        'success' => false, 
                        'message' => "This license key is bound to domain '$domain_part' but you're trying to activate it on '$current_domain'. Each license key is domain-specific."
                    );
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
        
        // Check domain-bound format: XYGh675*UGTFM.domainname.com
        if (preg_match('/^[A-Za-z0-9*]{10,}\.[a-z0-9.-]+\.[a-z]{2,}$/', $license_key)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get license information for display
     */
    public function get_license_info() {
        $license_key = $this->get_license_key();
        $license_status = $this->get_license_status();
        $domain_info = $this->get_domain_info();
        
        return array(
            'key' => $license_key,
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
                return end($parts);
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
     * Check if user can add more students
     */
    public function can_add_student() {
        if ($this->has_premium_access()) {
            return true;
        }
        
        $current_count = $this->get_student_count();
        return $current_count < 20;
    }
}