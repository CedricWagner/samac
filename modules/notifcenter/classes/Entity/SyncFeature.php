<?php
/**
* 
*/
class SyncFeature extends Synchronizable
{
	public $ps_id_feature;

	const TABLE_NAME= 'sync_features';

	public static $definition = array(
		'table' => self::TABLE_NAME,
		'primary' => 'id',
		'fields' => array(
			'id' =>			 		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
			'sync_id' => 			array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ws_id' => 				array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'ps_id' => 				array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ps_id_feature' => 		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ws_date_update' =>		array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true),
			'action' =>				array('type' => self::TYPE_STRING, 'validate' => 'isString','required' => true),
		),
	);

	public static function proceedLineSync($line,$sync){
		$feature = $line['feature'];
		$table_name = self::TABLE_NAME;

		//get last category sync
		$ws_id = self::getLineValue($line,0);
		$sql = 'SELECT id FROM '._DB_PREFIX_.$table_name.' WHERE ws_id = "'.$ws_id.'" AND ps_id_feature = '.($feature->id).' ORDER BY ws_date_update DESC';
		$id = Db::getInstance()->getValue($sql);

		//init SyncFeature
		$sf = new SyncFeature();
		
		if($id){
			$sf->action = self::ACTION_EDIT;
			$lastSyncCat = new SyncFeature($id);
			$sf->ps_id = $lastSyncCat->ps_id;
		}else{
			$sf->action = self::ACTION_ADD;
		}

		//get the feature if edit, otherwise create a new feature
		$featureValue = new FeatureValue($sf->action==self::ACTION_ADD?null:$sf->ps_id);
		$featureValue->id_feature = $feature->id;
		$featureValue->value[1] = self::getLineValue($line,0);


		if( $featureValue->save() ){
			//add syncProduct entry
			$sf->ws_id = self::getLineValue($line,0);
			$sf->ps_id = $featureValue->id;
			$dt = new DateTime();
			$sf->ws_date_update = $dt->format('Y-m-d H:i:s');
			$sf->sync_id = $sync->id;
			$sf->ps_id_feature = $feature->id;
			$sf->save();
		}
	}
}

?>