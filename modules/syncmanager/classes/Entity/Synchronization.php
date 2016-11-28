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

	public $__prodAdd;
	public $__prodEdit;

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

	public static function getLastSynchronizations($nb=10){
		$lastSyncs = array();
		$result = Db::getInstance()->executeS('SELECT id FROM '._DB_PREFIX_.'synchronizations ORDER BY date DESC LIMIT '.$nb);
		foreach ($result as $aSync) {
			$sync = new Synchronization($aSync['id']);
			$sync->__prodAdd = SyncProduct::getCountBySynchronization($aSync['id'],SyncProduct::ACTION_ADD);
			$sync->__prodEdit = SyncProduct::getCountBySynchronization($aSync['id'],SyncProduct::ACTION_EDIT);
			$lastSyncs[] = $sync;
		}

		return $lastSyncs;
	}

}

?>