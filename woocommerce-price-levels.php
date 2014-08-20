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
				global $product;
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
				add_filter('woocommerce_get_price_html',  array( &$this, 'return_variation_price_html' ), $product, 2);
			}
		
			/**
			 * Take care of anything that needs woocommerce to be loaded.  
			 * For instance, if you need access to the $woocommerce global
			 */
			public function woocommerce_loaded() {
				add_action( 'woocommerce_product_options_pricing', array( &$this,  'price_for_roles' ));
				add_action( 'save_post', array( &$this,  'price_for_roles_save' ) );
				add_action( 'woocommerce_product_after_variable_attributes',array( &$this,  'price_for_roles_varibles' ), 10, 2  );
				add_action( 'woocommerce_product_after_variable_attributes_js', array( &$this,  'price_for_roles_varibles' ) );
				add_action( 'woocommerce_process_product_meta_variable', array( &$this,  'price_for_roles_varibles_save' ), 10, 1 );
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
				woocommerce_wp_text_input( array( 'id' => 'wc_pl_cost', 'class' => 'wc_input_price short', 'label' => __('Cost Price',$this->textdomain) . ' (' . get_woocommerce_currency_symbol() . ')' ) );
				woocommerce_wp_text_input( array( 'id' => 'wc_pl_msrp', 'class' => 'wc_input_price short', 'label' => __('MSRP',$this->textdomain) . ' (' . get_woocommerce_currency_symbol() . ')' ) );
				global $wp_roles;
				$all_roles = $wp_roles->roles;
				foreach($all_roles as $key=>$role){
					if (isset($role['priceon']) && $role['priceon']==1){
						if (isset($role['price_type']) && $role['price_type']=='c' && empty($role['priceover'])){
							echo '<p class="form-field another_test_price_field "><label for="another_test_price">'.$role['name'].' '.__('Price',$this->textdomain) . ' (' . get_woocommerce_currency_symbol() . ')'.'</label>Calculated </p>';
						}
						else{woocommerce_wp_text_input( array( 'id' => $key.'_price', 'class' => 'wc_input_price short', 'label' => $role['name'].' '.__('Price',$this->textdomain) . ' (' . get_woocommerce_currency_symbol() . ')' ) );
						}
					}
				}
			}
			
			
			function price_for_roles_save($product_id ) {
				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return;
				global $wp_roles;
				$all_roles = $wp_roles->roles;
				foreach($all_roles as $key=>$role){
					if (isset($role['price_type']) && $role['price_type']=='c' && empty($role['priceover'])){
						continue;
					}
					if ( isset( $_POST[$key.'_price'] ) ) { 
						if ( is_numeric( $_POST[$key.'_price'] ) )  
						update_post_meta( $product_id,$key.'_price', $_POST[$key.'_price'] );
						elseif(empty( $_POST[$key.'_price'] )) delete_post_meta( $product_id, $key.'_price' );
					} else delete_post_meta( $product_id, $key.'_price' );
				}
				if ( isset( $_POST['wc_pl_cost'] ) ) { 
						if ( is_numeric( $_POST['wc_pl_cost'] ) )  
						update_post_meta( $product_id,'wc_pl_cost', $_POST['wc_pl_cost'] );
						elseif(empty( $_POST['wc_pl_cost'] )) delete_post_meta( $product_id, 'wc_pl_cost' );
				} else delete_post_meta( $product_id, 'wc_pl_cost');
				if ( isset( $_POST['wc_pl_msrp'] ) ) { 
						if ( is_numeric( $_POST['wc_pl_msrp'] ) )  
						update_post_meta( $product_id,'wc_pl_msrp', $_POST['wc_pl_msrp'] );
						elseif(empty( $_POST['wc_pl_msrp'] )) delete_post_meta( $product_id, 'wc_pl_msrp' );
				} else delete_post_meta( $product_id, 'wc_pl_msrp');
			}
			
			
			public function return_custom_price_level ($price, $product) {
				global $post; global $wp_roles;
				$user_id = get_current_user_id();
				$user = new WP_User( $user_id );
				$role = $user->roles[0];
				if(!empty($product->variation_id))
					$post_id = $product->variation_id;
				else
					$post_id = $product->id;
				$all_roles = $wp_roles->roles;
				if($role && isset($all_roles[$role]['priceon']) && $all_roles[$role]['priceon']==1){
					$check_price = get_post_meta($post_id,$role.'_price' , true);
					if($all_roles[$role]['price_type']=='c' &&  (empty($all_roles[$role]['priceover']) || ($all_roles[$role]['priceover']==1 && is_numeric($check_price)==false))){ 
						$percent=(float)$all_roles[$role]['price_percent'];
						switch($all_roles[$role]['price_type2']){
							case 1: //Regular Price 
								$regular = get_post_meta( $post_id, '_regular_price', true);
								if(is_numeric($regular)){
									if($all_roles[$role]['price_sign']=='+'){
											$new_price=$regular+($regular*$all_roles[$role]['price_percent']/100);
											if(is_numeric($new_price)) return $new_price;
									}elseif($all_roles[$role]['price_sign']=='-'){
											$new_price=$regular-($regular*$all_roles[$role]['price_percent']/100);
											if(is_numeric($new_price)) return $new_price;
									}
								}
								break;
							case 2: //Cost
								$cost_price=get_post_meta($post_id,'wc_pl_cost', true);
								if(is_numeric($cost_price)){
									if($all_roles[$role]['price_sign']=='+'){
											$new_price=$cost_price+($cost_price*$percent/100);
											if(is_numeric($new_price)) return $new_price;
									}elseif($all_roles[$role]['price_sign']=='-'){
											$new_price=$cost_price-($cost_price*$percent/100);
											if(is_numeric($new_price)) return $new_price;
									}
								}
							break;
							case 3: //MSRP
								$msrp=get_post_meta($post_id,'wc_pl_msrp', true);
								if(is_numeric($msrp)){
									if($all_roles[$role]['price_sign']=='+'){
											$new_price=$msrp+($msrp*$all_roles[$role]['price_percent']/100);
											if(is_numeric($new_price)) return $new_price;
									}elseif($all_roles[$role]['price_sign']=='-'){
											$new_price=$msrp-($msrp*$all_roles[$role]['price_percent']/100);
											if(is_numeric($new_price)) return $new_price;
									}
								}
							break;
							default: 
								$price_role_ = explode('pricerole_',$all_roles[$role]['price_type2']);
								if(isset($price_role_[1])){
									$price_role=$price_role_[1];}
								else {  break;}
								
								if(!empty($price_role)){
									$role_price = get_post_meta($post_id,$price_role.'_price' , true);
									if(is_numeric($role_price)){
										if($all_roles[$role]['price_sign']=='+'){
											$new_price=$role_price+($role_price*$percent/100);
											if(is_numeric($new_price)) return $new_price;
										}elseif($all_roles[$role]['price_sign']=='-'){
											$new_price=$role_price-($role_price*$percent/100);
											if(is_numeric($new_price)) return $new_price;
										}
									}
								}
							break;
						}
					}
					
					if($all_roles[$role]['price_type']=='u' || (isset($all_roles[$role]['priceover']) && $all_roles[$role]['priceover']==1)){
						$new_price = get_post_meta($post_id,$role.'_price' , true);
						if(is_numeric($new_price)) return $new_price;
					}
					
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
					if($_POST['priceon']!=1){ 
						global $wp_roles;
						$all_roles = $wp_roles->roles;
						foreach($all_roles as $key=>$role){
							if($role['price_type2']=='pricerole_'.$role_key && $role['price_type']=='c' && $role['priceon']==1){
								wp_die(__("Can't disable Price Levels: ",$this->textdomain).$role['name'].__(" role still uses this role to calculate price.",$this->textdomain).'<br /><a href="javascript:history.back(1);">'.__("<< Back",$this->textdomain).'</a>');
							}
						}
					}
					$val[$role_key]['price_type'] = $_POST['price_type'];
					if($_POST['price_type']=='c'){
						$val[$role_key]['price_type2'] = $_POST['price_type2'];
						$val[$role_key]['price_sign'] = $_POST['price_sign'];
						$val[$role_key]['price_percent'] = $_POST['price_percent'];
						$val[$role_key]['priceover'] = $_POST['priceover'];
					}
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
				if(isset($all_roles[$role_key]['priceover']) && $all_roles[$role_key]['priceover']==1){
					$priceover='checked="checked"';
				}else{
					$priceover='';
				}
				if(isset($all_roles[$role_key]['price_type']) && $all_roles[$role_key]['price_type']=='c'){
					$cal_price='selected="selected"';
					$un_price='';
				}else{
					$un_price='selected="selected"';
					$cal_price='';
				}
				//echo '<pre>'; print_R($all_roles[$role_key]); echo '</pre>';
				include( untrailingslashit( plugin_dir_path( __FILE__ ). '/templates/edit_role.php'));
				
			}
			 
			 
			 	public function deleterole_admin_action() {
					global $wp_roles;
					if ( isset($_GET['role'])){
						$role_key=$_GET['role'];
						$role=get_role( $role_key );
						if(isset($role->capabilities['woo_role']) && $role->capabilities['woo_role']==1){
							$all_roles = $wp_roles->roles;
							foreach($all_roles as $key=>$role){
								if($role['price_type2']=='pricerole_'.$role_key && $role['price_type']=='c' && $role['priceon']==1){
									wp_die(__("Can't delete: ",$this->textdomain).$role['name'].__(" role still uses this role to calculate price.",$this->textdomain).'<br /><a href="javascript:history.back(1);">'.__("<< Back",$this->textdomain).'</a>');
								}
							}
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
			
				function price_for_roles_varibles( $loop, $variation_data ) {
					global $wp_roles;
					$all_roles = $wp_roles->roles;
					?>
					<tr>
					<td><label><?php  _e('Cost Price',$this->textdomain) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label></td>
					<td><input type="text" size="5" name="wc_pl_cost[<?php echo $loop; ?>]" value="<?php echo isset($variation_data['wc_pl_cost'][0]) ? $variation_data['wc_pl_cost'][0] : ''; ?>"/>
					</td></tr>
					<tr><td>
					<label><?php  _e('MSRP',$this->textdomain) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label></td>
					<td><input type="text" size="5" name="wc_pl_msrp[<?php echo $loop; ?>]" value="<?php echo isset($variation_data['wc_pl_msrp'][0]) ? $variation_data['wc_pl_msrp'][0] : ''; ?>"/>
					</td></tr>
					<?php  
					foreach($all_roles as $key=>$role){
						if (isset($role['priceon']) && $role['priceon']==1){
							if (isset($role['price_type']) && $role['price_type']=='c' && empty($role['priceover'])){
								echo '<tr><td><label for="another_test_price">'.$role['name'].' '.__('Price',$this->textdomain) . ' (' . get_woocommerce_currency_symbol() . ')'.'</label></td><td>Calculated 	</td></tr>';
							}
							else{ ?>
							<tr><td><label><?php  echo $role['name'].' '.__('Price',$this->textdomain) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
							</td><td><input type="text" size="5" name="<?php echo $key; ?>_price[<?php echo $loop; ?>]" value="<?php echo isset($variation_data[ $key.'_price'][0]) ? $variation_data[ $key.'_price'][0] : ''; ?>"/>
							</td></tr>
							<?php }
						}
					} ?>
					
					</td>
					</tr>
					<?php
				}
			
				
				function price_for_roles_varibles_save( $post_id ) {
					global $wp_roles;
					$all_roles = $wp_roles->roles;
					if (isset( $_POST['variable_sku'] ) ) : 
						$variable_sku = $_POST['variable_sku'];
						$variable_post_id = $_POST['variable_post_id'];
						for ( $i = 0; $i < sizeof( $variable_sku ); $i++ ) :
							$variation_id = (int) $variable_post_id[$i];
							foreach($all_roles as $key=>$role){
								if ((isset($role['price_type']) && $role['price_type']=='c' && empty($role['priceover'])) || empty($role['priceon'])){
									continue;
								}
								$variable_role_price = $_POST[$key.'_price'];
								if ( isset( $variable_role_price[$i] ) ) { 
									if ( is_numeric( $variable_role_price[$i] ) )  
										update_post_meta( $variation_id,$key.'_price',   $variable_role_price[$i] );
									elseif(empty( $variable_role_price[$i] )) 
										delete_post_meta( $variation_id, $key.'_price' );
								} 
								else 
									delete_post_meta( $variation_id, $key.'_price' );
							}
							$variable_wc_pl_cost = $_POST['wc_pl_cost'][$i];
							echo $variable_wc_pl_cost.' ';
							if ( isset( $variable_wc_pl_cost ) ) { 
									if ( is_numeric( $variable_wc_pl_cost ) )  
									update_post_meta( $variation_id,'wc_pl_cost', $variable_wc_pl_cost );
									elseif(empty( $variable_wc_pl_cost )) delete_post_meta( $variation_id, 'wc_pl_cost' );
							} else 
								delete_post_meta( $variation_id, 'wc_pl_cost');
							$variable_wc_pl_msrp = $_POST['wc_pl_msrp'][$i];
							if ( isset( $variable_wc_pl_msrp ) ) { 
								if ( is_numeric($variable_wc_pl_msrp ) )  
									update_post_meta( $variation_id,'wc_pl_msrp', $variable_wc_pl_msrp );
								elseif(empty( $variable_wc_pl_msrp )) 
									delete_post_meta( $variation_id, 'wc_pl_msrp' );
							} else 
								delete_post_meta( $variation_id, 'wc_pl_msrp');	
						endfor;
					endif;
				}
				
				public function return_variation_price_html($price,$product){ 
					 if($product->is_type( 'variable' )){
						foreach($product->children as $postid){
							if($this->checkIfCustomPrice($postid)){
								$price = preg_replace('#(<del.*?>).*?(</del>)#', '$1$2', $price); //hide crossed price for not to be confused with sale price
								break;
							}
						}
					}

					return $price;
				}
				
				public function checkIfCustomPrice($post_id){ //check if for this product we have custom price
					global $post; global $wp_roles;
					$user_id = get_current_user_id();
					$user = new WP_User( $user_id );
					$role = $user->roles[0];
					$all_roles = $wp_roles->roles;
					if($role && isset($all_roles[$role]['priceon']) && $all_roles[$role]['priceon']==1){
						$check_price = get_post_meta($post_id,$role.'_price' , true);
						if($all_roles[$role]['price_type']=='c' &&  (empty($all_roles[$role]['priceover']) || ($all_roles[$role]['priceover']==1 && is_numeric($check_price)==false))){ 
							switch($all_roles[$role]['price_type2']){
								case 1: //Regular Price 
									$regular = get_post_meta( $post_id, '_regular_price', true);
									if(!empty($regular)){ return true; }else{return false;}
									break;
								case 2: //Cost
									$cost_price=get_post_meta($post_id,'wc_pl_cost', true);
									if(!empty($cost_price)){return true;}else{return false;}
									break;
								case 3: //MSRP
									$msrp=get_post_meta($post_id,'wc_pl_msrp', true);
									if(!empty($msrp)){return true;}else{return false;}
									break;
								default: 
									$price_role_ = explode('pricerole_',$all_roles[$role]['price_type2']);
									if(isset($price_role_[1])){
										$price_role=$price_role_[1];}
									else { return false;}
									
									if(!empty($price_role)){
										$role_price = get_post_meta($post_id,$price_role.'_price' , true);
										if(!empty($role_price)){return true;}else{return false;}
									}else{return false;}
								break;
							}
						}
						
						if($all_roles[$role]['price_type']=='u' || (isset($all_roles[$role]['priceover']) && $all_roles[$role]['priceover']==1)){
							$new_price = get_post_meta($post_id,$role.'_price' , true);
							if(!empty($new_price)){return true;}else{return false;}
						}
					}
					return false;
				}
		}
		
		
		
		// instantiate our plugin class and add it to the set of globals
		$GLOBALS['wc_pricelevels'] = new WC_PriceLevels();
		
	}
