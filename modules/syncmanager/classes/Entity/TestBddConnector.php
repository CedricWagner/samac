<?php
/**
* 
*/
class TestBddConnector implements iWaveSoftConnector
{
	
	private $instance;
	private $connectionParams;

	function __construct()
	{
		$pdo = new PDO('mysql:host=localhost;dbname=wavesoft_fake','root','admin');
		$this->instance = $pdo;
	}

	public function getInstance(){
		return $this->instance;
	}

	public function getLines($table,$fieldDate,$date){
		$sql = "SELECT * FROM ".$table." WHERE ".$fieldDate." >= '".$date."'";
		$stmt = $this->instance->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function getDistinctLines($table,$fieldDate,$date,$fields){
		$sql = "SELECT DISTINCT ".$fields." FROM ".$table." WHERE ".$fieldDate." >= '".$date."'";
		$stmt = $this->instance->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}
}

?>