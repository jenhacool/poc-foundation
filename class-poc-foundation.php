<?php

class POC_Foundation {
    private static $instance;

    /**
     * API Endpoint
     *
     * @var string
     */
    private static $api_endpoint = "https://api.poc.me/api";

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

        add_action( 'woocommerce_checkout_order_processed', array( $this, 'add_ref_to_order' ), 10, 3 );

        add_action( 'woocommerce_order_status_completed', array( $this, 'after_order_completed' ) );

        add_action( 'woocommerce_order_status_refunded', array( $this, 'after_order_refunded' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );

        add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_custom_product_data_field' ) );

        add_action( 'woocommerce_process_product_meta', array( $this, 'save_custom_product_data_field' ) );

        add_filter( 'woocommerce_get_shop_coupon_data', array( $this, 'create_virtual_coupon' ), 10, 2  );

        add_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'get_discount_amount' ), 10, 6 );

        add_action( 'woocommerce_before_cart', array( $this, 'apply_coupon_by_ref_by' ) );

        add_action( 'admin_menu', array( $this, 'register_options_page' ) );

        add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );

	    add_action( 'elementor_pro/init', array( $this, 'add_elementor_form_action' ) );

	    add_action( 'elementor/dynamic_tags/register_tags', array( $this, 'register_dynamic_tags' ) );

	    add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
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
    public function add_ref_to_order( $order_id, $posted_data, $order ) {
        if( ! empty( get_post_meta( $order_id, 'ref_by' ) ) ) {
            return;
        }

	    $coupon_codes = $order->get_coupon_codes();

        if ( ! empty( $coupon_codes ) && $this->is_coupon_valid( $coupon_codes[0] ) ) {
            $ref_by = $coupon_codes[0];
        } else {
	        $ref_by = ! empty( $_COOKIE['ref_by'] ) ? $_COOKIE['ref_by'] : get_user_meta( get_current_user_id(), 'ref_by', true );
        }

        if ( empty( $ref_by ) ) {
            return;
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

        $username = $this->get_uid_prefix();

        $amount = round( $this->get_revenue_share_total( $order ) * self::$currency_exchange / $poc_price, 6 );
        $release = time() + self::$refund_term * 60;

        $this->write_log("Added an affiliate TX:: username: ".$username." / ref_by: ".$ref_by." / uid: ".$this->get_uid_prefix()."-".$order_id." / amount: ".$amount." / release: ".$release);

	    $result = $this->send_request( self::$api_endpoint . "/transaction/addtransaction/username/".$username."/ref_by/".$ref_by."/uid/".$this->get_uid_prefix()."-".$order_id."/amount/".$amount."/merchant/".$this->get_uid_prefix()."/release/".$release."/" );
	    $result = json_decode( $result, true );
	    if ($result['message'] != "Done")  $this->write_log("Error while adding an affiliate TX:: username: ".$username." / uid: ".$this->get_uid_prefix().$order_id." / amount: ".$amount." / release: ".$release);
    }

    /**
     * Handle refunded order
     *
     * @param $order_id
     */
    public function after_order_refunded( $order_id )
    {
        $this->write_log("Revoked an affiliate TX:: ".$this->get_uid_prefix().$order_id);

        $result = $this->send_request(self::$api_endpoint."/revoketransaction/uid/".$this->get_uid_prefix().$order_id."/");

        if ($result != "Done") {
            $this->write_log("Error while revoke a Tx:: uid: ".$this->get_uid_prefix().$order_id);
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
     * Add custom product data field
     */
    public function add_custom_product_data_field()
    {
        woocommerce_wp_text_input( array(
            'id' => 'poc_foundation_discount',
            'label' => __( 'POC Discount' ),
        ) );

        woocommerce_wp_text_input( array(
            'id' => 'poc_foundation_revenue_share',
            'label' => __( 'POC Revenue share' ),
        ) );
    }

    /**
     * Save custom product data field
     *
     * @param $post_id
     */
    public function save_custom_product_data_field( $post_id )
    {
        if( ! isset( $_POST['poc_foundation_discount'] ) && ! isset( $_POST['poc_foundation_revenue_share'] ) ) {
            return;
        }

        $product = wc_get_product( $post_id );

        if( ! $product ) {
            return;
        }

        $discount = ( $_POST['poc_foundation_discount'] ) ? sanitize_text_field( $_POST['poc_foundation_discount'] ) : '';
        $revenue_share = ( $_POST['poc_foundation_revenue_share'] ) ? sanitize_text_field( $_POST['poc_foundation_revenue_share'] ) : '';

        $product->update_meta_data( 'poc_foundation_discount', $discount );
        $product->update_meta_data( 'poc_foundation_revenue_share', $revenue_share );

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

        $discount = (float) $cart_item['line_subtotal'] * ( (int) $custom_discount / 100 );

        $round = round( $discount, wc_get_rounding_precision() );

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
     * Plugin options page
     */
    public function register_options_page()
    {
        add_menu_page(
            'POC Foundation',
            'POC Foundation',
            'manage_options',
            'poc-foundation',
            array( $this, 'options_page_html' )
        );
    }

    /**
     * Plugin options page HTML
     */
    public function options_page_html()
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error( 'poc_foundation_messages', 'poc_foundation_message', __( 'Settings Saved', 'poc_foundation' ), 'updated' );
        }

        settings_errors( 'poc_foundation_messages' );

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php settings_fields( 'poc_foundation_option_group' ); ?>
                <?php do_settings_sections( 'poc_foundation_option_group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">API Key</th>
                        <td><input type="text" name="poc_foundation_api_key" value="<?php echo esc_attr( get_option( 'poc_foundation_api_key' ) ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">UID Prefix</th>
                        <td><input type="text" name="poc_foundation_uid_prefix" value="<?php echo esc_attr( get_option( 'poc_foundation_uid_prefix' ) ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Redirect Page</th>
                        <td>
                            <select name="poc_foundation_redirect_page" id="">
                                <?php foreach ( get_pages() as $page ) : ?>
                                    <option value="<?php echo $page->ID; ?>" <?php echo ( get_option( 'poc_foundation_redirect_page' ) == $page->ID ) ? 'selected' : ''; ?>><?php echo $page->post_title; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Fanpage URL</th>
                        <td><input type="text" name="poc_foundation_fanpage_url" value="<?php echo esc_attr( get_option( 'poc_foundation_fanpage_url' ) ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Fanpage ID</th>
                        <td><input type="text" name="poc_foundation_fanpage_id" value="<?php echo esc_attr( get_option( 'poc_foundation_fanpage_id' ) ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Chatbot Backlink</th>
                        <td><input type="text" name="poc_foundation_chatbot_backlink" value="<?php echo esc_attr( get_option( 'poc_foundation_chatbot_backlink' ) ); ?>"></td>
                    </tr>
                </table>
                <?php submit_button( 'Save Settings' ); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Plugin settings init
     */
    public function register_plugin_settings()
    {
        register_setting( 'poc_foundation_option_group', 'poc_foundation_api_key' );
        register_setting( 'poc_foundation_option_group', 'poc_foundation_uid_prefix' );
        register_setting( 'poc_foundation_option_group', 'poc_foundation_redirect_page' );
        register_setting( 'poc_foundation_option_group', 'poc_foundation_fanpage_id' );
	    register_setting( 'poc_foundation_option_group', 'poc_foundation_fanpage_url' );
	    register_setting( 'poc_foundation_option_group', 'poc_foundation_chatbot_backlink' );
    }

	/**
	 * Add custom Elementor action
	 */
    public function add_elementor_form_action()
    {
        include_once( POC_FOUNDATION_PLUGIN_DIR . 'elementor/modules/forms/actions/poc.php' );

        $poc_affiliate_notifier = new POC_Affiliate_Notifier( array(
	        'api_endpoint' => self::$api_endpoint,
	        'api_key' => $this->get_api_key(),
	        'domain' => $this->get_uid_prefix()
        ) );

        \ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' )->add_form_action( $poc_affiliate_notifier->get_name(), $poc_affiliate_notifier );
    }

    public function register_dynamic_tags( $dynamic_tags )
    {
        \Elementor\Plugin::$instance->dynamic_tags->register_group( 'poc-foundation-dynamic-tags', [
            'title' => 'POC Foundation'
        ] );

        include_once( POC_FOUNDATION_PLUGIN_DIR . 'elementor/core/dynamic-tags/facebook-url-tag.php' );
        include_once( POC_FOUNDATION_PLUGIN_DIR . 'elementor/core/dynamic-tags/facebook-id-tag.php' );
	    include_once( POC_FOUNDATION_PLUGIN_DIR . 'elementor/core/dynamic-tags/poc-ref-by-tag.php' );
	    include_once( POC_FOUNDATION_PLUGIN_DIR . 'elementor/core/dynamic-tags/poc-subid-tag.php' );

        $dynamic_tags->register_tag( 'Facebook_URL_Tag' );
        $dynamic_tags->register_tag( 'Facebook_ID_Tag' );
	    $dynamic_tags->register_tag( 'POC_Ref_By_Tag' );
	    $dynamic_tags->register_tag( 'POC_SubID_Tag' );
    }

    public function register_rest_routes()
    {
	    register_rest_route(
		    'poc-foundation/v1',
		    '/get_fanpage',
		    array(
			    'methods' => 'POST',
			    'callback' => array( $this, 'get_fanpage' )
		    )
	    );
    }

    public function get_fanpage( $request ) {
	    $params = $request->get_json_params();

	    $ref_by = $params['ref_by'];

	    $response = wp_remote_get( get_option( 'poc_foundation_api_endpoint' ) . '/website/get_fanpage/' . $ref_by, array(
		    'headers' => array(
			    'Content-Type' => 'application/json',
			    'api-key' => get_option( 'poc_foundation_api_key' )
		    ),
	    ) );

	    if ( is_wp_error( $response ) ) {
		    return $this->rest_response( array(
			    'link' => ''
		    ) );
	    }

	    $fanpage_data = json_decode( wp_remote_retrieve_body( $response ), true );

	    if ( empty( $fanpage_data ) || ! isset( $fanpage_data['data'] ) || ! isset( $fanpage_data['data']['fanpage_url'] ) || ! isset( $fanpage_data['data']['fanpage_id'] ) ) {
		    return $this->rest_response( array(
			    'link' => ''
		    ) );
	    }

	    return $this->rest_response( array(
            'link' => 'https://www.messenger.com/t/' . $fanpage_data['fanpage_id']
        ) );
    }

    protected function rest_response( $data )
    {
	    $response = new \WP_REST_Response( $data );

	    $response->set_status( 200 );

	    return $response;
    }

    /**
     * Get POC Price
     *
     * @return bool|string
     */
    protected function get_poc_price()
    {
	    $price = json_decode( $this->send_request( self::$api_endpoint . '/getprice/poc' ), true );
	    if ($price && is_numeric($price['data']['price']) && $price['data']['price'] > 0) {
		    return $price['data']['price'];
	    } else {
		    // Try again after 1s
		    sleep(1);
		    $price = json_decode( $this->send_request( self::$api_endpoint . '/getprice/poc' ), true );
		    if ($price && is_numeric($price['data']['price']) && $price['data']['price'] > 0) {
			    return $price['data']['price'];
		    } else {
			    // Try again after 1s
			    sleep(1);
			    $price = json_decode( $this->send_request( self::$api_endpoint . '/getprice/poc' ), true );
			    if ($price && is_numeric($price['data']['price']) && $price['data']['price'] > 0) {
				    return $price['data']['price'];
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
            'api-key: ' . $this->get_api_key(),
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
     *
     * @param $coupon_code
     *
     * @return boolean
     */
    protected function is_coupon_valid( $coupon_code )
    {
        $coupon_code = strtolower( $coupon_code );

	    $response = wp_remote_get( "https://api.poc.me/api/user/$coupon_code" );

	    if( is_wp_error( $response ) ) {
		    return false;
	    }

	    $body = wp_remote_retrieve_body( $response );

	    $data = json_decode( $body, true );

	    if( is_null( $data ) || $data['message'] != 'success' ) {
		    return false;
	    }

	    return true;
    }

    /**
     * Get API Key
     *
     * @return mixed|void
     */
    protected function get_api_key()
    {
        return get_option( 'poc_foundation_api_key' );
    }

    /**
     * Get UID Prefix
     *
     * @return mixed|void
     */
    protected function get_uid_prefix()
    {
        return get_option( 'poc_foundation_uid_prefix' );
    }
}