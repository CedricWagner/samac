<?php
/**
* 
*/
class SyncDocLine extends Synchronizable
{
	const TABLE_NAME = 'sync_document_lines';

	public static $definition = array(
		'table' => self::TABLE_NAME,
		'primary' => 'id',
		'fields' => array(
			'id' =>			 		array('type' => self::TYPE_INT, 	'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
			'sync_id' => 			array('type' => self::TYPE_INT, 	'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ws_id' => 				array('type' => self::TYPE_STRING, 	'validate' => 'isString', 'required' => true),
			'ps_id' => 				array('type' => self::TYPE_INT, 	'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ws_date_update' =>		array('type' => self::TYPE_DATE, 	'validate' => 'isDateFormat', 'required' => true),
			'action' =>				array('type' => self::TYPE_STRING, 	'validate' => 'isString','required' => true),
		),
	);

	public static function proceedLineSync($line,$sync){
		$table_name = self::TABLE_NAME;

		//get last doc line sync
		$ws_id = self::getLineValue($line,'DOC_CODE').'-'.self::getLineValue($line,'LDOC_NUM');
		$sql = 'SELECT id FROM '._DB_PREFIX_.$table_name.' WHERE ws_id = "'.$ws_id.'" ORDER BY ws_date_update DESC';
		$id = Db::getInstance()->getValue($sql);

		//get product
		$sql = 'SELECT ps_id FROM '._DB_PREFIX_.SyncProduct::TABLE_NAME.' WHERE ws_id = "'.(int)self::getLineValue($line,'ART_ID').'" ORDER BY ws_date_update DESC';
		$product = new Product((int) Db::getInstance()->getValue($sql));

		//get order
		$sql = 'SELECT ps_id FROM '._DB_PREFIX_.SyncDoc::TABLE_NAME.' WHERE ws_id = "'.self::getLineValue($line,'DOC_CODE').'" ORDER BY ws_date_update DESC';
		$id_order = (int) Db::getInstance()->getValue($sql);

		//init syncDoc
		$sdl = new SyncDocLine();
		
		if($id){
			$sdl->action = self::ACTION_EDIT;
			$lastSyncDoc = new SyncDocLine($id);
			$sdl->ps_id = $lastSyncDoc->ps_id;
		}else{
			$sdl->action = self::ACTION_ADD;
		}

		//get the product if edit, otherwise create a new product
		$orderDetail = new OrderDetail($sdl->action==self::ACTION_ADD?null:$sdl->ps_id,null,Context::getContext());
		$orderDetail->id_order_detail = $sdl->action==self::ACTION_ADD?null:$sdl->ps_id;
		$orderDetail->id_order = $id_order;
		$orderDetail->id_warehouse = 0;
		$orderDetail->id_shop = Context::getContext()->shop->id;
		$orderDetail->product_id = $product->id;
		$orderDetail->product_name = $product->name[1];
		$orderDetail->product_quantity = (int) self::getLineValue($line,'LDOC_QTE');
		$orderDetail->product_price = (float) self::getLineValue($line,'LDOC_PRIX');
		$orderDetail->unit_price_tax_incl = (float) self::getLineValue($line,'LDOC_PRIX');
		$orderDetail->unit_price_tax_excl = (float) self::getLineValue($line,'LDOC_PRIX');
		$orderDetail->total_price_tax_excl = ((float) self::getLineValue($line,'LDOC_PRIX')) * ((int) self::getLineValue($line,'LDOC_QTE'));
		$orderDetail->total_price_tax_incl = ((float) self::getLineValue($line,'LDOC_PRIX')) * ((int) self::getLineValue($line,'LDOC_QTE'));

		if( $orderDetail->save() ){

			//add syncDocLine entry
			$sdl->ws_id = self::getLineValue($line,'DOC_CODE').'-'.self::getLineValue($line,'LDOC_NUM');
			$sdl->ps_id = $orderDetail->id;
			$sdl->ws_date_update = $line['LDOC_DATEUPDATE'];
			$sdl->sync_id = $sync->id;
			$sdl->save();
		}
	}
}

?>