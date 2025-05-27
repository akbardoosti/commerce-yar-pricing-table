<?php
if (!defined('ABSPATH')) {
    exit;
}

class Commerce_Yar_Payment_Handler {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'commerce_yar_subscriptions';
        
        add_action('wp_ajax_commerce_yar_initiate_payment', array($this, 'initiate_payment'));
        add_action('wp_ajax_nopriv_commerce_yar_initiate_payment', array($this, 'initiate_payment'));
        add_action('init', array($this, 'handle_payment_callback'));
    }

    public function initiate_payment() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'commerce_yar_pricing_nonce')) {
            wp_send_json_error(array('message' => 'Invalid security token'));
        }

        // Get plan data
        $plan_data = $_POST['plan_data'];
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => 'لطفا ابتدا وارد سایت شوید.',
                'redirect_to' => wp_login_url(wp_get_referer())
            ));
        }

        // Generate unique order ID
        $order_id = uniqid('CYP_');

        // Store temporary order data in transient
        set_transient('cyp_order_' . $order_id, array(
            'user_id' => get_current_user_id(),
            'plan_data' => $plan_data,
            'created_at' => current_time('mysql')
        ), HOUR_IN_SECONDS);

        // Initialize payment gateway (example with Zarinpal)
        try {
            $payment_url = $this->initialize_zarinpal_payment($order_id, $plan_data);
            wp_send_json_success(array('payment_url' => $payment_url));
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }

    private function initialize_zarinpal_payment($order_id, $plan_data) {
        // Replace with your Zarinpal merchant ID
        $merchant_id = 'YOUR_MERCHANT_ID';
        
        // Extract price from plan data (assuming it's in Tomans)
        $amount = intval(str_replace(['تومان', ',', ' '], '', $plan_data['price']));
        
        // Callback URL
        $callback_url = add_query_arg(array(
            'action' => 'cyp_payment_callback',
            'order_id' => $order_id
        ), home_url('/'));

        // Initialize Zarinpal payment
        $data = array(
            'merchant_id' => $merchant_id,
            'amount' => $amount,
            'callback_url' => $callback_url,
            'description' => sprintf('خرید اشتراک %s - %s', $plan_data['title'], $plan_data['type']),
        );

        $jsonData = json_encode($data);
        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);

        if ($result['data']['code'] == 100) {
            return 'https://www.zarinpal.com/pg/StartPay/' . $result['data']["authority"];
        }

        throw new Exception('خطا در اتصال به درگاه پرداخت: ' . $result['errors']['message']);
    }

    public function handle_payment_callback() {
        if (!isset($_GET['action']) || $_GET['action'] !== 'cyp_payment_callback') {
            return;
        }

        $order_id = $_GET['order_id'];
        $order_data = get_transient('cyp_order_' . $order_id);

        if (!$order_data) {
            wp_die('سفارش نامعتبر است.');
        }

        // Verify payment with Zarinpal
        if (isset($_GET['Authority'])) {
            $authority = $_GET['Authority'];
            $status = $_GET['Status'];

            if ($status === 'OK') {
                // Verify payment with Zarinpal
                $verified = $this->verify_zarinpal_payment($authority, $order_data);
                
                if ($verified) {
                    // Generate subscription token
                    $token = $this->generate_subscription_token($order_data);
                    
                    // Store subscription data
                    $this->store_subscription($order_data, $token, $authority);

                    // Redirect to success page
                    wp_redirect(add_query_arg('payment_status', 'success', home_url('/subscription-status/')));
                    exit;
                }
            }
        }

        // If we get here, payment failed
        wp_redirect(add_query_arg('payment_status', 'failed', home_url('/subscription-status/')));
        exit;
    }

    private function verify_zarinpal_payment($authority, $order_data) {
        $merchant_id = 'YOUR_MERCHANT_ID';
        $amount = intval(str_replace(['تومان', ',', ' '], '', $order_data['plan_data']['price']));

        $data = array(
            'merchant_id' => $merchant_id,
            'authority' => $authority,
            'amount' => $amount,
        );

        $jsonData = json_encode($data);
        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/verify.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);

        return isset($result['data']) && $result['data']['code'] === 100;
    }

    private function generate_subscription_token($order_data) {
        $token_base = implode('|', array(
            $order_data['user_id'],
            $order_data['plan_data']['type'],
            time()
        ));

        return wp_hash($token_base);
    }

    private function store_subscription($order_data, $token, $transaction_id) {
        global $wpdb;

        // Calculate expiration date based on plan type
        $expires_at = new DateTime();
        switch ($order_data['plan_data']['type']) {
            case 'monthly':
                $expires_at->modify('+1 month');
                break;
            case 'quarterly':
                $expires_at->modify('+3 months');
                break;
            case 'biannual':
                $expires_at->modify('+6 months');
                break;
            case 'yearly':
                $expires_at->modify('+1 year');
                break;
        }

        $wpdb->insert(
            $this->table_name,
            array(
                'user_id' => $order_data['user_id'],
                'plan_title' => $order_data['plan_data']['title'],
                'plan_type' => $order_data['plan_data']['type'],
                'price' => str_replace(['تومان', ',', ' '], '', $order_data['plan_data']['price']),
                'token' => $token,
                'payment_status' => 'completed',
                'transaction_id' => $transaction_id,
                'created_at' => current_time('mysql'),
                'expires_at' => $expires_at->format('Y-m-d H:i:s')
            ),
            array('%d', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s')
        );

        // Delete the temporary order data
        delete_transient('cyp_order_' . $_GET['order_id']);
    }

    public static function verify_token($token) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'commerce_yar_subscriptions';

        $subscription = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE token = %s AND payment_status = 'completed' AND expires_at > NOW()",
            $token
        ));

        return !empty($subscription);
    }
}

new Commerce_Yar_Payment_Handler(); 