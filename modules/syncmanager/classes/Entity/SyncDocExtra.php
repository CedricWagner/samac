<?php
/**
* 
*/
class SyncDocExtra extends Synchronizable
{
	const TABLE_NAME = 'sync_document_extra';

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

		//get order
		$sql = 'SELECT ps_id FROM '._DB_PREFIX_.SyncDoc::TABLE_NAME.' WHERE ws_id = "'.self::getLineValue($line,'DOC_CODE').'" ORDER BY ws_date_update DESC';
		$id_order = (int) Db::getInstance()->getValue($sql);

		//get order detail
		$sql = 'SELECT ps_id FROM '._DB_PREFIX_.SyncDocLine::TABLE_NAME.' WHERE ws_id = "'.self::getLineValue($line,'DOC_CODE').'-'.self::getLineValue($line,'LDOC_NUM').'" ORDER BY ws_date_update DESC';
		$id_order_detail = (int) Db::getInstance()->getValue($sql);

		//init syncDoc
		$sde = new SyncDocExtra();
		
		$sde->action = self::ACTION_ADD;
		
		$orderExtra = new OrderExtra();
		$orderExtra->id_order = $id_order;
		$orderExtra->id_order_detail = $id_order_detail;
		$orderExtra->product_quantity = (int) self::getLineValue($line,'WS_QTE');
		$orderExtra->tracking_code = self::getLineValue($line,'WS_TRACKING');
		$orderExtra->delivery_date = self::getLineValue($line,'WS_DATE');
		$orderExtra->ws_num_order = self::getLineValue($line,'WS_C');
		$orderExtra->ws_num_delivery = self::getLineValue($line,'WS_L');
		$orderExtra->ws_num_invoice = self::getLineValue($line,'WS_F');
		
		if( $orderExtra->save() ){

			//add syncDocExtra entry
			$sde->ws_id = self::getLineValue($line,'DOC_CODE').'-'.self::getLineValue($line,'LDOC_NUM').'-'.self::getLineValue($line,'WS_QTE');
			$sde->ps_id = $orderExtra->id;
			$sde->ws_date_update = $line['WS_DATEUPDATE'];
			$sde->sync_id = $sync->id;
			$sde->save();
		}
	}

	public static function deleteOrderExtra($line){
		//get sync doc extra
		$sql = 'SELECT ps_id FROM '._DB_PREFIX_.self::TABLE_NAME.' WHERE ws_id = "'.self::getLineValue($line,'DOC_CODE').'-'.self::getLineValue($line,'LDOC_NUM').'-'.self::getLineValue($line,'WS_QTE').'" ORDER BY ws_date_update DESC';
		$id = Db::getInstance()->getValue($sql);
		if($id){
			$orderExtra = new OrderExtra($id);
			$orderExtra->delete();
		}
	}
}

?>