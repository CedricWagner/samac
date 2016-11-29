<?php

abstract class Synchronizable extends ObjectModel implements iSynchronizable
{
	public $id;
	public $synchronization_id;
	public $ws_id;
	public $ps_id;
	public $ws_date_update;
	public $action;
	private $synchronization = false;

	const ACTION_EDIT = 'E'; 
	const ACTION_ADD = 'A'; 
	const ACTION_DELETE = 'D'; 

	public function getSynchronization(){
		if($this->synchronization === false){
			$this->synchronization = new Synchronization($synchronization_id);
		}
		return $this->synchronization;
	}

	public static function getLineValue($line, $key){
		if (isset($line[$key])) {
			return $line[$key];
		}else{
			throw new Exception("Colonne introuvable : ".$key, 1);
		}
	}

}

?>