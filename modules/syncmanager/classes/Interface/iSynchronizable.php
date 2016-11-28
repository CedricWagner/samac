<?php
// workaround for PHP not allowing abstract static methods
interface iSynchronizable
{
	static function getCountBySynchronization($id,$action=false);
}


?>