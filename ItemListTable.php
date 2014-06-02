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


class ItemListTable extends \WP_List_Table {

	public $data = array();

	public $_column_headers;

	function __construct(){
//		global $status, $page;

		parent::__construct( array(
			'singular'  => 'item',
			'plural'    => 'items',
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
			case 'detail':
			case 'price':
			case 'stock':
			case 'visible':
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
	function column_title($item){

		//Build row actions
		$actions = array(
			'edit'      => sprintf('<a href="?page=%s&action=%s&item=%s">Edit</a>',$_REQUEST['page'],'edit',$item['item_id']),
			'delete'    => sprintf('<a href="?page=%s&action=%s&item=%s">Delete</a>',$_REQUEST['page'],'delete',$item['item_id']),
		);

		//Return the title contents
		return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			/*$1%s*/ $item['title'],
			/*$2%s*/ $item['item_id'],
			/*$3%s*/ $this->row_actions($actions)
		);
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
			/*$2%s*/ $item['item_id']                //The value of the checkbox should be the record's id
		);
	}


	/**
	 * @return array
	 */
	function get_columns(){
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'title'     => 'Title',
			'detail'    => 'Detail',
			'price'  => 'Price',
			'stock'  => 'Stock',
			'visible'  => 'Visible',
		);
		return $columns;
	}


	/**
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'title'     => array('title',false),     //true means it's already sorted
			'detail'    => array('detail',false),
			'price'  => array('price',false),
			'stock'  => array('stock',false),
			'visible'  => array('visible',false),
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
	function prepare_items() {
//		global $wpdb;

		$per_page = 5;

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->process_bulk_action();

		$data = $this->data;

		usort($data, function ($a,$b) {
			$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'item_id';
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