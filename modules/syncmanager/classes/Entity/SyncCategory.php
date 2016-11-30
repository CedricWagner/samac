<?php
/**
* 
*/
class SyncCategory extends Synchronizable
{
	
	public static $definition = array(
		'table' => 'sync_categories',
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

		//get last category sync
		$ws_id = self::getLineValue($line,'FAM_ID');
		$sql = 'SELECT id FROM '._DB_PREFIX_.$table_name.' WHERE ws_id = '.(int)$ws_id.' ORDER BY ws_date_update DESC';
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
		$cat->name[1] = self::getLineValue($line,'FAM_DESIGNATION');
		$cat->link_rewrite[1] = Tools::link_rewrite(self::getLineValue($line,'FAM_DESIGNATION'));
		$cat->parent_id = 1;

		if( $cat->save() ){
			//add syncProduct entry
			$sc->ws_id = $line['FAM_ID'];
			$sc->ps_id = $cat->id;
			$dt = new DateTime();
			$sc->ws_date_update = $dt->format('Y-m-d H:i:s');
			$sc->sync_id = $sync->id;
			$sc->save();
		}
	}
}

?>