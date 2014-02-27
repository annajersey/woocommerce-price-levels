<?php

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}





class WC_PriceLevels_AdminRolesTable extends WP_List_Table {
    
    public $textdomain = 'wc_pricelevels';
    function __construct(){
        global $status, $page;
                 
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'role',     //singular name of the listed records
            'plural'    => 'roles',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
		
        
    }


    function column_default($item, $column_name){
        switch($column_name){
            case 'number':
            case 'actions':
			case 'priceon':
			case 'key':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }


    function column_title($item){
        return sprintf('%1$s',
            /*$1%s*/ $item['title']
            
        );
    }


    
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }


    
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'title'     => __('Role Display Name',$this->textdomain),
			'key'		=>	__('Role Key',$this->textdomain),
            'number'    => __('Count of Users',$this->textdomain),
			'priceon'    => __('Enable Price Levels',$this->textdomain),
			'actions' => __('Actions',$this->textdomain)
           
        );
        return $columns;
    }


   
    function get_sortable_columns() {
        $sortable_columns = array(
            'title'     => array('title',false),     //true means it's already sorted
            'number'    => array('number',false),
			'priceon'    => array('priceon',false),
			'key'    => array('key',false)
			//'actions'    => array('actions',false)
        );
        return $sortable_columns;
    }


    
    function get_bulk_actions() {
        $actions = array(
            'delete'    => __('Delete',$this->textdomain),
		
			
        );
		
        return $actions;
    }


    
    function process_bulk_action() {
         global $wp_roles;
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
			foreach($_REQUEST['role'] as $role_key){
				$role=get_role( $role_key );
				if(isset($role->capabilities['woo_role']) && $role->capabilities['woo_role']==1){ 
					$wp_user_search = new WP_User_Query($usersearch, $userspage, $role_key );
					$editors = $wp_user_search->get_results();
					$user_num = sizeof($editors);
					if($user_num>0){ wp_die($role->name.' '.__("role still has",$this->textdomain).' '.$user_num.' '.__("customers / users assigned to it. You must remove all users from a role before it can be deleted.",$this->textdomain).'<br /><a href="javascript:history.back(1);">'.__("<< Back",$this->textdomain).'</a>');
					
					}else{
					$all_roles = $wp_roles->roles;
					foreach($all_roles as $key=>$role1){
						if($role1['price_type2']=='pricerole_'.$role_key && $role1['price_type']=='c' && $role1['priceon']==1){
							wp_die(__("Can't delete: ",$this->textdomain).$role1['name'].__(" role still uses this role to calculate price.",$this->textdomain).'<br /><a href="javascript:history.back(1);">'.__("<< Back",$this->textdomain).'</a>');
						}
					} 
					$wp_roles->remove_role($role_key);}
				}else{
					wp_die(__("Cannot delete roles that were not added by this plugin.",$this->textdomain).'<br /><a href="javascript:history.back(1);">'.__("<< Back",$this->textdomain).'</a>');
				}
			}
			@wp_redirect( get_bloginfo('url').'/wp-admin/admin.php?page=customer-levels' );
        }
        
    }


    
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 10;
        
        
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
       
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
       
        $this->process_bulk_action();
        
        
        
		 global $wp_roles;
		
		 
		
		$i=0;
		$all_roles = $wp_roles->roles;
		$result = count_users();
		$table_data=array();
		global $usersearch; global $userspage;
		foreach($all_roles as $key=>$role){
			$table_data[$i]['ID']=$key;
			$table_data[$i]['title']= $role['name'];
			$table_data[$i]['key']= $key;
			$wp_user_search = new WP_User_Query($usersearch, $userspage, $key);
			$editors = $wp_user_search->get_results();
			$table_data[$i]['number']= sizeof($editors);
			$edit = sprintf('<a href="?page=%s&role=%s">'.__("Edit",$this->textdomain).'</a>','edit_role',$key);
			if(isset($role['capabilities']['woo_role']) && $role['capabilities']['woo_role']==1){
				$delete = sprintf('&nbsp;<a class="delete" href="?action=%s&role=%s">'.__("Delete",$this->textdomain).'</a>','deleterole',$key);
				$table_data[$i]['actions']=$edit.' '.$delete;
			}else{
				$table_data[$i]['actions']=$edit;
			}
			$table_data[$i]['priceon']= (isset($role['priceon']) && $role['priceon']==1) ? __("Yes",$this->textdomain) : __("No",$this->textdomain) ;
			$i++;
		 }
		 
        $data = $table_data;
                
         
        
		
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        
        
                
        
        $current_page = $this->get_pagenum();
        
       
        $total_items = count($data);
        
        
        
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        
        $this->items = $data;
        
        
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}

