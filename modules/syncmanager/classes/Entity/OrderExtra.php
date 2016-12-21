<?php

/**
* 
*/
class OrderExtra extends ObjectModel
{
	
	public $id_order_extra;
	public $id_order;
	public $id_order_detail;
	public $product_quantity;
	public $tracking_code;
	public $delivery_date;
	public $ws_num_order;
	public $ws_num_delivery;
	public $ws_num_invoice;

	public $product;

	public static $definition = array(
		'table' => 'order_extra',
		'primary' => 'id_order_extra',
		'fields' => array(
			'id_order_extra' =>		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
			'id_order' =>			array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId'),
			'id_order_detail' =>	array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId'),
			'product_quantity' =>	array('type' => self::TYPE_INT, 'required' => true),
			'tracking_code' =>		array('type' => self::TYPE_STRING, 'required' => false, 'size' => 32),
			'delivery_date' =>		array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true),
			'ws_num_order' =>		array('type' => self::TYPE_STRING, 'required' => false, 'size' => 32),
			'ws_num_delivery' =>	array('type' => self::TYPE_STRING, 'required' => false, 'size' => 32),
			'ws_num_invoice' =>		array('type' => self::TYPE_STRING, 'required' => false, 'size' => 32),
		),
	);

	public static function getByOrderId($idOrder){
		// get order extra by order
		$sql = 'SELECT oe.id_order_extra, p.id_product FROM '._DB_PREFIX_.'order_extra oe INNER JOIN '._DB_PREFIX_.'order_detail od ON oe.id_order_detail = od.id_order_detail INNER JOIN '._DB_PREFIX_.'product p ON p.id_product = od.product_id WHERE oe.id_order = '.$idOrder;
		$lines = Db::getInstance()->executeS($sql);
		$lstOrderExtra = array();
		foreach ($lines as $oe) {
			$orderExtra = new OrderExtra($oe['id_order_extra']);
			$orderExtra->product = new Product($oe['id_product']);
			$lstOrderExtra[] = $orderExtra;
		}

		return $lstOrderExtra;
	}

}