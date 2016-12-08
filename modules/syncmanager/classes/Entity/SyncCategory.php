<?php
/**
* 
*/
class SyncCategory extends Synchronizable
{
	const TABLE_NAME = 'sync_categories';
	
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

		//get last category sync
		$ws_id = self::getLineValue($line,'ART_ASF');
		$sql = 'SELECT id FROM '._DB_PREFIX_.self::TABLE_NAME.' WHERE ws_id = "'.$ws_id.'" ORDER BY ws_date_update DESC';
		$id = Db::getInstance()->getValue($sql);

		//init syncCategory
		$sc = new SyncCategory();
		
		if($id){
			$sc->action = self::ACTION_EDIT;
			$lastSyncCat = new SyncCategory($id);
			$sc->ps_id = $lastSyncCat->ps_id;
		}else{
			$sc->action = self::ACTION_ADD;
		}

		//get the category if edit, otherwise create a new category
		$cat = new Category($sc->action==self::ACTION_ADD?null:$sc->ps_id);
		$cat->name[1] = self::getLineValue($line,'ART_ASF');
		$cat->link_rewrite[1] = Tools::link_rewrite(self::getLineValue($line,'ART_ASF'));
		$cat->id_parent = Category::getRootCategory()->id;

		if( $cat->save() ){
			//add syncProduct entry
			$sc->ws_id = $line['ART_ASF'];
			$sc->ps_id = $cat->id;
			$dt = new DateTime();
			$sc->ws_date_update = $dt->format('Y-m-d H:i:s');
			$sc->sync_id = $sync->id;
			$sc->save();
		}
	}
}

?>