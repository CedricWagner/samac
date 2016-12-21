<?php
/**
* 
*/
interface iWaveSoftConnector
{

	function __construct();

	public function getInstance();
	public function getLines($table,$fieldDate,$date);
	public function getDistinctLines($table,$fieldDate,$date,$fields);
	public function getDocLines($date);
	public function getDocExtraLines();
	public function notifyChanges();


}


?>