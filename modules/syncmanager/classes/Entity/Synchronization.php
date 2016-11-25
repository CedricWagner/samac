<?php
/**
* 
*/
class Synchronization extends ObjectModel
{
	
	public $id;
	public $date;
	public $method;
	public $state;


	public static $definition = array(
		'table' => 'synchronizations',
		'primary' => 'id',
		'fields' => array(
			'id' =>			 		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
			'date' => 				array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true),
			'method' => 			array('type' => self::TYPE_STRING, 'required' => false, 'size' => 12),
			'state' => 				array('type' => self::TYPE_STRING, 'required' => false, 'size' => 10),
		),
	);

}

?>