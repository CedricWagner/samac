<?php
/**
* 
*/
class SyncShippingAddress extends Synchronizable
{
	const TABLE_NAME = 'sync_shipping_adresses';
	
	public static $definition = array(
		'table' => self::TABLE_NAME,
		'primary' => 'id',
		'fields' => array(
			'id' =>			 		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
			'sync_id' =>			array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ws_id' => 				array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'ps_id' => 				array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ws_date_update' =>		array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true),
			'action' =>				array('type' => self::TYPE_STRING, 'validate' => 'isString','required' => true),
		),
	);

	public static function proceedLineSync($line,$sync){
		$table_name = self::TABLE_NAME;

		//get all customers of company
		$id_group = Db::getInstance()->getValue('SELECT ps_id FROM ps_sync_customer_companies WHERE ws_id = '.self::getLineValue($line,'CLI_ID'));
		$group = new Group($id_group);
		$customers = $group->getCustomers();
		foreach ($customers as $customer) {

			//get last category sync
			$ws_id = self::getLineValue($line,'ADR_ID').'-'.$customer['id_customer'];
			$sql = 'SELECT id FROM '._DB_PREFIX_.$table_name.' WHERE ws_id = "'.$ws_id.'" ORDER BY ws_date_update DESC';
			$id = Db::getInstance()->getValue($sql);

			//init SyncShippingAddress
			$ssa = new SyncShippingAddress();
			
			if($id){
				$ssa->action = self::ACTION_EDIT;
				$lastSyncSSA = new SyncShippingAddress($id);
				$ssa->ps_id = $lastSyncSSA->ps_id;
			}else{
				$ssa->action = self::ACTION_ADD;
			}

			//get the category if edit, otherwise create a new category
			$address = new Address($ssa->action==self::ACTION_ADD?null:$ssa->ps_id);
			$address->alias = self::getLineValue($line,'ADR_PRI')=='O'?'Adresse de livraison principale':'Adresse de livraison secondaire';
			$address->company = self::getLineValue($line,'ADR_SOCIETE');
			$address->lastname = $customer['lastname'];
			$address->firstname = $customer['firstname'];
			$address->address1 = self::getLineValue($line,'ADR_1')?self::getLineValue($line,'ADR_1'):' ';
			$address->address2 = self::getLineValue($line,'ADR_2').' '.self::getLineValue($line,'ADR_3');
			$address->postcode = self::getLineValue($line,'ADR_CP');
			$address->city = self::getLineValue($line,'ADR_VIL')?self::getLineValue($line,'ADR_VIL'):' ';
			$address->phone = self::getLineValue($line,'ADR_TEL');
			$address->phone_mobile = self::getLineValue($line,'ADR_POR');
			$address->other = self::getLineValue($line,'ADR_INF');	
			$address->id_customer = $customer['id_customer'];	
			if (self::getLineValue($line,'ADR_PAY')) {
				$country_id = Country::getByIso(substr(self::getLineValue($line,'ADR_PAY'),0,2));
			}else{
				$country_id = Country::getByIso('FR');
			}
			$address->id_country = $country_id;	

			if( $address->save() ){
				//add syncProduct entry
				$ssa->ws_id = self::getLineValue($line,'ADR_ID').'-'.$customer['id_customer'];
				$ssa->ps_id = $address->id;
				$dt = new DateTime();
				$ssa->ws_date_update = $dt->format('Y-m-d H:i:s');
				$ssa->sync_id = $sync->id;
				$ssa->save();
			}
		}

	}
}

?>