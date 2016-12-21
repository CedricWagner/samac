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
		$sql = "SELECT d.*, SUM(ld.LDOC_QTE*ld.LDOC_PRIX) AS TOTAL, SUM(ld.LDOC_QTE) AS QTE FROM EXT_WEB_DOC d INNER JOIN EXT_WEB_LDOC ld ON (d.DOC_CODE = ld.DOC_CODE) WHERE d.DOC_DATEUPDATE >= '".$date."' GROUP BY d.DOC_CODE";
		$stmt = $this->instance->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function getDocExtraLines(){
		$sql = "	SELECT DISTINCT td.* 
					FROM EXT_WEB_TRDOC td
					INNER JOIN EXT_WEB_LDOC ld 
						ON CONCAT(td.DOC_CODE,td.LDOC_NUM) = CONCAT(ld.DOC_CODE,ld.LDOC_NUM) 
					INNER JOIN EXT_WEB_DOC d 
						ON td.DOC_CODE = d.DOC_CODE 
					WHERE d.DOC_NOT = 'O'
					OR ld.LDOC_NOT = 'O'
					";
		$stmt = $this->instance->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function notifyChanges(){
		$sql = "UPDATE EXT_WEB_DOC SET DOC_NOT = 'N' WHERE DOC_NOT = 'O'";
		$stmt = $this->instance->prepare($sql);
		$stmt->execute();
		$sql = "UPDATE EXT_WEB_LDOC SET LDOC_NOT = 'N' WHERE LDOC_NOT = 'O'";
		$stmt = $this->instance->prepare($sql);
		$stmt->execute();
	}
}

?>