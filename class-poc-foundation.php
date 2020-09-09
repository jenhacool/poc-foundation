<?php

require_once __DIR__ . '/vendor/autoload.php';
use kornrunner\Ethereum\Address;
use Ezdefi\Poc\Client;

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

	    add_action( 'admin_menu', array( $this, 'menu_pay_the_reward' ) );

        add_action( "wp_ajax_take_data_user", array( $this, 'so_wp_ajax_function' ) );

        add_action( "wp_ajax_nopriv_take_data_user", array( $this, 'so_wp_ajax_function' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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

        $amount = round( $this->get_revenue_share_total( $order ) / $poc_price, 6 );
        $release = time() + self::$refund_term * 60;
        
        //get referral id
        $ref_id = $this->get_id_referral( $order_id );
        // get ref_rate
        $ref_rate = get_post_meta( $order_id, 'ref_rates', true );
        // _uid is domain-orderId config from input
        $uid = add_post_meta($order_id, 'domain_order_id', true);
        // Send transaction
        $transaction_hash = $this->get_hash_from_send_transaction( $username, $amount, $release, $ref_rate );
        // Save hash to post_meta
        $this->save_transaction_hash( $order_id, $transaction_hash );
        // Save reward status
        $save_reward_status = add_post_meta( $order_id, 'reward_status', 'sent' );


//        $this->write_log("Added an affiliate TX:: username: ".$username." / ref_by: ".$ref_by." / uid: ".$this->get_uid_prefix()."-".$order_id." / amount: ".$amount." / release: ".$release);
//
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
        $currency = strtolower( get_woocommerce_currency() );
	    $price = json_decode( $this->send_request( self::$api_endpoint . '/getprice/poc/' . $currency ), true );
	    if ($price && is_numeric($price['data']['price']) && $price['data']['price'] > 0) {
		    return $price['data']['price'];
	    } else {
		    // Try again after 1s
		    sleep(1);
		    $price = json_decode( $this->send_request( self::$api_endpoint . '/getprice/poc/' . $currency ), true );
		    if ($price && is_numeric($price['data']['price']) && $price['data']['price'] > 0) {
			    return $price['data']['price'];
		    } else {
			    // Try again after 1s
			    sleep(1);
			    $price = json_decode( $this->send_request( self::$api_endpoint . '/getprice/poc/' . $currency ), true );
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

    public function menu_pay_the_reward()
    {
        add_menu_page(
            'pay the reward',
            'pay the reward',
            3,
            $this->get_slug_page_pay_the_reward(),
            array( $this, 'pay_the_reward' ),
            'dashicons-groups',
            '2'
        );
    }

    public function pay_the_reward()
    {
        $private_key = $this->valid_generate_private_key();

        if( !$private_key ) {
            $generate_key = new Address();
            $generate_key->get();
            $private_key = $generate_key->getPrivateKey();
            $this->save_private_key( $private_key );
            echo '<div class="notice notice-warning is-dismissible">
                    <p> This is private key: <b>'.$private_key.'</b></p>
                    <p> Please save private key of you</p>
                  </div>';
            return $private_key;
        }

        echo '<div class="notice notice-warning is-dismissible">
                    <p>Private key of you : <b>'.$private_key.'</b></p>
              </div>';

        $data = $this->get_data_referral_from_meta_table();

        $this->build_table_referral( $data );
        echo (get_post_meta(180, 'ref_rates', true));

        $home_url = get_home_url();
        $site_url = get_site_url();
        $site_url1 = site_url();

        echo $home_url;
        echo '<br />';
        echo $site_url.'<br />';
        echo $site_url1;

        return $private_key;

    }

    protected function valid_generate_private_key()
    {
        $get_private_key = get_option( 'private_key' );
        if( !$get_private_key ){
            return false;
        }
        return $get_private_key;
    }

    protected function save_private_key( $private_key )
    {
        $is_save_data = update_option( 'private_key', $private_key );
        if( $is_save_data ) {
            return true;
        }
        return false;
    }

    public function get_data_referral_from_meta_table()
    {
        global $wpdb;
        $sql = "SELECT post_id, meta_value
                FROM wp_postmeta 
                WHERE meta_key = 'reward_status'
                    AND meta_value = 'sent'
                    OR meta_value = 'error'
                ";
        $results = $wpdb->get_results($sql);
        return $results;
    }

    protected function build_table_referral( $data_array )
    {
        $url = wp_get_referer();
        ob_start(); ?>
        <form action="<?php echo $url ?>">
            <table id="reward-table" class="form-table comment-ays wp-list-table widefat fixed striped pages">
                <tr>
                    <th scope="row" class="manage-column num desc"><?php _e( 'ID Referral' ); ?></th>
                    <th scope="row" class="manage-column num desc"><?php _e( 'status' ); ?></th>
                </tr>
                <tbody id="table_id_referral">
                <?php
                foreach ($data_array as $item) {
                    ?>
                    <tr id="<?php echo $item->post_id ?>" >
                        <td class="manage-column num desc"><?php echo $item->post_id ?></td>
                        <td class="manage-column num desc" id="<?php echo 'id_referral_'.$item->post_id ?>"><?php echo $item->meta_value ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <br>
        </form>
        <button id="submit_pay_reward" > Check pay the reward </button>
        <?php
        $html = ob_get_clean();
        echo $html;
        return;
    }

    function so_wp_ajax_function()
    {
        $order_id = $_POST['order_id'];

        $transaction_hash = get_post_meta( $order_id, 'transaction_hash', true );

        $status = get_post_meta( $order_id, 'reward_status', true );

        $ref_by = get_post_meta( $order_id, 'ref_by', true );


        $new_status = $this->check_status_transaction_hash( $transaction_hash );

        if ( $status != $new_status ) {
            update_post_meta( $order_id, 'reward_status', $new_status );
        }

        switch ( $new_status ) {
            case 'error':
                // Gui email
                $message = 'fail. email send';
                break;
            case 'success':
                // Gui email
                $message = 'success. email send';
                break;
        }

        wp_send_json_success( array( 'reward_status' => $new_status, 'message' => $message ) );
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script( 'send_token_ajax', plugin_dir_url( __FILE__ ) . 'assets/pay_the_reward.js', array( 'jquery' ) );

        wp_localize_script( 'send_token_ajax', 'send_token_ajax_data',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' )
            )
        );
    }

    public function get_id_referral( $order_id )
    {
      return get_post_meta( $order_id, 'ref_by', true );
    }

    public function get_hash_from_send_transaction( $username, $amount, $release, $ref_rate )
    {
        // wait install composer send transaction
        $eth = new Client();
        $private_key = $this->valid_generate_private_key();
        $amount_hex = $eth->amountToWei( $amount );
        $release_hex = $this->bcdechex( $release );
        $ref_rate_hex = $this->bcdechex( $ref_rate );
        var_dump($ref_rate_hex);
        $data = [
             'transaction_data' => array(
                 'addressContract'  => '0x8d82238C53Db647A1911c6512cC40963b0c19B81', // pool contract address
                 'privateKey'       => $private_key,
                 'chainId'          => 66666,
                 'gas'              => 500000,
                 'gasPrice'         => '1000000',
                 'value'            => 0,
             ),
             'rpc_config' => array(
                 'url'                  => 'https://rpc.nexty.io',
                 'abi_json_file_path'   => 'http://localhost/ezdefi-send-token/poc_token_abi.json',
                 'name_abi'             => 'addTransaction'
             ),
             'param' => array(
                    '_uid'          => '', // domain-order-id config
                    '_username'     => $username, // username của khách hàng, nếu không có thì để domain
                    '_ref_by'       => '', // ref_by
                    '_amount'       => $amount_hex, // chính là số lượng trả thưởng =  giá trị đơn hàng * % trích về cho hệ thống -> hex
                    '_merchant'     => $username, // domain
                    '_subid'        => '', // nếu có thì điền không thì để trống
                    '_release'      => $release_hex, // thời gian hoàn thành đơn hàng + thời gian lock -> unixtime
                    '_ref_rates'    => [ $ref_rate_hex, '0', '0', '0', '0', '0', '0', '0', '0', '0']  // set cho don hang khi thanh cong
             )
         ];

//        $transaction_hash = $eth->sendTransaction($data);

//        $transaction_hash = '0xca1147d3543e51049ef00a6adc8617aceee5e08c6fd9c9338f09e0f928aa8008'; // khong thanh cong
        $transaction_hash = '0xa7f33447f68e9aee879621569326e133513fb90ee8d6b3bed08b095fe8828b77'; // thanh cong
        return $transaction_hash;
    }

    public function save_transaction_hash( $order_id, $transaction_hash )
    {
        add_post_meta( $order_id, 'transaction_hash', $transaction_hash );
    }

    // call api check status transaction hash
    public function check_status_transaction_hash( $hash )
    {
        // call api check status transaction hash
        $url = 'https://explorer.nexty.io/api?module=transaction&action=getstatus&txhash='.$hash;

        $response = wp_remote_get($url);

        $result = (json_decode($response['body'])->result->isError);

        if( $result === "1" ) {
            return 'error';
        }

        return 'success';

    }

    protected function get_slug_page_pay_the_reward() {
        $slug_page = 'pay_the_reward';
        return $slug_page;
    }

    protected function bcdechex($dec)
    {
        $hex = '';
        do {
            $last   = bcmod($dec, 16);
            $hex    = dechex($last).$hex;
            $dec    = bcdiv(bcsub($dec, $last), 16);
        } while($dec > 0);
        return $hex;
    }
}