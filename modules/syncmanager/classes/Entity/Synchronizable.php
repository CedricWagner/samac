<?php

abstract class Synchronizable extends ObjectModel implements iSynchronizable
{
	public $id;
	public $synchronization_id;
	public $ws_id;
	public $ps_id;
	public $ws_date_update;
	public $action;
	private $synchronization;

	const ACTION_EDIT = 'E'; 
	const ACTION_ADD = 'A'; 
	const ACTION_DELETE = 'D'; 

	function __construct($param)
	{
		$synchronization = false;
		parent::__construct($param);
	}


	public function getSynchronization(){
		if($this->synchronization === false){
			$this->synchronization = new Synchronization($synchronization_id);
		}
		return $this->synchronization;
	}

	public abstract function proceedLineSync($line);
	public abstract function proceedAddLine($line);
	public abstract function proceedEditLine($line);

	public static getLineValue($line, $key){
		if (isset($line[$key])) {
			return $line[$key];
		}else{
			throw new Exception("Colonne introuvable : ".$key, 1);
		}
	}

}

?>