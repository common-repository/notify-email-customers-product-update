<?php
/*
 * Plugin Name: Bulk Email Notify Customers on Product Update for WooCommerce
 * Description: This plugin provides a metabox in product edit screen to Notify WooCommerce Customers once updating your Product
 * Version: 1.1
 * Author: extendWP
 * Author URI: https://extend-wp.com
 *
 * WC requires at least: 2.2
 * WC tested up to: 8.4
 *   
 * License: GPL2
 * Created On: 28-01-2021
 * Updated On: 21-12-2023
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class NotifyWooCustomers{
	
	public $plugin = 'not_woo_customers';
	public $slug = 'notify-woo-customers';
	public $email_content;
	public $name = 'Bulk Email Notify Customers on Product Update for WooCommerce';
	public $menuPosition ='50';
	public $extensions = 'https://extend-wp.com/wp-json/products/v2/product/category/woocommerce';
	public $allowed_html = array(
            'a' => array(
                'style' => array(),
                'href' => array(),
                'title' => array(),
                'class' => array(),
                'id'=>array()                   
            ),
			'i' => array('style' => array(),'class' => array(),'id'=>array() ),
            'br' => array('style' => array(),'class' => array(),'id'=>array() ),
            'em' => array('style' => array(),'class' => array(),'id'=>array() ),
            'strong' => array('style' => array(),'class' => array(),'id'=>array() ),
            'h1' => array('style' => array(),'class' => array(),'id'=>array() ),
            'h2' => array('style' => array(),'class' => array(),'id'=>array() ),
            'h3' => array('style' => array(),'class' => array(),'id'=>array() ),
            'h4' => array('style' => array(),'class' => array(),'id'=>array() ),
            'h5' => array('style' => array(),'class' => array(),'id'=>array() ),
            'h6' => array('style' => array(),'class' => array(),'id'=>array() ),
            'img' => array('style' => array(),'class' => array(),'id'=>array() ),
            'p' => array('style' => array(),'class' => array(),'id'=>array() ),
            'ul' => array('style' => array(),'class' => array(),'id'=>array() ),
            'li' => array('style' => array(),'class' => array(),'id'=>array() ),
            'ol' => array('style' => array(),'class' => array(),'id'=>array() ),
            'video' => array('style' => array(),'class' => array(),'id'=>array() ),
            'blockquote' => array('style' => array(),'class' => array(),'id'=>array() ),
            'style' => array(),            
            'img' => array(
                'alt' => array(),
                'src' => array(),
                'title' => array(),
                'style' => array(),
                'class' => array(),
                'id'=>array()
            ),
	);
	
	public function __construct() {	
		
		add_action( 'admin_menu', array( $this, 'SettingsPage' ) );
		add_action( "admin_init", array( $this, 'adminPanels' ) );		
		add_action( 'admin_enqueue_scripts', array( $this, 'BackEndScripts' ) );
		add_action( "admin_init", array( $this,"metaBox" ) );
		add_action( 'save_post', array( $this, 'trigger_email' ) );
		add_action( 'wp_ajax_getOrderEmails', array( $this, 'getOrderCustomerEmail' ) );
		add_action( esc_html( $this->plugin ).'_getEmails', array( $this, 'getOrderCustomerEmail' ), 10  );		
		add_action( 'wp_ajax_nopriv_not_woo_customers_extensions', array( $this,'extensions' ) );
		add_action( 'wp_ajax_not_woo_customers_extensions', array( $this,'extensions' ) );	
		add_action( 'activated_plugin',  array( $this,'redirectOnActivation' ),10, 1 );	
		register_activation_hook( __FILE__,  array( $this, 'onActivation') );
		add_action( 'admin_notices',  array( $this, 'notice' ), 10, 1 );

			// HPOS compatibility declaration

			add_action( 'before_woocommerce_init', function() {
				if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
				}
			} );		
	}

	// function to query, display & finally delete an admin notice for this plugin

	public function notice() {
		
		$notice = get_option( esc_html( $this->plugin ) . "_notice", array() );
		// if a notice exists dipplay it in admin_notices section		
		if( !empty( $notice ) ){
					
			printf( '<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
				esc_html( $notice['type'] ),
				esc_html( $notice['dismissible'] ),
				esc_html( $notice['notice'] )
			);
	
		}
		
		// Now delete the option
		if( ! empty( $notice ) ) {
			delete_option( sanitize_text_field( $this->plugin ). "_notice", array() );
		}
	}

	// on activation hook, register the default for the plugin

	public function onActivation(){
						
		update_option( sanitize_text_field( $this->plugin ). '_enable', 'true' );			
		update_option( sanitize_text_field( $this->plugin ).'_post_types', 'product' );
		
	}

// once activated redirect to plugin settings screen

	public function redirectOnActivation( $plugin ){
			
		if( $plugin == plugin_basename( __FILE__ ) ) {
			wp_redirect( esc_url( admin_url( "admin.php?page=". $this->slug ) ) );
			exit;	
		}
	}	

	// enqueue the necessary scripts	

	public function BackEndScripts(){
			
		wp_enqueue_style( esc_html( $this->plugin )."adminCss", plugins_url( "/css/backend.css?v=1s", __FILE__ ) );	
		wp_enqueue_style( esc_html( $this->plugin )."adminCss" );	
						
		wp_enqueue_script('jquery');
		wp_enqueue_style( 'dashicons' );
        wp_enqueue_script( 'jquery-ui-datepicker' ); // enqueue datepicker from WP
		
		wp_enqueue_script( esc_html( $this->plugin )."_Js", plugins_url( "/js/backend.js" , __FILE__ ) , array( 'jquery','jquery-ui-datepicker' ) , null, true );
		
		$this->localizeBackend = array( 
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		);	
		
		wp_localize_script( esc_html( $this->plugin )."_Js", esc_html( $this->plugin ) , $this->localizeBackend );
		wp_enqueue_script( esc_html( $this->plugin )."_Js" );
	}

	// menu pages of the plugin

	public function SettingsPage(){
		
		add_submenu_page( 'woocommerce', esc_html__( "Notify Customers", 'not_woo_customers' ), esc_html__( "Notify Customers", 'CrmErpSolution' ), 'administrator' , esc_html( $this->slug ), array( $this, "init" ) );	
		
		add_submenu_page( 'edit.php?post_type=product', esc_html__( "Notify Customers", 'not_woo_customers' ), esc_html__( "Notify Customers", 'not_woo_customers' ), 'administrator',  esc_html( $this->slug ),  array( $this, "init" ) );
		
	}

	// metabox enable function for option settings	

	public function enable(){
		
		if( isset( $_REQUEST[ $this->plugin.'_enable' ] ) ){
			$enable =  sanitize_text_field( $_REQUEST[$this->plugin.'_enable' ] );
		}else $enable = sanitize_text_field( get_option( $this->plugin.'_enable' ) ); 
		
		?>
		<input type="checkbox" name="<?php print esc_attr( $this->plugin. "_enable" ); ?>" id="<?php print esc_attr( $this->plugin. "_enable" );?>" value='true'  <?php if( $enable == 'true' ) print "checked"; ?> />
		<?php
	}

	// post types selection function for option settings	

	public function post_types(){
		
		if( isset( $_REQUEST[ $this->plugin.'_post_types'] ) ){
			$post_types =  sanitize_textarea_field( $_REQUEST[$this->plugin.'_post_types'] );
		}elseif( get_option( $this->plugin.'_post_types' ) != '' ){
			$post_types = sanitize_text_field( get_option( $this->plugin.'_post_types' ) );
		}
		?>
		<textarea name='<?php print esc_attr( $this->plugin. "_post_types" ); ?>' placeholder='<?php esc_html_e( "write comma separated Post Types names", "not_woo_customers" ); ?>' ><?php if( isset( $post_types ) ) print esc_html( $post_types ) ;?></textarea>

		<?php
	}

	// from name ( what will be displayed on emails ) function for option settings	

	public function fromName(){
		
		if( isset( $_REQUEST[ $this->plugin.'_fromName'] ) ){
			$fromName =  sanitize_text_field( $_REQUEST[$this->plugin.'_fromName'] );
		}elseif( get_option( $this->plugin.'_fromName' ) != '' ){
			$fromName = sanitize_text_field( get_option( $this->plugin.'_fromName' ) );
		}else $fromName = get_bloginfo( 'name' );
		?>
		<input type='text' name='<?php print esc_attr( $this->plugin. "_fromName" ); ?>' value = '<?php print esc_attr( $fromName ); ?>' />


		<?php
	}

	// from email ( what email will be displayed on emails ) function for option settings	

	public function fromEmail(){
		
		if( isset( $_REQUEST[ $this->plugin.'_fromEmail'] ) ){
			$fromEmail =  sanitize_text_field( $_REQUEST[$this->plugin.'_fromEmail'] );
		}elseif( get_option( $this->plugin.'_fromEmail' ) != '' ){
			$fromEmail = sanitize_text_field( get_option( $this->plugin.'_fromEmail' ) );
		}else $fromEmail = get_bloginfo( 'admin_email' );
		?>
		<input type='email' name='<?php print esc_attr( $this->plugin. "_fromEmail" ); ?>' value = '<?php print esc_attr( $fromEmail ); ?>' />

		<?php
	}

	// build the options panel - plugin settings

	public function adminPanels(){
		
		$this->plugin  = sanitize_text_field( $this->plugin ); 
		
		add_settings_section( esc_html( $this->plugin )."general", "", null, esc_html( $this->plugin ). "general-options" );
		
		add_settings_field( 'enable', esc_html__( "Enable Email Metabox", "not_woo_customers" ), array( $this, 'enable' ),  esc_html( $this->plugin )."general-options", esc_html( $this->plugin )."general" );			
		register_setting( esc_html( $this->plugin )."general", esc_html( $this->plugin ).'_enable' );
		
		add_settings_field( 'post_types', esc_html__( "Enable to Post Types", "not_woo_customers" ), array( $this, 'post_types' ),  esc_html( $this->plugin )."general-options", esc_html( $this->plugin )."general" );			
		register_setting( esc_html( $this->plugin )."general", esc_html( $this->plugin ).'_post_types' );

		add_settings_field( 'fromEmail', esc_html__( "Email From Email Address (from which address email is sent) ", "not_woo_customers" ), array( $this, 'fromEmail' ),  esc_html( $this->plugin )."general-options", esc_html( $this->plugin )."general" );			
		register_setting( esc_html( $this->plugin )."general", esc_html( $this->plugin ).'_fromEmail' );
		
		add_settings_field( 'fromName', esc_html__( "Email From Name (who sends the emails)", "not_woo_customers" ), array( $this, 'fromName' ),  esc_html( $this->plugin )."general-options", esc_html( $this->plugin )."general" );			
		register_setting( esc_html( $this->plugin )."general", esc_html( $this->plugin ).'_fromName' );		
					
	}

	// display the settings in plugin menu page 

	public function settings(){ 
		
		$this->process_settings();
		$this->plugin  = sanitize_text_field( $this->plugin ); 
		?>
		
		<form method='POST' id='<?php print esc_attr( $this->plugin ).'_settings'; ?>' > 
			
		<?php
			settings_fields( esc_html( $this->plugin ).'general-options' );
			do_settings_sections( esc_html( $this->plugin).'general-options' );	
			
			wp_nonce_field( esc_html( $this->plugin )."_settings", esc_html( $this->plugin )."_settings" );			
			submit_button();
		?>
		</form>

		<?php	
		
	}

	// if specific form saved process the settings

	public function process_settings(){
		
		if( $_SERVER['REQUEST_METHOD'] == 'POST' && current_user_can('administrator') && wp_verify_nonce( $_REQUEST[ $this->plugin."_settings" ], esc_html( $this->plugin )."_settings" ) ) { 

			if( isset( $_REQUEST[ $this->plugin."_enable" ] ) ){
				update_option( sanitize_text_field( $this->plugin )."_enable" ,'true' );				
			}else update_option( sanitize_text_field( $this->plugin )."_enable" ,'' );	
			
			if( isset( $_REQUEST[ $this->plugin."_post_types" ] ) ){
				update_option( sanitize_text_field( $this->plugin )."_post_types" , sanitize_text_field( $_REQUEST[ $this->plugin."_post_types" ] ) );				
			}else update_option( sanitize_text_field( $this->plugin )."_post_types" ,'' );	

			if( isset( $_REQUEST[ $this->plugin."_fromName" ] ) ){
				update_option( sanitize_text_field( $this->plugin )."_fromName" , sanitize_text_field( $_REQUEST[ $this->plugin."_fromName" ] ) );				
			}else update_option( sanitize_text_field( $this->plugin )."_fromName" ,'' );	

			if( isset( $_REQUEST[ $this->plugin."_fromEmail" ] ) && is_email( $_REQUEST[ $this->plugin."_fromEmail" ] ) ){
				update_option( sanitize_text_field( $this->plugin )."_fromEmail" , sanitize_email( $_REQUEST[ $this->plugin."_fromEmail" ] ) );				
			}else update_option( sanitize_text_field( $this->plugin )."_fromEmail" ,'' );	
			
		}
		
	}


	// if metabox enabled, register metabox in product edit screen && other post types  - if selected 

	public function metaBox( $post ){
		
		$this->plugin  = sanitize_text_field( $this->plugin ); 
		
		if( get_option( esc_html( $this->plugin )."_enable" ) ){
			
			if( get_option( esc_html( $this->plugin )."_post_types" ) ){
				
				$post_type = explode( "," , sanitize_text_field( get_option( $this->plugin."_post_types" ) ) );
				
			}else $post_type = array( 'product' );
			
			foreach( $post_type as $type ){
				
				if( $type == 'product'){
					
					add_meta_box( "sendmail", esc_html__('Send Email', "not_woo_customers" ), array( $this, "fieldsCreateForProducts" ) , esc_html( $type ), "normal", "high" ); 
					
				}else{
					
					add_meta_box( "sendmail", esc_html__('Send Email', "not_woo_customers" ), array( $this, "fieldsCreate" ) , esc_html( $type ), "normal", "high" ); 
					
				}
			}
		}		
	}

	// metabox to display for products case

	public function fieldsCreateForProducts( $post ){
		
		global $post;

			?>
			<div class='<?php print esc_html( $this->plugin ); ?>_meta_box '>
				<p>	
					<label><?php esc_html_e( 'Send Email' , "not_woo_customers" )?></label>
					<input type="checkbox"  class='<?php print esc_attr( $this->plugin )."_send_email";?>' name='<?php print esc_attr( $this->plugin )."_send_email";?>' value='1' />
				</p>
				
				<div>
				
				<?php
				
				$product_id = ( isset( $_REQUEST['post'] ) ) ? sanitize_text_field( $_REQUEST['post'] ) : "" ;
				
				if(  $product_id != '' ) { ?>
					<p>	
						<label><?php esc_html_e( 'Order Date From ' , "not_woo_customers" )?></label>
						<input type="text"  placeholder='dd/mm/yyyy' class="<?php print esc_attr( $this->plugin );?>_datepicker <?php print esc_attr( $this->plugin )."_from";?>" name='<?php print esc_attr( $this->plugin )."_from";?>' value='' />
					</p>
		 
					<p>
						<label><?php esc_html_e( 'Order Date To ', "not_woo_customers" )?></label>
						<input type="text"  class="<?php print esc_attr( $this->plugin ) ;?>_datepicker <?php print esc_attr( $this->plugin )."_to"; ?>" placeholder='dd/mm/yyyy' name='<?php print esc_attr( $this->plugin )."_to"; ?>' value = '' />
						
					</p>
					
					<p>
						<input type='button' class='button-primary getEmails' value='<?php esc_html_e( 'Query Customers' , "not_woo_customers" ); ?>' /> 
					</p>
					<p>
					<input type='hidden' id='product_id' name='product_id' value='<?php print esc_attr( $product_id ); ?>' /> 
					
				<?php } ?>	
				
					<textarea style='width:100%' placeholder='<?php esc_html_e( 'Insert Emails comma separated or get search results based on Order Dates' , "not_woo_customers" )?>' name='<?php print esc_attr( $this->plugin ); ?>_user_emails' class='<?php print esc_attr( $this->plugin );?>_user_emails' ><?php do_action( esc_html( $this->plugin ).'_getEmails' ); ?></textarea>	
					</p>
									
					<p>
						<label><?php esc_html_e( 'Email Subject' , "not_woo_customers" )?></label>
						<input type="text"  class="<?php print esc_attr( $this->plugin );?>_wait" name='<?php print esc_attr( $this->plugin )."_sbj"; ?>' value = '' />
					</p>				
					<p>
					<?php

					echo wp_editor( apply_filters( $this->email_content, $this->email_content ), esc_attr( $this->plugin ).'_email_content', array( "wpautop" => true, 'textarea_name' => esc_attr( $this->plugin ).'_email_content', 'textarea_rows' => '5','editor_height' => 225)  );	?>

					</p>
				</div>
			</div>
			<?php

	}
	
	// metabox to display for other post types

	public function fieldsCreate( $post ){
		
		global $post;
		
			?>
			<div class='<?php print esc_attr( $this->plugin ); ?>_meta_box '>
				<p>	
					<label><?php esc_html_e( 'Send Email' , "not_woo_customers" )?></label>
					<input type="checkbox"  class='<?php print esc_attr( $this->plugin )."_send_email";?>' name='<?php print esc_attr( $this->plugin )."_send_email";?>' value='1' />
				</p>
				
				<div>
					<p>
						<textarea  style='width:100%' placeholder='<?php esc_html_e( 'Insert Emails comma separated' , "not_woo_customers" )?>' name='<?php print esc_attr( $this->plugin ); ?>_user_emails' class='<?php print esc_attr( $this->plugin ) ; ?>_user_emails' ></textarea>	
					</p>
						
						
					<p>
						<label><?php esc_html_e( 'Email Subject' , "not_woo_customers" )?></label>
						<input type="text"  class="<?php print esc_attr( $this->plugin ); ?>_wait" name='<?php print esc_attr( $this->plugin )."_sbj"; ?>' value = '' />
					</p>				
					<p>
					<?php
						echo wp_editor( apply_filters( $this->email_content, $this->email_content ), esc_attr( $this->plugin ).'_email_content', array( "wpautop" => true, 'textarea_name' => esc_attr( $this->plugin ).'_email_content', 'textarea_rows' => '5', 'editor_height' => 225 ) );	?>
					</p>
				</div>

			</div>
			<?php

	}
	
	// get customers emails ( product case ) if user clicks the button
	
	public function getOrderCustomerEmail( ){
		
		if ( is_admin() && current_user_can( 'administrator' ) ){
		
			if ( isset( $_REQUEST['from'] ) && isset( $_REQUEST['to'] ) && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'getOrderEmails' ) {
				
				// from and to fields are mandatory
				if( isset( $_REQUEST['from'] ) ) $from = sanitize_text_field( $_REQUEST['from'] );
				
				if( isset( $_REQUEST['to'] ) ){
					$to = sanitize_text_field( $_REQUEST['to'] );
				}else	$to = date("Y-m-d");
					
				$emails = array();
				
				$args = array(
				 'limit' => '-1',
				 'return' => 'ids',
				 'type' => 'shop_order',
				 'date_completed' => $from."...".$to,
				 'status' => 'completed'
				);
				$query = new WC_Order_Query( $args );
				$orders = $query->get_orders();
				
				// get $_REQUEST product id from edit screen to compare it with order items
				
				$product_id = ( isset( $_REQUEST['product_id'] ) ) ? sanitize_text_field( $_REQUEST['product_id'] ) : "" ;
				
				foreach( $orders as $order_id ) {
					
					$order = wc_get_order( $order_id );
					
					$items    = $order->get_items();
					
					foreach ( $items as $item ) {
						
						  $id = $item['product_id'];
						  
						  if ( $id == $product_id ) {
							  
								// if product id of order same with request add to queue
								if( !in_array( $order->get_billing_email(), $emails ) ){
									
									array_push( $emails, $order->get_billing_email() );
									
								}					
						  }
					}
					
				}
				// display in the textarea for email use
				print esc_html( implode( ",", $emails ) );
				wp_die();
			}
		
		}
	}
	
	// function to send the email on product Save Action
	
	public function trigger_email( $post_id ) {

		if( get_option( $this->plugin."_enable" ) ){ //enable option needs to be enabled
			
			if( get_option( $this->plugin."_post_types" ) ){
				
				$post_type = esc_html( get_option( $this->plugin."_post_types" ) );
				
			}else $post_type = 'product';
					
		
			if( strstr( $post_type , get_post_type() ) ){ // we check the post type as email will be sent only on post save of the selected post types

				if( isset( $_POST[$this->plugin.'_send_email'] ) ){
					
					if ( !empty( $_POST[$this->plugin.'_send_email'] ) ) { // send mail checkbox needs to be checked
						
						if( isset( $_POST[$this->plugin.'_user_emails'] ) && !empty( $_POST[$this->plugin.'_user_emails'] )  ){ // we need emails in the textarea
											
							if( !empty( $_POST[$this->plugin.'_sbj'] ) && !empty( $_POST[$this->plugin.'_email_content'] ) ) {  // we need content && subject as well
								
								$emails =  explode( ",", sanitize_textarea_field( $_POST[$this->plugin.'_user_emails'] ) );							
									
								$subject = sanitize_text_field( $_POST[$this->plugin.'_sbj'] ); 
								$content =  wp_kses( $_POST[$this->plugin.'_email_content'], $this->allowed_html ) ; 
								
								$headers[] = "Content-Type: text/html; charset=UTF-8";
								
								if( !empty( get_option( $this->plugin.'_fromEmail' ) ) ){
									$fromemail   = sanitize_text_field( get_option( $this->plugin.'_fromEmail' ) );
								}else $fromemail = sanitize_text_field( get_bloginfo( 'admin_email' ) );
															
								if( !empty( get_option( $this->plugin.'_fromName' ) ) ){
									$fromname   = sanitize_text_field( get_option( $this->plugin.'_fromName' ) );
								}else $fromname = sanitize_text_field( get_bloginfo( 'name' ) );


								$headers[] = "From: ".esc_html( $fromname )." <".esc_html( $fromemail ).">";						
								
								$emailsChecked = array();
								
								foreach( $emails as $email ) {
																	
									
									if( is_email( $email ) ){
										//valid email address to send the email
										$send = wp_mail( esc_html( $email ),  esc_html( $subject ) , wp_kses( $content, $this->allowed_html ) ,$headers );
										
										if( !$send ) {	
											
											update_option( sanitize_text_field( $this->plugin ) ."_notice", array( 'notice' => esc_html__("Mail could not be sent", "not_woo_customers" ), 'type' => 'error', 'dismissible' => 'is-dismissible'  ) );
																						
										} else {
											array_push( $emailsChecked, $email );
											update_option( sanitize_text_field( $this->plugin )."_notice", array( 'notice' => esc_html__("Mail sent to ", "not_woo_customers" ) . esc_html( implode( ", "  , $emailsChecked ) ) , 'type' => 'success', 'dismissible' => 'is-dismissible'  ) );
										}
										
									} else update_option( sanitize_text_field( $this->plugin )."_notice", array( 'notice' => esc_html( $email ). esc_html__("is not a valid email address.", "not_woo_customers" ), 'type' => 'error', 'dismissible' => 'is-dismissible'  ) );
								}	
							} else update_option( sanitize_text_field( $this->plugin )."_notice", array( 'notice' => esc_html( $email ). esc_html__("You need to fill a subject & content to send an email", "not_woo_customers" ), 'type' => 'error', 'dismissible' => 'is-dismissible'  ) );
							
						} else update_option( sanitize_text_field( $this->plugin )."_notice", array( 'notice' => esc_html( $email ). esc_html__("You need to select some emails to send an email", "not_woo_customers" ), 'type' => 'error', 'dismissible' => 'is-dismissible'  ) );						 
					}
					
				}	
			
			}
		
		}	

	}

	// check more if you like!
	
	public function extensions(){
		
		if( is_admin() && current_user_can( 'administrator' ) && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'not_woo_customers_extensions' ){
			
			$response = wp_remote_get( $this->extensions );
			
			if( is_wp_error( $response ) ) {
				return;
			}	
			
			$posts = json_decode( wp_remote_retrieve_body( $response ) );

			if( empty( $posts ) ) {
				return;
			}

			if( !empty( $posts ) ) {
				echo "<div id='".esc_attr( $this->plugin )."_extensions_popup'>";
					echo "<div class='".esc_attr( $this->plugin )."_extensions_content'>";	
						?>
						<span class="<?php print esc_attr( $this->plugin ); ?>close">&times;</span>
						<h2><i><?php esc_html_e( 'Extend your WordPress functionality with Extend-WP.com well crafted Premium Plugins!','not_woo_customers' ); ?></i></h2>
						<hr/>
						<?php
						foreach( $posts as $post ) {
							
							echo "<div class='ex_columns'><a target='_blank' href='".esc_url( $post->url )."' /><img src='".esc_url( $post->image )."' /></a>
							<h3><a target='_blank' href='".esc_url( $post->url )."' />". esc_html( $post->title ) . "</a></h3>
							<div>". wp_kses( $post->excerpt, $this->allowed_html )."</div>
							<a class='button_extensions button-primary' target='_blank' href='".esc_url( $post->url )."' />". esc_html__( 'Get it here', 'CrmErpSolution' ) . " <i class='fa fa-angle-double-right'></i></a>
							</div>";
						}
					echo '</div>';
				echo '</div>';	
			}
			wp_die();
		}
	}

	// give us a rating!
	
	public function rating(){
	?>
		<div class="notices notice-success rating is-dismissible">

			<?php esc_html_e( "You like this plugin? ", 'CrmErpSolution' ); ?><i class='fa fa-smile-o' ></i> <?php esc_html_e( "Then please give us ", 'CrmErpSolution' ); ?>
				<a target='_blank' href='https://wordpress.org/support/plugin/<?php print esc_attr( 'notify-email-customers-product-update' ); ?>/reviews/#new-post'>
					<i class='fa fa-star' ></i><i class='fa fa-star' ></i><i class='fa fa-star' ></i><i class='fa fa-star' ></i><i class='fa fa-star' ></i>
				</a>

		</div> 	
	<?php	
	}
	
	
	// initiate settings page
	
	public function init(){
		
		print "<div class='". esc_attr( $this->plugin )."'</div>";
		print "<h1>". esc_html( $this->name )."</h1>";
		
		$this->settings();
		
		print "<hr/>"; ?>
		
		<div class='<?php print esc_attr( $this->plugin ).'_footer'; ?> '>
			<a target='_blank' class=''  style='text-align:center;margin:0 auto' href='https://extend-wp.com'>
				<img  style='text-align:center;width:100px;' src='<?php echo esc_url( plugins_url( 'images/extendwp.png', __FILE__ ) ); ?>' alt='<?php esc_html__( "Get more plugins by extendWP", "not_woo_customers" ) ; ?>' title='<?php esc_html__( "Get more plugins by extendWP", "not_woo_customers" ) ; ?>' />
			</a><br/>
			<a target='_blank' class='wp_extensions'  style='text-align:center;margin:0 auto' href='https://extend-wp.com'>
				<b>
					<span class="dashicons dashicons-admin-plugins"></span> <?php esc_html_e( "Get more plugins by extendWP", "not_woo_customers" ); ?>
				</b>
			</a>
			
		</div>
		<div class='get_ajax'></div>
		<?php
		$this->rating();		
		print "</div>"; 
		
	}

}

$notifywoocusts = new NotifyWooCustomers();		