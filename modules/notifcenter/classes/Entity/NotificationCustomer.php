<?php
/**
* 
*/
class NotificationCustomer extends ObjectModel
{
	
	public $id;
	public $notif_id;
	public $customer_id;
	public $is_seen = null;

	public $__customer;

	public static $definition = array(
		'table' => 'notification_customers',
		'primary' => 'id',
		'fields' => array(
			'id' =>			 		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
			'notif_id' => 			array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
			'customer_id' =>		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
			'is_seen' => 			array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => false),
		),
	);

	function __construct($id = null)
	{
		parent::__construct($id);
		$this->__customer = new Customer($this->customer_id);
	}
	
}

?>