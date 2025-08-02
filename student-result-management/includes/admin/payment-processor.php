<?php
/**
 * Payment Processor for Student Result Management System
 * Handles payment processing for premium feature upgrades
 */

if (!defined('ABSPATH')) exit;

class SRM_Payment_Processor {
    
    private $stripe_secret_key;
    private $paypal_client_id;
    private $paypal_secret;
    
    public function __construct() {
        $this->stripe_secret_key = get_option('srm_stripe_secret_key', '');
        $this->paypal_client_id = get_option('srm_paypal_client_id', '');
        $this->paypal_secret = get_option('srm_paypal_secret', '');
    }
    
    /**
     * Process payment using the specified method
     */
    public function process_payment($payment_data) {
        $payment_method = sanitize_text_field($payment_data['payment_method']);
        
        switch ($payment_method) {
            case 'stripe':
                return $this->process_stripe_payment($payment_data);
            case 'paypal':
                return $this->process_paypal_payment($payment_data);
            case 'manual':
                return $this->process_manual_payment($payment_data);
            default:
                return array('success' => false, 'message' => 'Invalid payment method');
        }
    }
    
    /**
     * Process Stripe payment
     */
    private function process_stripe_payment($payment_data) {
        if (empty($this->stripe_secret_key)) {
            return array('success' => false, 'message' => 'Stripe is not configured');
        }
        
        // Include Stripe PHP library
        if (!class_exists('Stripe\Stripe')) {
            require_once SRM_PLUGIN_PATH . 'assets/lib/stripe-php/init.php';
        }
        
        try {
            \Stripe\Stripe::setApiKey($this->stripe_secret_key);
            
            $amount = floatval($payment_data['amount']) * 100; // Convert to cents
            $currency = sanitize_text_field($payment_data['currency']);
            $customer_email = sanitize_email($payment_data['customer_email']);
            $customer_name = sanitize_text_field($payment_data['customer_name']);
            $token = sanitize_text_field($payment_data['stripe_token']);
            
            // Create customer
            $customer = \Stripe\Customer::create(array(
                'email' => $customer_email,
                'name' => $customer_name,
                'source' => $token
            ));
            
            // Create charge
            $charge = \Stripe\Charge::create(array(
                'customer' => $customer->id,
                'amount' => $amount,
                'currency' => $currency,
                'description' => 'Student Result Management System - Premium Upgrade',
                'metadata' => array(
                    'site_url' => get_site_url(),
                    'plugin' => 'student-result-management'
                )
            ));
            
            if ($charge->status === 'succeeded') {
                $this->log_payment($payment_data, $charge->id, 'stripe');
                return array(
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'transaction_id' => $charge->id
                );
            } else {
                return array('success' => false, 'message' => 'Payment failed');
            }
            
        } catch (\Stripe\Exception\CardException $e) {
            return array('success' => false, 'message' => 'Card error: ' . $e->getMessage());
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return array('success' => false, 'message' => 'Invalid request: ' . $e->getMessage());
        } catch (\Stripe\Exception\AuthenticationException $e) {
            return array('success' => false, 'message' => 'Authentication failed: ' . $e->getMessage());
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            return array('success' => false, 'message' => 'Network error: ' . $e->getMessage());
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return array('success' => false, 'message' => 'API error: ' . $e->getMessage());
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Payment error: ' . $e->getMessage());
        }
    }
    
    /**
     * Process PayPal payment
     */
    private function process_paypal_payment($payment_data) {
        if (empty($this->paypal_client_id) || empty($this->paypal_secret)) {
            return array('success' => false, 'message' => 'PayPal is not configured');
        }
        
        // For demo purposes, we'll simulate a successful PayPal payment
        // In a real implementation, you would integrate with PayPal's API
        
        $amount = floatval($payment_data['amount']);
        $currency = sanitize_text_field($payment_data['currency']);
        $customer_email = sanitize_email($payment_data['customer_email']);
        
        // Simulate payment processing
        $transaction_id = 'PAYPAL-' . time() . '-' . wp_generate_password(8, false);
        
        $this->log_payment($payment_data, $transaction_id, 'paypal');
        
        return array(
            'success' => true,
            'message' => 'PayPal payment processed successfully',
            'transaction_id' => $transaction_id
        );
    }
    
    /**
     * Process manual payment (bank transfer, etc.)
     */
    private function process_manual_payment($payment_data) {
        $amount = floatval($payment_data['amount']);
        $currency = sanitize_text_field($payment_data['currency']);
        $customer_email = sanitize_email($payment_data['customer_email']);
        $customer_name = sanitize_text_field($payment_data['customer_name']);
        
        // Generate pending transaction ID
        $transaction_id = 'MANUAL-' . time() . '-' . wp_generate_password(8, false);
        
        // Log the payment as pending
        $this->log_payment($payment_data, $transaction_id, 'manual', 'pending');
        
        // Send email notification to admin
        $this->send_manual_payment_notification($payment_data, $transaction_id);
        
        return array(
            'success' => true,
            'message' => 'Manual payment request submitted. You will receive instructions via email.',
            'transaction_id' => $transaction_id,
            'status' => 'pending'
        );
    }
    
    /**
     * Log payment transaction
     */
    private function log_payment($payment_data, $transaction_id, $method, $status = 'completed') {
        global $wpdb;
        
        $payments_table = $wpdb->prefix . 'srm_payments';
        
        $wpdb->insert($payments_table, array(
            'transaction_id' => $transaction_id,
            'amount' => $payment_data['amount'],
            'currency' => $payment_data['currency'],
            'payment_method' => $method,
            'customer_email' => $payment_data['customer_email'],
            'customer_name' => $payment_data['customer_name'],
            'status' => $status,
            'created_at' => current_time('mysql')
        ));
    }
    
    /**
     * Send manual payment notification
     */
    private function send_manual_payment_notification($payment_data, $transaction_id) {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $subject = sprintf(__('Manual Payment Request - %s', 'student-result-management'), $site_name);
        
        $message = sprintf(
            __("A manual payment request has been submitted for the Student Result Management System.\n\n" .
               "Transaction ID: %s\n" .
               "Amount: %s %s\n" .
               "Customer: %s (%s)\n" .
               "Date: %s\n\n" .
               "Please process this payment and update the license status in the admin panel.",
               'student-result-management'),
            $transaction_id,
            $payment_data['amount'],
            $payment_data['currency'],
            $payment_data['customer_name'],
            $payment_data['customer_email'],
            current_time('mysql')
        );
        
        wp_mail($admin_email, $subject, $message);
    }
    
    /**
     * Get payment methods
     */
    public function get_available_payment_methods() {
        $methods = array();
        
        if (!empty($this->stripe_secret_key)) {
            $methods['stripe'] = array(
                'name' => 'Credit Card (Stripe)',
                'icon' => 'dashicons-credit-card',
                'description' => 'Pay securely with your credit card'
            );
        }
        
        if (!empty($this->paypal_client_id)) {
            $methods['paypal'] = array(
                'name' => 'PayPal',
                'icon' => 'dashicons-paypal',
                'description' => 'Pay with your PayPal account'
            );
        }
        
        $methods['manual'] = array(
            'name' => 'Bank Transfer',
            'icon' => 'dashicons-money-alt',
            'description' => 'Pay via bank transfer (manual processing)'
        );
        
        return $methods;
    }
    
    /**
     * Get payment history
     */
    public function get_payment_history($limit = 10) {
        global $wpdb;
        
        $payments_table = $wpdb->prefix . 'srm_payments';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $payments_table ORDER BY created_at DESC LIMIT %d",
            $limit
        ));
    }
    
    /**
     * Update payment status
     */
    public function update_payment_status($transaction_id, $status) {
        global $wpdb;
        
        $payments_table = $wpdb->prefix . 'srm_payments';
        
        $result = $wpdb->update(
            $payments_table,
            array('status' => $status),
            array('transaction_id' => $transaction_id),
            array('%s'),
            array('%s')
        );
        
        if ($result && $status === 'completed') {
            // Activate license for this payment
            $license_manager = new SRM_License_Manager();
            $license_key = $license_manager->generate_license_key();
            $license_manager->activate_license($license_key);
        }
        
        return $result;
    }
}