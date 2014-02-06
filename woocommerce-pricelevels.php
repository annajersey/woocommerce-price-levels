<?php
/*
Plugin Name: WooCommerce Price Levels
Description: WooCommerce plugin for products can be offered at different prices for different customer roles.
Version: 1.0.0

	
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/**
 * Check if WooCommerce is active
 */
//if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	if ( ! class_exists( 'WC_PriceLevels' ) ) {
		
		/**
		 * Localisation
		 **/
		
	
		class WC_PriceLevels {
			public $textdomain = 'WC_PriceLevels';
			public function __construct() {
				$textdomain = 'WC_PriceLevels';
				include_once( 'classes/admin-role-table.php' );
				// called only after woocommerce has finished loading
				add_action( 'woocommerce_init', array( &$this, 'woocommerce_loaded' ) );
				
				// called after all plugins have loaded
				add_action( 'plugins_loaded', array( &$this, 'plugins_loaded' ) );
				
				// called just before the woocommerce template functions are included
				add_action( 'init', array( &$this, 'include_template_functions' ), 20 );
				
				// indicates we are running the admin
				if ( is_admin() ) {
					// ...
				}
				
				register_activation_hook(__FILE__, array( 'WC_PriceLevels', 'plugin_activate' ));
				add_action('admin_menu',array( 'WC_PriceLevels', 'register_my_custom_submenu' ) ,99);
				add_filter('woocommerce_get_price',  array( 'WC_PriceLevels', 'return_custom_price_level' ), $product, 2);
				// take care of anything else that needs to be done immediately upon plugin instantiation, here in the constructor
			}
		
			/**
			 * Take care of anything that needs woocommerce to be loaded.  
			 * For instance, if you need access to the $woocommerce global
			 */
			public function woocommerce_loaded() {
				add_action( 'woocommerce_product_options_pricing', array( 'WC_PriceLevels',  'price_for_roles' ));
				add_action( 'save_post', array( 'WC_PriceLevels',  'price_for_roles_save' ) );
			}
			
			/**
			 * Take care of anything that needs all plugins to be loaded
			 */
			public function plugins_loaded() {
				load_plugin_textdomain( 'wc_pricelevels', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			}
			
			/**
			 * Override any of the template functions from woocommerce/woocommerce-template.php 
			 * with our own template functions file
			 */
			public function include_template_functions() {
				//include( 'woocommerce-template.php' );
			}
			
			
			public  function plugin_activate() {
			 if(!in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )){
				deactivate_plugins(basename(__FILE__)); // Deactivate ourself
                wp_die(__("WooCommerce Price Levels requires WooCommerce installed and activated.",'wc_pricelevels')); 
			  }
			}
			public  function register_my_custom_submenu() {
				add_submenu_page( 'woocommerce', __('Customer Levels','wc_pricelevels'), __('Customer Levels','wc_pricelevels'), 'manage_options', 'customer-levels', array( 'WC_PriceLevels','customer_levels_page_callback' )   ); 
				add_submenu_page( null, 'Add New Customer Role',  'Add New Customer Role', 'manage_options', 'new_roles', array( 'WC_PriceLevels','new_roles_page_callback' )   );
				add_submenu_page( null, 'Delete Customer Role',  'Delete Customer Role', 'manage_options', 'delete_role', array( 'WC_PriceLevels','delete_role_page_callback' )   ); 
				add_submenu_page( null, 'Edit Customer Role',  'Edit Customer Role', 'manage_options', 'edit_role', array( 'WC_PriceLevels','edit_role_page_callback' )   ); 			
			}
			function price_for_roles() {
				global $wp_roles;
				$all_roles = $wp_roles->roles;
				foreach($all_roles as $key=>$role){
					woocommerce_wp_text_input( array( 'id' => $key.'_price', 'class' => 'wc_input_price short', 'label' => $role['name'].' '.__(' 	Price','wc_pricelevels' ) . ' (' . get_woocommerce_currency_symbol() . ')' ) );
				}
			}
			function price_for_roles_save($product_id ) {
				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return;
				global $wp_roles;
				$all_roles = $wp_roles->roles;
				foreach($all_roles as $key=>$role){
					if ( isset( $_POST[$key.'_price'] ) ) { 
						if ( is_numeric( $_POST[$key.'_price'] ) )  
						update_post_meta( $product_id,$key.'_price', $_POST[$key.'_price'] );
						elseif(empty( $_POST[$key.'_price'] )) delete_post_meta( $product_id, $key.'_price' );
					} else delete_post_meta( $product_id, $key.'_price' );
				}	
			}
			public function return_custom_price_level ($price, $product) {
				 global $post;
				$user_id = get_current_user_id();
				$user = new WP_User( $user_id );
				$role = $user->roles[0];
				//echo($role);
				$post_id = $product->id;
				//echo $post_id;
				if($role){
					$new_price = get_post_meta($post_id,$role.'_price' , true);
					if(is_numeric($new_price)) return $new_price;
				}
				
				return $price;
			}
			public function customer_levels_page_callback() {
				global $wp_roles;
				$all_roles = $wp_roles->roles;
				$editable_roles = apply_filters('editable_roles', $all_roles);
				
				$testListTable = new WC_PriceLevels_AdminRolesTable();
				//Fetch, prepare, sort, and filter our data...
				$testListTable->prepare_items();
				include( untrailingslashit( plugin_dir_path( __FILE__ ). '/templates/levels_template.php'));
			 }
			public function new_roles_page_callback() {
				if ( $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['role_name'])){
					$role_key=strtolower(str_replace(' ','_',$_POST['role_name']));
					add_role($role_key, $_POST['role_name'], array(
						'read' => true, 
						'edit_posts' => false,
						'delete_posts' => false, 
						'woo_role' => 1
					));
					wp_redirect( get_bloginfo('url').'/wp-admin/admin.php?page=customer-levels' );
				} else {
				global $wp_roles;
				$all_roles = $wp_roles->roles;
				//echo '<pre>'; print_r($all_roles); echo '</pre>';
				echo '
				<h2>'.__("Add New Role",'wc_pricelevels').'</h2>
				<form method="post" action="'.$_SERVER['REQUEST_URI'].'">
					<div id="titlewrap">
					<label for="title" id="title-prompt-text" class="">'.__("Role Name",'wc_pricelevels').'</label>
					<input type="text" autocomplete="off" id="title" value="" size="30" name="role_name">
					</div><br />
					<input id="publish" class="button button-primary button-large" type="submit" accesskey="p" value="'.__("Save","wc_pricelevels").'" name="publish">
				</form>';
				}
				
			 }	
			 public function edit_role_page_callback() {
				global $wp_roles;
				$all_roles = $wp_roles->roles;
				
				if ( $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['role_name']) && isset($_POST['role_key'])){
					$role_key=$_POST['role_key'];
					$role=get_role( $role_key );
					if(isset($role->capabilities['woo_role']) && $role->capabilities['woo_role']==1){
						$val = get_option( 'wp_user_roles' );
						$val[$role_key]['name'] = $_POST['role_name'];
						update_option( 'wp_user_roles', $val );  
					}
					wp_redirect( get_bloginfo('url').'/wp-admin/admin.php?page=customer-levels' );
				} else {
				
				$role_key=$_GET['role'];
				
				
				echo '
				<h2>'.__("Edit Role",'wc_pricelevels').'</h2>
				<form method="post" action="'.$_SERVER['REQUEST_URI'].'">
					<div id="titlewrap">
					<label for="title" id="title-prompt-text" class="">'.__("Role Name",'wc_pricelevels').'</label>
					<input type="text" autocomplete="off" id="title" value="'.$all_roles[$role_key]['name'].'" size="30" name="role_name">
					<input type="hidden" name="role_key" value="'.$role_key.'">
					</div><br />
					<input id="publish" class="button button-primary button-large" type="submit" accesskey="p" value="'.__("Save","wc_pricelevels").'" name="publish">
				</form>';
				}
				
			 }
			 	public function delete_role_page_callback() {
					global $wp_roles;
				
					if ( isset($_GET['role'])){
						$role_key=$_GET['role'];
						$role=get_role( $role_key );
						if(isset($role->capabilities['woo_role']) && $role->capabilities['woo_role']==1){
							$wp_user_search = new WP_User_Search($usersearch, $userspage, $role_key );
							$editors = $wp_user_search->get_results();
							$user_num = sizeof($editors);
							if($user_num>0){ wp_die(__("This role still has",'wc_pricelevels').' '.$user_num.' '.__("customers / users assigned to it. You must remove all users from a role before it can be deleted.",'wc_pricelevels')); }else{
							$wp_roles->remove_role($role_key);}
						}
					wp_redirect( get_bloginfo('url').'/wp-admin/admin.php?page=customer-levels' );
					}
				}
			
		}
		
		
		
		// instantiate our plugin class and add it to the set of globals
		$GLOBALS['wc_pricelevels'] = new WC_PriceLevels();
		
	}
//}