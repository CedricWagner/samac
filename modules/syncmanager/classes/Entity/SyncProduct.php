<?php
/**
* 
*/
class SyncProduct extends Synchronizable
{
	
	public static $definition = array(
		'table' => 'sync_products',
		'primary' => 'id',
		'fields' => array(
			'id' =>			 		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
			'sync_id' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ws_id' => 				array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ps_id' => 				array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ws_date_update' =>		array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true),
			'action' =>				array('type' => self::TYPE_STRING, 'validate' => 'isString','required' => true),
		),
	);

	public static function getCountBySynchronization($id, $action=false){
		$table_name = self::$definition['table'];
		$where_clause = '';
		if ($action) {
			$where_clause = 'AND action LIKE "'.$action.'"';
		}
		$sql = 'SELECT COUNT(id) FROM '._DB_PREFIX_.$table_name.' WHERE sync_id = '.$id.' '.$where_clause;
		return $row = Db::getInstance()->getValue($sql);
	}

	public function proceedLineSync($line){
		$table_name = self::$definition['table'];

		//check if product already exists
		$ws_id = self::getLineValue($line,'ART_ID');
		$sql = 'SELECT COUNT(id) FROM '._DB_PREFIX_.$table_name.' WHERE ws_id = '.(int)$ws_id;
		$nb = Db::getInstance()->getValue($sql);
		
		if ($nb >= 1) {
			$action = self::ACTION_EDIT;
			$this->proceedAddLine($line);
		}else{
			$action = self::ACTION_ADD;
			$this->proceedEditLine($line);
		}

	}

	public function proceedAddLine($line){
		$product = new Product();
		$product->name = self::getLineValue($line,'ART_DESIGNATION');
		$product->meta_title = self::getLineValue($line,'ART_DESIGNATION');
		$product->description = self::getLineValue($line,'ART_DESCRIPTION');
		$product->description_short = self::getLineValue($line,'ART_DESCRIPTION');
		$product->meta_description = self::getLineValue($line,'ART_DESCRIPTION');
		$product->reference = self::getLineValue($line,'ART_CODE');
		$product->weight = self::getLineValue($line,'ART_POI');
		$product->height = self::getLineValue($line,'ART_HAU');
		$product->width = self::getLineValue($line,'ART_LAR');
		$product->depth = self::getLineValue($line,'ART_LON');
		$product->price = self::getLineValue($line,'ART_PRIXPUB');

	}

}

?>