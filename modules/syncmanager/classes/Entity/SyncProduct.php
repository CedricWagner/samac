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

	public static function proceedLineSync($line,$sync){
		$table_name = self::$definition['table'];

		//get last product sync
		$ws_id = self::getLineValue($line,'ART_ID');
		$sql = 'SELECT id FROM '._DB_PREFIX_.$table_name.' WHERE ws_id = '.(int)$ws_id.' ORDER BY ws_date_update DESC';
		$id = Db::getInstance()->getValue($sql);

		//init syncProduct
		$sp = new SyncProduct();
		
		if($id){
			$sp->action = self::ACTION_EDIT;
			$lastSyncProduct = new SyncProduct($id);
			$sp->ps_id = $lastSyncProduct->ps_id;
		}else{
			$sp->action = self::ACTION_ADD;
		}

		//get the product if edit, otherwise create a new product
		$product = new Product($sp->action==self::ACTION_ADD?null:$sp->ps_id);
		$product->name[1] = self::getLineValue($line,'ART_DESIGNATION');
		$product->description[1] = self::getLineValue($line,'ART_DESCRIPTION');
		$product->description_short[1] = self::getLineValue($line,'ART_DESCRIPTION');
		$product->reference = self::getLineValue($line,'ART_CODE');
		$product->weight = self::getLineValue($line,'ART_POI');
		$product->height = self::getLineValue($line,'ART_HAU');
		$product->width = self::getLineValue($line,'ART_LAR');
		$product->depth = self::getLineValue($line,'ART_LON');
		$product->price = str_replace(',', '.', self::getLineValue($line,'ART_PRIXPUB'));
		$product->date_add = self::getLineValue($line,'ART_DATEUPDATE');
		$product->date_upd = self::getLineValue($line,'ART_DATEUPDATE');
		$product->quantity = self::getLineValue($line,'ART_STK');
		$product->ecotax = self::getLineValue($line,'ART_ECOPAR');

		$product->modifierWsLinkRewrite();

		if( $product->save() ){
			//set product stock
			StockAvailable::setQuantity($product->id, 0, (int) self::getLineValue($line,'ART_STK'));
			
			//add syncProduct entry
			$sp->ws_id = $line['ART_ID'];
			$sp->ps_id = $product->id;
			$sp->ws_date_update = $line['ART_DATEUPDATE'];
			$sp->sync_id = $sync->id;
			$sp->save();
		}
	}
}

?>