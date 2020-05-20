<?php

class POC_Foundation {
    private static $instance;

    /**
     * API Endpoint
     *
     * @var string
     */
    private static $api_endpoint = "http://51.158.174.189:3002";

    /**
     * API Key
     *
     * @var string
     */
    private static $api_key = "co15rysYZGGQEPk6qbSOywzgZJbeuXzX";

    /**
     * UID Prefix
     *
     * @var string
     */
    private static $uid_prefix = "loc.com.vn";

    /**
     * Refund term
     *
     * @var int
     */
    private static $refund_term = 1;

    /**
     * Default revenue share
     *
     * @var int
     */
    private static $revenue_share = 60;

    /**
     * Default currency exchange
     *
     * @var float
     */
    private static $currency_exchange = 0.00004;

    /**
     * Default discount
     *
     * @var int
     */
    private static $default_discount = 10;

    /**
     * Create new instance of class
     *
     * @return POC_Foundation
     */
    public static function instance()
    {
        if( is_null( self::$instance) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * POC_Foundation constructor.
     */
    protected function __construct()
    {
        $this->add_hooks();
    }

    /**
     * Add hooks
     */
    protected function add_hooks()
    {
        add_action( 'wp_login', array( $this, 'add_ref_to_user' ) , 10, 2);

        add_action( 'woocommerce_after_order_object_save', array( $this, 'add_ref_to_order' ), 10, 2 );

        add_action( 'woocommerce_order_status_completed', array( $this, 'after_order_completed' ) );

        add_action( 'woocommerce_order_status_refunded', array( $this, 'after_order_refunded' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );

        add_filter( 'do_shortcode_tag', array( $this, 'add_purchase_conversion_setup' ), 999, 2 );

        add_action( 'init', array( $this, 'handle_ajax_request' ) );

        add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_custom_product_data_field' ) );

        add_action( 'woocommerce_process_product_meta', array( $this, 'save_custom_product_data_field' ) );

        add_filter( 'woocommerce_get_shop_coupon_data', array( $this, 'create_virtual_coupon' ), 10, 2  );

        add_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'get_discount_amount' ), 10, 6 );

        add_action( 'woocommerce_before_cart', array( $this, 'apply_coupon_by_ref_by' ) );
    }

    /**
     * Add ref to user after logged in
     *
     * @param $user_login
     * @param $user
     */
    public function add_ref_to_user( $user_login, $user ) {
        if ( ! empty( $_COOKIE['ref_by'] ) ) {
            add_user_meta( $user->ID, 'ref_by', $_COOKIE['ref_by'], true );
        }

        if ( ! empty( $_COOKIE['ref_by_subid'] ) ) {
            add_user_meta( $user->ID, 'ref_by_subid', $_COOKIE['ref_by_subid'], true );
        }
    }

    /**
     * Add ref to user after order created and meta data saved
     *
     * @param $object
     * @param $data_store
     */
    public function add_ref_to_order( $object, $data_store ) {
        $order_id = $object->get_id();

        if( ! empty( get_post_meta( $order_id, 'ref_by' ) ) ) {
            return;
        }

        $ref_by = ! empty( $_COOKIE['ref_by'] ) ? $_COOKIE['ref_by'] : get_user_meta( get_current_user_id(), 'ref_by', true );

        if( empty( $ref_by ) ) {
            $order = wc_get_order( $order_id );

            $coupon_codes = $order->get_coupon_codes();

            if( empty( $coupon_codes ) ) {
                return;
            }

            $coupon_code = $coupon_codes[0];

            $this->write_log( "Coupon code: $coupon_code" );

            if( ! $this->is_coupon_valid( $coupon_code ) ) {
                return;
            }

            $ref_by = $coupon_code;
        }

        add_post_meta( $order_id, 'ref_by', $ref_by );

        if( ! empty( get_post_meta( $order_id, 'ref_by_subid' ) ) ) {
            return;
        }

        $ref_by_subid = ! empty( $_COOKIE['ref_by_subid'] ) ? $_COOKIE['ref_by_subid'] : get_user_meta( get_current_user_id(), 'ref_by_subid', true );

        if( empty( $ref_by_subid ) ) {
            return;
        }

        add_post_meta( $order_id, 'ref_by_subid', $ref_by_subid );
    }

    /**
     * Handle completed order
     *
     * @param $order_id
     */
    public function after_order_completed( $order_id ) {
        $poc_price = $this->get_poc_price();

        $ref_by = get_post_meta( $order_id, 'ref_by', true );

        $ref_by_subid = get_post_meta( $order_id, 'ref_by_subid', true );

        if ( ! $ref_by ) $ref_by = 'null';

        if ( $ref_by_subid ) $ref_by = $ref_by . '::' . urlencode( $ref_by_subid );

        $order = wc_get_order( $order_id );

        $username = self::$uid_prefix;

        $amount = round( $this->get_revenue_share_total( $order ) * self::$currency_exchange / $poc_price, 6 );
        $release = time() + self::$refund_term * 60;

        $this->write_log("Added an affiliate TX:: username: ".$username." / ref_by: ".$ref_by." / uid: ".self::$uid_prefix."-".$order_id." / amount: ".$amount." / release: ".$release);

        $result = $this->send_request( self::$api_endpoint."/addtransaction/username/".$username."/ref_by/".$ref_by."/uid/".self::$uid_prefix."-".$order_id."/amount/".$amount."/merchant/".self::$uid_prefix."/release/".$release."/" );
        if ($result != "Done")  $this->write_log("Error while adding an affiliate TX:: username: ".$username." / uid: ".self::$uid_prefix.$order_id." / amount: ".$amount." / release: ".$release);
    }

    /**
     * Handle refunded order
     *
     * @param $order_id
     */
    public function after_order_refunded( $order_id )
    {
        $this->write_log("Revoked an affiliate TX:: ".self::$uid_prefix.$order_id);

        $result = $this->send_request(self::$api_endpoint."/revoketransaction/uid/".self::$uid_prefix.$order_id."/");

        if ($result != "Done") {
            $this->write_log("Error while revoke a Tx:: uid: ".self::$uid_prefix.$order_id);
        }
    }

    /**
     * Add needed scripts
     */
    public function add_scripts()
    {
        wp_enqueue_script( 'poc-foundation-script', plugin_dir_url( __FILE__ ) . 'assets/c.js', array( 'jquery' ) );
    }

    /**
     * Purchase conversion tracking setup
     *
     * @param $output
     * @param $tag
     *
     * @return string
     */
    public function add_purchase_conversion_setup( $output, $tag )
    {
        global $wp;

        if ( $tag != 'woocommerce_checkout' ) {
            return $output;
        }

        if( empty( $wp->query_vars['order-received'] ) ) {
            return $output;
        }

        $order_id = $wp->query_vars['order-received'];

        if( ! $order_id ) {
            return $output;
        }

        $order = wc_get_order( $order_id );

        if( ! $order ) {
            return $output;
        }

        if( ! $order->has_status( 'completed' ) ) {
            return $output;
        }

        $html = '
        <script data-cfasync="false" data-pagespeed-no-defer type="text/javascript">//<![CDATA[
          dataLayer.push({
            "event": "paymentCompleted",
            "ConversionID": "1022110835",
            "ConversionLabel": "VcZ0CPGB2M0BEPPYsOcD",
            "ConversionValue": "'.$order->get_total() * self::$currency_exchange.'",
            "ConversionOrderID": "'.self::$uid_prefix."-".$order_id.'",
            "ConversionCurrency": "USD",
            "eventCallback": function() {
              // alert("tessssss")
              window.location = "https://loc.com.vn/thanh-toan-thanh-cong/"
            }
          })
        //]]></script>';

        return $output . $html;
    }

    /**
     * Handle ajax request
     */
    public function handle_ajax_request()
    {
        if ( ! empty( $_GET["poc_action"] ) && $_GET["poc_action"] == "getuid" ) {
            echo md5(rand(0,999).microtime());
            die();
        }

        if ( ! empty( $_GET["poc_crmuid_notify_url"] ) ) {
            echo $this->send_get(
                "https://crmuid.xyz/site/".self::$uid_prefix."/url/".urlencode(base64_encode($_GET["poc_crmuid_notify_url"])),
                ["crmuid: $_GET[crmuid]"]
            );
            die();
        }
    }

    /**
     * Add custom product data field
     */
    public function add_custom_product_data_field()
    {
        $args = array(
            'id' => 'poc_foundation_discount',
            'label' => __( 'POC Discount' ),
        );

        woocommerce_wp_text_input( $args );
    }

    /**
     * Save custom product data field
     *
     * @param $post_id
     */
    public function save_custom_product_data_field( $post_id )
    {
        if( ! isset( $_POST['poc_foundation_discount'] ) ) {
            return;
        }

        $product = wc_get_product( $post_id );

        if( ! $product ) {
            return;
        }

        $product->update_meta_data( 'poc_foundation_discount', sanitize_text_field( $_POST['poc_foundation_discount'] ) );

        $product->save();
    }

    /**
     * Create virtual coupon
     *
     * @param $false
     * @param $data
     *
     * @return array|null
     */
    public function create_virtual_coupon( $false, $data )
    {
        if ( is_admin() ) {
            return $false;
        }

        $coupon_settings = null;

        if ( ! $this->is_coupon_valid( $data ) ) {
            return $false;
        }

        $coupon_settings = array(
            'discount_type' => 'percent',
            'amount' => self::$default_discount,
            'individual_use' => true,
        );

        return $coupon_settings;
    }

    /**
     * Custom discount amount base on product
     *
     * @param $round
     * @param $discounting_amount
     * @param $cart_item
     * @param $single
     * @param $coupon
     *
     * @return false|float
     */
    public function get_discount_amount( $round, $discounting_amount, $cart_item, $single, $coupon )
    {
        $product = wc_get_product( $cart_item['product_id'] );

        if( ! $product ) {
            return $round;
        }

        $custom_discount = $product->get_meta( 'poc_foundation_discount' );

        if( empty( $custom_discount ) ) {
            return $round;
        }

        $discount = (float) $coupon->get_amount() * ( $custom_discount / 100 );

        $round = round( min( $discount, $discounting_amount ), wc_get_rounding_precision() );

        return $round;
    }

    /**
     * Auto apply coupon if COOKIE has ref_by value
     */
    public function apply_coupon_by_ref_by()
    {
        $ref_by = ! empty( $_COOKIE['ref_by'] ) ? $_COOKIE['ref_by'] : get_user_meta( get_current_user_id(), 'ref_by', true );

        if( empty( $ref_by ) ) {
            return;
        }

        $cart = WC()->cart;

        $applied_coupons = $cart->get_applied_coupons();

        // Check if cart has coupon or not
        if( in_array( $ref_by, $applied_coupons ) ) {
            return;
        }

        // Check if coupon is valid or not
        if( ! $this->is_coupon_valid( $ref_by ) ) {
            return;
        }

        // If valid, apply it
        $cart->add_discount( $ref_by );

        wc_print_notices();
    }

    /**
     * Get POC Price
     *
     * @return bool|string
     */
    protected function get_poc_price()
    {
        $price = $this->send_request( self::$api_endpoint . '/getprice/poc' );
        if (is_numeric($price) && $price > 0) {
            return $price;
        } else {
            // Try again after 1s
            sleep(1);
            $price = $this->send_request( self::$api_endpoint . '/getprice/poc' );
            if (is_numeric($price) && $price > 0) {
                return $price;
            } else {
                // Try again after 1s
                sleep(1);
                $price = $this->send_request( self::$api_endpoint . '/getprice/poc' );
                if (is_numeric($price) && $price > 0) {
                    return $price;
                } else {
                    // Try again after 1s
                    sleep(1);
                    return false;
                }
            }
        }
    }

    /**
     * Send API Request
     *
     * @param $url
     *
     * @return bool|string
     */
    protected function send_request( $url ) {
        $headers = [
            'api-key: ' . self::$api_key,
        ];

        return $this->send_get( $url, $headers );
    }

    /**
     * Send API GET Request
     *
     * @param $url
     * @param $headers
     *
     * @return bool|string
     */
    protected function send_get($url, $headers) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $result = curl_exec ($ch);

        curl_close ($ch);

        return $result;
    }

    /**
     * Write log
     *
     * @param $log
     */
    protected function write_log( $log )
    {
        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
        } else {
            error_log( $log );
        }
    }

    /**
     * Calculate revenue share total
     *
     * @param $order
     *
     * @return float|int
     */
    protected function get_revenue_share_total( $order )
    {
        $revenue_share_total = 0;

        foreach ( $order->get_items() as $item ) {
            $revenue_share_percent = (int) $item->get_product()->get_meta( 'poc_foundation_revenue_share' );

            if( empty( $revenue_share_percent ) ) {
                $revenue_share_percent = (int) self::$revenue_share;
            }

            $revenue_share = ( (int) $item->get_total() ) * ( $revenue_share_percent / 100 );

            $revenue_share_total += $revenue_share;
        }

        return $revenue_share_total;
    }

    /**
     * Check if coupon is valid or not
     * @param $coupon_code
     */
    protected function is_coupon_valid( $coupon_code )
    {
        $coupon_code = strtolower( $coupon_code );

        $response = wp_remote_get( "https://api.hostletter.com/api/poc_user/$coupon_code" );

        if( is_wp_error( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );

        $data = json_decode( $body, true );

        if( is_null( $data ) || $data['message'] != 'success' || empty( $data['data'] ) ) {
            return false;
        }

        return true;
    }
}