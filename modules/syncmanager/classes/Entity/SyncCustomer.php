<?php
/**
* 
*/
class SyncCustomer extends Synchronizable
{
	
	public static $definition = array(
		'table' => 'sync_customers',
		'primary' => 'id',
		'fields' => array(
			'id' =>			 		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
			'sync_id' => 			array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
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

	public static function generatePassword($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	public static function proceedLineSync($line,$sync){
		$datetime = new DateTime();
		$table_name = self::$definition['table'];

		//get last customer sync
		$ws_id = self::getLineValue($line,'CON_ID');
		$sql = 'SELECT id FROM '._DB_PREFIX_.$table_name.' WHERE ws_id = '.(int)$ws_id.' ORDER BY ws_date_update DESC';
		$id = Db::getInstance()->getValue($sql);

		//init SyncCustomer
		$sc = new SyncCustomer();
		
		if($id){
			$sc->action = self::ACTION_EDIT;
			$lastSyncComp = new SyncCustomer($id);
			$sc->ps_id = $lastSyncComp->ps_id;
		}else{
			$sc->action = self::ACTION_ADD;
		}

		//get the customer if edit, otherwise create a new customer
		$customer = new Customer($sc->action==self::ACTION_ADD?null:$sc->ps_id);
		$customer->firstname = self::getLineValue($line,'CON_PRE');
		$customer->lastname = self::getLineValue($line,'CON_NOM');
		$customer->email = self::getLineValue($line,'CON_MAIL');
		$customer->id_gender = self::getLineValue($line,'CON_TYPE')=="Mr"?1:2;
		if($sc->action==self::ACTION_ADD){
			$customer->date_add = $datetime->format('Y-m-d H:i:s');
			$passwd = SyncCustomer::generatePassword();
			$customer->setWsPasswd($passwd);
		}
		$customer->date_upd = $datetime->format('Y-m-d H:i:s');

		if( $customer->save() ){
			//send email with account information
			if($sc->action==self::ACTION_ADD){
				if(!_PS_MODE_DEV_){	
					Mail::Send((int)Context::getContext()->language->id, 'account', 'Création de votre compte SAMAC',array('{email}'=>$customer->email,'{firstname}'=>$customer->firstname,'{lastname}'=>$customer->lastname,'{passwd}'=>$passwd),'c.wagner@mkdn-groupe.com','Dev','c.wagner@mkdn-groupe.com','SAMAC');
				}else{
					Logger::addLog('Nouveau compte créé : '.$customer->email.'/'.$passwd,1,null,'Customer',$customer->id);
				}
			}
			//add to group/company
			$customer->addGroups(array(DB::getInstance()->getValue('SELECT scc.ps_id FROM '._DB_PREFIX_.'sync_customer_companies scc WHERE scc.ws_id = '.self::getLineValue($line,'CLI_ID').' ORDER BY scc.ws_date_update, scc.sync_id')));
			//add syncProduct entry
			$sc->ws_id = $line['CON_ID'];
			$sc->ps_id = $customer->id;
			$sc->ws_date_update = $datetime->format('Y-m-d H:i:s');
			$sc->sync_id = $sync->id;
			$sc->save();
		}
	}
}

?>