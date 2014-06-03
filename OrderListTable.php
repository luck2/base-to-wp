<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2014/06/01
 * Time: 22:58
 */

namespace BaseToWP;

if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class ItemListTable
 * @package BaseToWP
 */
class OrderListTable extends \WP_List_Table {

	public $data = array();

	public $_column_headers;

	public function __construct(){
//		global $status, $page;
		parent::__construct( array(
			'singular'  => 'order',
			'plural'    => 'orders',
			'ajax'      => true,
		) );
	}

	/**
	 * @param $item
	 * @param $column_name
	 *
	 * @return mixed
	 */
	function column_default($item, $column_name){
		switch($column_name){
			case 'unique_key':
			case 'ordered':
			case 'payment':
			case 'total':
			case 'terminated':
				return $item[$column_name];
			default:
				return print_r($item,true);
		}
	}
	/**
	 * @param $item
	 *
	 * @return string
	 */
	function column_unique_key($item){
		//Build row actions
		$actions = array(
			'edit'      => sprintf('<a href="?page=%s&action=%s&unique_key=%s">Detail</a>',$_GET['page'],'detail',$item['unique_key']),
			'delete'    => sprintf('<a href="?page=%s&action=%s&unique_key=%s">Delete</a>',$_GET['page'],'delete',$item['unique_key']),
		);
		//Return the title contents
		return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			/*$1%s*/ $item['unique_key'],
			/*$2%s*/ $item['unique_key'],
			/*$3%s*/ $this->row_actions($actions)
		);
	}
	/**
	 * @param $item
	 *
	 * @return string
	 */
	function column_name($item){
		return $item['last_name'] . ' ' .  $item['first_name'];
	}
	/**
	 * @param $item
	 *
	 * @return string
	 */
	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item['unique_key']                //The value of the checkbox should be the record's id
		);
	}

	/**
	 * @return array
	 */
	function get_columns(){
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'unique_key'     => 'Unique key',
			'ordered'    => 'Ordered',
			'payment'  => 'Payment',
			'name'  => 'Name',
			'total'  => 'Total',
			'terminated'  => 'Terminated',
		);
		return $columns;
	}
	/**
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'unique_key'     => array('unique_key',false),     //true means it's already sorted
			'ordered'    => array('ordered',false),
			'payment'  => array('payment',false),
			'name'  => array('name',false),
			'total'  => array('total',false),
			'terminated'  => array('terminated',false),
		);
		return $sortable_columns;
	}
	/**
	 * @return array
	 */
	function get_bulk_actions() {
		$actions = array(
			'delete'    => 'Delete'
		);
		return $actions;
	}
	/**
	 * Detect when a bulk action is being triggered...
	 */
	function process_bulk_action() {
		if( 'delete'===$this->current_action() ) {
			wp_die('Items deleted (or they would be if we had items to delete)!');
		}
	}

	/**
	 *
	 */
	public function prepare_items() {
//		global $wpdb;

		$per_page = 5;

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->process_bulk_action();

		$data = $this->data;

		usort($data, function ($a,$b) {
			$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'ordered';
			$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc';
			$result = strcmp($a[$orderby], $b[$orderby]);
			return ($order==='asc') ? $result : -$result;
		});

		$current_page = $this->get_pagenum();

		$total_items = count($data);

		$data = array_slice($data,(($current_page-1)*$per_page),$per_page);

		$this->items = $data;

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil($total_items/$per_page)
		) );
	}




} 