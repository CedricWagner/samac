<?php
/**
* 
*/
class SyncCompany extends Synchronizable
{
	
	public static $definition = array(
		'table' => 'sync_customer_companies',
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
		$datetime = new DateTime();
		$table_name = self::$definition['table'];

		//get last company sync
		$ws_id = self::getLineValue($line,'CLI_ID');
		$sql = 'SELECT id FROM '._DB_PREFIX_.$table_name.' WHERE ws_id = '.(int)$ws_id.' ORDER BY ws_date_update DESC';
		$id = Db::getInstance()->getValue($sql);

		//init syncCompany
		$sc = new SyncCompany();
		
		if($id){
			$sc->action = self::ACTION_EDIT;
			$lastSyncComp = new SyncCompany($id);
			$sc->ps_id = $lastSyncComp->ps_id;
		}else{
			$sc->action = self::ACTION_ADD;
		}

		//get the category if edit, otherwise create a new category
		$comp = new Group($sc->action==self::ACTION_ADD?null:$sc->ps_id);
		$comp->name[1] = self::getLineValue($line,'CLI_SOCIETE');
		$comp->price_display_method = 1;
		if($sc->action==self::ACTION_ADD){
			$comp->date_add = $datetime->format('Y-m-d H:i:s');
		}
		$comp->date_upd = $datetime->format('Y-m-d H:i:s');

		if( $comp->save() ){
			//delete default module restrictions
			Group::truncateModulesRestrictions($comp->id);
			//add syncProduct entry
			$sc->ws_id = $line['CLI_ID'];
			$sc->ps_id = $comp->id;
			$sc->ws_date_update = $datetime->format('Y-m-d H:i:s');
			$sc->sync_id = $sync->id;
			$sc->save();
		}
	}
}

?>