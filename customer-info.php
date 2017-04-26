<?php

if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class Customer_Info_List_Table extends WP_List_Table {

	public static function get_customers(){
		$args = array(
	        'role' => 'customer',
	        'orderby' => 'user_nicename',
	        'order' => 'ASC'
	      );
       $users = get_users($args);

       $newdata = array();

       foreach ($users as $each) {

       		$metas =  get_user_meta($each->ID);

       		 // Get all customer orders
		    $customer_orders = get_posts( array(
		        'numberposts' => -1,
		        'meta_key'    => '_customer_user',
		        'meta_value'  => $each->ID,
		        'post_type'   => wc_get_order_types(),
		        'post_status' => array_keys( wc_get_order_statuses() ),
		    ) );

			$order = '';

		    if(!empty($customer_orders)){
			    $i = 1;

			    foreach($customer_orders as $single){

			    	$order .= '#'.$single->ID;

			    	if($i < count($customer_orders)) $order .= ', ';

			    	$i++;
			    }
			}
       		
       		$newdata[] = array(
       				'id' => $each->ID,
   					'username' => $each->data->display_name,
       				'name' 	=> $metas['first_name'][0].' '.$metas['last_name'][0],
       				'company' => $metas['billing_company'][0],
       				'email'	=> $each->data->user_email,
       				'phone'	=> $metas['billing_phone'][0],
       				'order' => $order
       			);
       }

       return $newdata;
	}

	function __construct() {
		parent::__construct( array(
			'singular' => 'customer',
			'plural' => 'customers',
			'ajax' => false ) );
	}

	function prepare_items() {
		$current_screen = get_current_screen();

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$newdata = self::get_customers();
       	$page = $this->get_pagenum();

       	if(!empty($_GET['s'])){
       		foreach ($newdata as $key => $value){
       			$delimit = "/".$_GET['s']."/";
			    if(preg_match($delimit ,$newdata[$key][$_GET['group']]) == false )
			    {
			        unset($newdata[$key]);
			    }
			}
       	}

		$total_items = count( $newdata );//WE have to calculate the total number of items
		$limit = 10;  //WE have to determine how many items to show on a page

		$slicedata = array_slice( $newdata, (($page-1)*$limit), $limit );

		  $current_page = $this->get_pagenum();
		
		  $this->set_pagination_args( [
		    'total_items' => $total_items, 
		    'per_page'    => $limit 
		  ] );

		$this->items = $slicedata;

	}

	function get_columns() {

		$columns = array(
			'name' => __( 'Name', 'customer-info' ),
			'company' => __( 'Company Name', 'customer-info' ),
			'email' => __( 'Email', 'customer-info' ),
			'phone' => __( 'Phone', 'customer-info' ),
			'order' => __( 'Order', 'customer-info' ) ,
		);

		return $columns;
	
	}

	function column_default( $item, $column_name ) {

		$data = $item;

		switch ( $column_name ) {
			case 'name':
				return $data['name'];
			case 'email':
				return $data['email'];
			case 'company':
				return (!empty($data['company'])) ? $data['company'] : '' ;					
			case 'phone':
				return (!empty($data['phone'])) ? $data['phone'] : '';
			case 'order':
				return (!empty($data['order'])) ? $data['order'] : '';
			default:
				return print_r( $column_name, true ); //Show the whole array for troubleshooting purposes
		}
	}

	public function no_items() {
		_e( 'No customer avaliable.', 'grosh' );
	}

	function extra_tablenav( $which ) {
	   if ( $which == "top" ){
	      //The code that goes before the table is here
	      ?>
	      <div class="alignleft actions">
		<form method="get" class="search-form" action="">
			<p class="search-box">
			<label class="screen-reader-text" for="search_id-search-input">search:</label>
			<input type="search" id="search_id-search-input" name="s" <?php if(isset($_GET['s']) && !empty($_GET['s'])) { ?>value="<?php echo $_GET['s']; ?>" <?php } else { ?>value="" <?php } ?>placeholder="Enter keywords &hellip;">
			<select name="group" class="postform">
				<?php foreach($this->get_columns() as $k => $v){ ?>
					<option value="<?php echo $k; ?>" <?php if($_GET['group'] == $k){ echo 'selected'; }?>><?php echo $v; ?></option>
				<?php } ?>
			</select>
			<input type="submit" id="search-submit" class="button" value="search">
			</p>
			<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />	
		</form>
		</div>
	      <?php
	   }
	}
}

class customerInfo {
 
    /**
     * Adds a submenu for this plugin to the 'Tools' menu.
     */
    public function init() {
         add_action( 'admin_menu', array( $this, 'customer_info_page' ) );
    }
 
    /**
     * Creates the submenu item and calls on the Submenu Page object to render
     * the actual contents of the page.
     */
    public function customer_info_page() {
 
        add_menu_page( 
        	'Customer Information Page', 
        	'Customer Info', 
        	'manage_options',
        	'customer_info_page',
        	array( $this, 'management_page' ),
        	'dashicons-groups', 24 );
    }

    public function management_page(){

    	$list_table = new Customer_Info_List_Table();
		$list_table->prepare_items();

    	?>
			<div class="wrap">
				<h2>Customer Info</h2>
				<?php $list_table->display(); ?>
			</div>
		<?php
    }
}

$plugin = new customerInfo();
$plugin->init();