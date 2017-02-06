<?php	
/**
 * Plugin Name: Woo Already Bought
 * Plugin URI:  https://github.com/mattkrupnik/Woocommerce-Already-Bought
 * Description: Inform your clients if they bought any product already or if product is added to cart.
 * Version:     1.0
 * Author:      Matt Krupnik
 * Author URI:  http://mattkrupnik.com
 * License:     GPLv2+
 **/
register_activation_hook(   __FILE__, array( 'Woo_Already_Bought', 'activation' ) );
register_uninstall_hook(    __FILE__, array( 'Woo_Already_Bought', 'uninstall' ) );
class Woo_Already_Bought {
	const VERSION = '1.0'; //self::VERSION
	
	public function activation() {
		add_filter( 'woocommerce_general_settings', array ( $this, 'add_plugin_settings_to_general_tab' ) );
		
		if( get_option( 'already_bought_enable_option' ) == 'yes' ){
			
			if ( get_option( 'already_bought_enable_piaic' ) == 'yes' ){
				
				add_action( 'woocommerce_before_single_product', array ( $this, 'already_bought_product_is_already_in_cart' ), 10 );
				
			}
			
			if ( get_option( 'already_bought_enable_pwb' ) == 'yes' ){
				
				add_action( 'woocommerce_before_single_product', array ( $this, 'already_bought_product_was_bought' ), 10 );
				
			}
			
			if ( !empty(get_option( 'already_bought_add_to_cart_custom_msg' ) ) ){
				
				add_filter( 'wc_add_to_cart_message', array ( $this, 'already_bought_add_to_cart_message' ), 10, 2 );
				
			}
		}
		
	}
	
	
	public function already_bought_add_to_cart_message( $message, $product_id ) {
	
		global $product;
		
		$message = sprintf('<a href="%s" class="button wc-forwards">%s</a> %s',
				   get_permalink( woocommerce_get_page_id('cart') ),
				   __( 'View Cart', 'woocommerce' ),
				   __( str_replace( '{product}', get_the_title( $product_id ), get_option( 'already_bought_add_to_cart_custom_msg' ) ) ) );
		return $message;
		
	}
	
	
	public function already_bought_product_was_bought( $message, $product_id ) {
		global $product;
		if( empty( $product->id ) ){
			$wc_pf = new WC_Product_Factory();
			$product = $wc_pf->get_product( $id );
		}
		$current_user = wp_get_current_user();
		if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product->id ) ) {
			wc_add_notice( sprintf(
			'<a href="%s" class="button wc-forward">%s</a> %s', get_permalink( wc_get_page_id( 'shop' ) ),
			__( 'Continue Shopping', 'woocommerce' ),
			__( str_replace( '{product}', get_the_title( $_product->id ), get_option( 'already_bought_custom_msg_pwb' ) ) )
			), 'success' );
		}
		
	}
	
	
	public function already_bought_product_is_already_in_cart( $message, $product_id ) {
		foreach( WC()->cart->get_cart() as $cart_item_key => $values ) {
			$_product = $values['data'];
			if( get_the_ID() == $_product->id ) {
				
				wc_add_notice( sprintf(
				'<a href="%s" class="button wc-forward">%s</a> %s',
				get_permalink( wc_get_page_id( 'cart' ) ),
				__( 'View Cart', 'woocommerce' ),
				__( str_replace( '{product}', get_the_title( $_product->id ), get_option( 'already_bought_custom_msg_piaic' ) ) )
				), 'success' );
				
			}
			
		}
		
	}
	public function add_plugin_settings_to_general_tab( $settings ) {
	
		$settings_already_bought = array();
		foreach ( $settings as $section ) {
			if ( isset( $section['id'] ) && 'pricing_options' == $section['id'] && isset( $section['type'] ) && 'sectionend' == $section['type'] ) {
				$settings_already_bought[] = array( 'type' => 'sectionend', 'id' => 'Woo_already_bought' );
				// Add Title to Setting Page
				$settings_already_bought[] = array( 'name' => __( 'Alredy Bought', 'text-domain' ), 'type' => 'title', 'desc' => __( 'The following options are used to configure Already Bought', 'text-domain' ), 'id' => 'alredy_bought' );
				// Add Checkboox - Enable Plugin
				$settings_already_bought[] = array(
					'name'     => __( 'Enable Plugin' ),
					'id'       => 'already_bought_enable_option',
					'type'     => 'checkbox',
					'css'      => 'min-width:300px;',
					'desc'     => __( 'Enable plugin', 'text-domain' ),
				);
				
				
				// Add Text Field - Custom Message
				$settings_already_bought[] = array(
					'name'     => __( 'Add to Cart', 'text-domain' ),
					'desc_tip' => __( 'Set custom notification when product is add to cart. Use {product} if You want display product title.', 'text-domain' ),
					'id'       => 'already_bought_add_to_cart_custom_msg',
					'placeholder' => 'Enter custom message',
					'type'     => 'text',
					'css'      => 'min-width:300px;',
					'desc'     => __( 'Set custom message', 'text-domain' ),
				);
				
				// Add Checkbox - Enable Product is Already in Cart
				$settings_already_bought[] = array(
					'name'     => __( 'Product is Already in Cart' ),
					'id'       => 'already_bought_enable_piaic',
					'type'     => 'checkbox',
					'css'      => 'min-width:300px;',
					'desc'     => __( 'Enable option', 'text-domain' ),
				);
				
				// Add Text Field - Product is Already in Cart
				$settings_already_bought[] = array(
					//'name'     => __( 'Custom mesage', 'text-domain' ),
					'desc_tip' => __( 'Set custom message when product is already in cart. Use {product} if You want display product title.', 'text-domain' ),
					'id'       => 'already_bought_custom_msg_piaic',
					'default'      => 'This product is already in cart.',
					'placeholder' => 'Enter custom message',
					'type'     => 'text',
					'css'      => 'min-width:300px;',
					'desc'     => __( 'Set custom message', 'text-domain' ),
				);
				
				// Add Checkbox - Enable Product was Bought
				$settings_already_bought[] = array(
					'name'     => __( 'Product was Bought' ),
					'id'       => 'already_bought_enable_pwb',
					'type'     => 'checkbox',
					'css'      => 'min-width:300px;',
					'desc'     => __( 'Enable option', 'text-domain' ),
				);
				
				// Add Text Field - Product was Bought
				$settings_already_bought[] = array(
					//'name'     => __( 'Custom mesage', 'text-domain' ),
					'desc_tip' => __( 'Set custom message when user bought this product already. Use {product} if You want display product title.', 'text-domain' ),
					'id'       => 'already_bought_custom_msg_pwb',
					'default'      => 'You have bought this product already.',
					'placeholder' => 'Enter custom message',
					'type'     => 'text',
					'css'      => 'min-width:300px;',
					'desc'     => __( 'Set custom message', 'text-domain' ),
				);
			}
			$settings_already_bought[] = $section;
			
		}
		return $settings_already_bought;
	}
	
	
	public function uninstall() {
		if( current_user_can( 'activate_plugins' ) ){
		
			delete_option( 'already_bought_enable_option' );
			delete_option( 'already_bought_add_to_cart_custom_msg' );
			delete_option( 'already_bought_enable_piaic' );
			delete_option( 'already_bought_custom_msg_piaic' );
			delete_option( 'already_bought_enable_pwb' );
			delete_option( 'already_bought_custom_msg_pwb' );
		}
		
    }
}
$woo_already_bought = new Woo_Already_Bought();
$woo_already_bought->activation();
