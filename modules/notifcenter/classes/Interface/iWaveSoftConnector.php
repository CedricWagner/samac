<?php
/**
* 
*/
interface iWaveSoftConnector
{

	function __construct();

	public function getInstance();
	public function getLines($table,$fieldDate,$date);


}


?>