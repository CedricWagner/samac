<?php
/**
* 
*/
class SyncPrice extends Synchronizable
{
	const TABLE_NAME = 'sync_prices';

	public static $definition = array(
		'table' => self::TABLE_NAME,
		'primary' => 'id',
		'fields' => array(
			'id' =>			 		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
			'sync_id' => 			array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ws_id' => 				array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'ps_id' => 				array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ws_date_update' =>		array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true),
			'action' =>				array('type' => self::TYPE_STRING, 'validate' => 'isString','required' => true),
		),
	);

	public static function proceedLineSync($line,$sync){
		$datetime = new DateTime();
		$table_name = self::TABLE_NAME;

		//get last company sync
		$ws_id = self::getLineValue($line,'ART_ID').'-'.self::getLineValue($line,'CLI_ID').'-'.((int)self::getLineValue($line,'ATC_QTE'));
		$sql = 'SELECT id FROM '._DB_PREFIX_.$table_name.' WHERE ws_id = "'.$ws_id.'" ORDER BY ws_date_update DESC';
		$id = Db::getInstance()->getValue($sql);

		//init syncPrice
		$sp = new SyncPrice();
		
		if($id){
			$sp->action = self::ACTION_EDIT;
			$lastSyncPrice = new SyncPrice($id);
			$sp->ps_id = $lastSyncPrice->ps_id;
		}else{
			$sp->action = self::ACTION_ADD;
		}

		//get the price if edit, otherwise create a new price
		$price = new SpecificPrice($sp->action==self::ACTION_ADD?null:$sp->ps_id);
		$price->price = self::getLineValue($line,'ATC_PRIX');
		$price->reduction_type = 'amount';
		$price->from_quantity = (int)self::getLineValue($line,'ATC_QTE');
		$price->reduction_tax = 1;
		$price->id_currency = 0;
		$price->id_country = 0;
		$price->id_customer = 0;
		$price->from = 0;
		$price->to = 0;
		$price->reduction = 0;
		$price->id_shop = (int)Context::getContext()->shop->id;
		//get product
		$product_id = Db::getInstance()->getValue('SELECT ps_id FROM ps_sync_products WHERE ws_id = '.self::getLineValue($line,'ART_ID').' ORDER BY ws_date_update DESC, sync_id DESC');
		$price->id_product = $product_id;
		//get group
		$group_id = Db::getInstance()->getValue('SELECT ps_id FROM ps_sync_customer_companies WHERE ws_id = '.self::getLineValue($line,'CLI_ID').' ORDER BY ws_date_update DESC, sync_id DESC');
		$price->id_group = $group_id;

		if( $price->save() ){
			//add syncPrice entry
			$sp->ws_id = self::getLineValue($line,'ART_ID').'-'.self::getLineValue($line,'CLI_ID').'-'.((int)self::getLineValue($line,'ATC_QTE'));
			$sp->ps_id = $price->id;
			$sp->ws_date_update = $datetime->format('Y-m-d H:i:s');
			$sp->sync_id = $sync->id;
			$sp->save();
		}
	}
}

?>