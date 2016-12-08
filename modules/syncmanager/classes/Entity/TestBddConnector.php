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

	public function getDocLines($date){
		$sql = "SELECT d.*, SUM(ld.LDOC_QTE*ld.LDOC_PRIX) AS TOTAL FROM EXT_WEB_DOC d INNER JOIN EXT_WEB_LDOC ld ON (d.DOC_CODE = ld.DOC_CODE) WHERE d.DOC_DATEUPDATE >= '".$date."' GROUP BY d.DOC_CODE";
		$stmt = $this->instance->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}
}

?>