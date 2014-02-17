<?php
/*
Plugin Name: WooCommerce Price Levels
Description: WooCommerce plugin to offer different prices per customer roles.
Version: 1.0

*/

	
	if ( ! class_exists( 'WC_PriceLevels' ) ) {
		
		class WC_PriceLevels {
			
			public $textdomain = 'wc_pricelevels';
			public function __construct() {
				include_once( 'classes/admin-role-table.php' );
				// called only after woocommerce has finished loading
				add_action( 'woocommerce_init', array( &$this, 'woocommerce_loaded' ) );
				
				// called after all plugins have loaded
				add_action( 'plugins_loaded', array( &$this, 'plugins_loaded' ) );
				
				register_activation_hook(__FILE__, array( &$this, 'plugin_activate' ));
				add_action('admin_menu',array( &$this, 'register_my_custom_submenu' ) ,99);
				add_filter('woocommerce_get_price',  array( &$this, 'return_custom_price_level' ), $product, 2);
				add_action( 'admin_action_deleterole', array( &$this, 'deleterole_admin_action' ) );
				add_action( 'admin_action_addrole', array( &$this, 'addrole_admin_action' ) );
				add_action( 'admin_action_editrole', array( &$this, 'editrole_admin_action' ) );
			}
		
			/**
			 * Take care of anything that needs woocommerce to be loaded.  
			 * For instance, if you need access to the $woocommerce global
			 */
			public function woocommerce_loaded() {
				add_action( 'woocommerce_product_options_pricing', array( &$this,  'price_for_roles' ));
				add_action( 'save_post', array( &$this,  'price_for_roles_save' ) );
			}
			
			/**
			 * Take care of anything that needs all plugins to be loaded
			 */
			public function plugins_loaded() {
				load_plugin_textdomain( 'wc_pricelevels', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			}
			
			
			
			public  function plugin_activate() {
				if(!in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )){
				deactivate_plugins(basename(__FILE__)); // Deactivate ourself
                wp_die(__("WooCommerce Price Levels requires WooCommerce installed and activated.",$this->textdomain).'<br /><a href="javascript:history.back(1);">'.__("<< Back",$this->textdomain).'</a>'); 
			  }
			}
			
			
			public  function register_my_custom_submenu() {
				add_submenu_page( 'woocommerce', __('Customer Levels',$this->textdomain), __('Customer Levels',$this->textdomain), 'manage_options', 'customer-levels', array( &$this,'customer_levels_page_callback' )   ); 
				add_submenu_page( null, __('Add New Customer Role',$this->textdomain),  __('Add New Customer Role',$this->textdomain), 'manage_options', 'new_roles', array( &$this,'new_roles_page_callback' )   );
				add_submenu_page( null, __('Edit Customer Role',$this->textdomain),  __('Edit Customer Role',$this->textdomain), 'manage_options', 'edit_role', array( &$this,'edit_role_page_callback' )   ); 			
			}
			
			
			function price_for_roles() {
				global $wp_roles;
				$all_roles = $wp_roles->roles;
				foreach($all_roles as $key=>$role){
					if (isset($role['priceon']) && $role['priceon']==1){
						woocommerce_wp_text_input( array( 'id' => $key.'_price', 'class' => 'wc_input_price short', 'label' => $role['name'].' '.__('Price',$this->textdomain) . ' (' . get_woocommerce_currency_symbol() . ')' ) );
					}
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
				 global $post; global $wp_roles;
				$user_id = get_current_user_id();
				$user = new WP_User( $user_id );
				$role = $user->roles[0];
				//echo($role);
				$post_id = $product->id;
				//echo $post_id;
				$all_roles = $wp_roles->roles;
				if($role && isset($all_roles[$role]['priceon']) && $all_roles[$role]['priceon']==1){
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
			 
			 public function addrole_admin_action(){
				if ( $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['role_name'])){
					$role_key=strtolower(str_replace(' ','_',$_POST['role_name']));
					add_role($role_key, $_POST['role_name'], array(
						'read' => true, 
						'edit_posts' => false,
						'delete_posts' => false, 
						'woo_role' => 1
					));
					wp_redirect( get_bloginfo('url').'/wp-admin/admin.php?page=customer-levels' );
				}
			 }
			public function new_roles_page_callback() {
				
				global $wp_roles;
				$all_roles = $wp_roles->roles;
				//echo '<pre>'; print_r($all_roles); echo '</pre>';
				echo '
				<h2>'.__("Add New Role",$this->textdomain).'</h2>
				<form method="post" action="'.admin_url( 'admin.php' ).'">
					<div id="titlewrap">
					<label for="title" id="title-prompt-text" class="">'.__("Role Name",$this->textdomain).'</label>
					<input type="text" autocomplete="off" id="title" value="" size="30" name="role_name">
					</div><br />
					 <input type="hidden" name="action" value="addrole" />
					<input id="publish" class="button button-primary button-large" type="submit" accesskey="p" value="'.__("Save",$this->textdomain).'" name="publish">
				</form>';
				
				
			 }	
			  public function editrole_admin_action(){
				if ( $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['role_key'])){
					$role_key=$_POST['role_key'];
					$role=get_role( $role_key );
					$val = get_option( 'wp_user_roles' );
					if(isset($role->capabilities['woo_role']) && $role->capabilities['woo_role']==1 && isset($_POST['role_name']) && $_POST['role_name']!=''){
						$val[$role_key]['name'] = $_POST['role_name'];
						update_option( 'wp_user_roles', $val );  
					}
					$val[$role_key]['priceon'] = $_POST['priceon'];
					update_option( 'wp_user_roles', $val ); 
					wp_redirect( get_bloginfo('url').'/wp-admin/admin.php?page=customer-levels' );
				} 
			  }
			 
			 public function edit_role_page_callback() {
				global $wp_roles;
				$all_roles = $wp_roles->roles;
				$role_key=$_GET['role'];
				$role=get_role( $role_key );
				if(isset($role->capabilities['woo_role']) && $role->capabilities['woo_role']==1){
					$disabled='';
				}else{
					$disabled='disabled="disabled"';
				}
				if(isset($all_roles[$role_key]['priceon']) && $all_roles[$role_key]['priceon']==1){
					$priceon='checked="checked"';
				}else{
					$priceon='';
				}
				
				echo '
				<h2>'.__("Edit Role",$this->textdomain).'</h2>
				<form method="post" action="'.admin_url( 'admin.php' ).'">
					<div id="titlewrap">
					<label for="title" id="title-prompt-text" class="">'.__("Role Name",$this->textdomain).'</label>
					<input type="text" '.$disabled.' autocomplete="off" id="title" value="'.$all_roles[$role_key]['name'].'" size="30" name="role_name">
					<br /><br />
					<label for="priceon" id="priceon-prompt-text" class="">'.__("Enable Price Levels",$this->textdomain).'</label>&nbsp;
					<input type="checkbox" id="priceon"  name="priceon" value="1" '.$priceon.'/>
					<input type="hidden" name="role_key" value="'.$role_key.'">
					</div><br />
					 <input type="hidden" name="action" value="editrole" />
					<input id="publish" class="button button-primary button-large" type="submit" accesskey="p" value="'.__("Save",$this->textdomain).'" name="publish">
				</form>';
				
				
			 }
			 
			 
			 	public function deleterole_admin_action() {
					global $wp_roles;
					if ( isset($_GET['role'])){
						$role_key=$_GET['role'];
						$role=get_role( $role_key );
						if(isset($role->capabilities['woo_role']) && $role->capabilities['woo_role']==1){
							$wp_user_search = new WP_User_Search($usersearch, $userspage, $role_key );
							$editors = $wp_user_search->get_results();
							$user_num = sizeof($editors);
							if($user_num>0){ wp_die(__("This role still has",$this->textdomain).' '.$user_num.' '.__("customers / users assigned to it. You must remove all users from a role before it can be deleted.",$this->textdomain).'<br /><a href="javascript:history.back(1);">'.__("<< Back",$this->textdomain).'</a>'); }else{
							$wp_roles->remove_role($role_key);}
						}else{
							wp_die(__("Cannot delete roles that were not added by this plugin.",$this->textdomain).'<br /><a href="javascript:history.back(1);">'.__("<< Back",$this->textdomain).'</a>');
						}
						
					wp_redirect( get_bloginfo('url').'/wp-admin/admin.php?page=customer-levels' );
					}
				}
			
		}
		
		
		
		// instantiate our plugin class and add it to the set of globals
		$GLOBALS['wc_pricelevels'] = new WC_PriceLevels();
		
	}
