<?php
/**
* 
*/
class SyncDoc extends Synchronizable
{
	const TABLE_NAME = 'sync_products';

	public static $definition = array(
		'table' => self::TABLE_NAME,
		'primary' => 'id',
		'fields' => array(
			'id' =>			 		array('type' => self::TYPE_INT, 	'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
			'sync_id' => 			array('type' => self::TYPE_INT, 	'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ws_id' => 				array('type' => self::TYPE_INT, 	'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ps_id' => 				array('type' => self::TYPE_INT, 	'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ws_date_update' =>		array('type' => self::TYPE_DATE, 	'validate' => 'isDateFormat', 'required' => true),
			'action' =>				array('type' => self::TYPE_STRING, 	'validate' => 'isString','required' => true),
		),
	);

	public static function proceedLineSync($line,$sync){
		$table_name = self::TABLE_NAME;

		//get last product sync
		$ws_id = self::getLineValue($line,'DOC_CODE');
		$sql = 'SELECT id FROM '._DB_PREFIX_.$table_name.' WHERE ws_id = "'.(int)$ws_id.'" ORDER BY ws_date_update DESC';
		$id = Db::getInstance()->getValue($sql);

		//init syncDoc
		$sd = new SyncDoc();
		
		if($id){
			$sd->action = self::ACTION_EDIT;
			$lastSyncDoc = new SyncDoc($id);
			$sd->ps_id = $lastSyncDoc->ps_id;
		}else{
			$sd->action = self::ACTION_ADD;
		}

		//get the product if edit, otherwise create a new product
		$order = new Order($sd->action==self::ACTION_ADD?null:$sd->ps_id);
		$order->payment = 'Wavesoft';
		$order->conversion_rate = 1;
		$order->module = 'sync_manager';
		$order->recyclable = 0;
		$order->gift = 0;
		$order->gift_message = '';
		$total = self::getLineValue($line,'TOTAL');
		if (in_array(self::getLineValue($line,'DOC_STAT'), array('L','F'))) {
			$order->total_paid = $total;
			$order->total_paid_tax_excl = $total;
			$order->total_paid_tax_incl = $total;
		}else{
			$order->total_paid = 0;
			$order->total_paid_tax_excl = 0;
			$order->total_paid_tax_incl = 0;
		}
		$order->total_products_wt = $total;
		$order->total_shipping = 0;
		$order->total_shipping_tax_excl = 0;
		$order->total_shipping_tax_incl = 0;
		$order->carrier_tax_rate = 0;
		$order->carrier_tax_rate = 0;

		//http://stackoverflow.com/questions/35314410/create-order-in-prestashop-programmatically

		if( $product->save() ){
			


			//add syncDoc entry
			$sd->ws_id = $line['ART_ID'];
			$sd->ps_id = $product->id;
			$sd->ws_date_update = $line['ART_DATEUPDATE'];
			$sd->sync_id = $sync->id;
			$sd->save();
		}
	}
}

?>