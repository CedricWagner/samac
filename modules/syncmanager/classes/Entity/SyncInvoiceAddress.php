<?php
/**
* 
*/
class SyncInvoiceAddress extends Synchronizable
{
	const TABLE_NAME = 'sync_invoice_adresses';

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

			//get last customer sync
			$ws_id = self::getLineValue($line,'CLI_ID').'-'.$customer['id_customer'];
			$sql = 'SELECT id FROM '._DB_PREFIX_.$table_name.' WHERE ws_id LIKE "'.$ws_id.'" ORDER BY ws_date_update DESC';
			$id = Db::getInstance()->getValue($sql);

			//init SyncInvoiceAddress
			$sia = new SyncInvoiceAddress();
			
			if($id){
				$sia->action = self::ACTION_EDIT;
				$lastSyncSSA = new SyncInvoiceAddress($id);
				$sia->ps_id = $lastSyncSSA->ps_id;
			}else{
				$sia->action = self::ACTION_ADD;
			}

			//get the category if edit, otherwise create a new category
			$address = new Address($sia->action==self::ACTION_ADD?null:$sia->ps_id);
			$address->alias = 'Adresse de facturation';
			$address->company = self::getLineValue($line,'CLI_SOCIETE');
			$address->lastname = $customer['lastname'];
			$address->firstname = $customer['firstname'];
			$address->address1 = self::getLineValue($line,'ADR_FA1')?self::getLineValue($line,'ADR_FA1'):' ';
			$address->address2 = self::getLineValue($line,'ADR_FA2').' '.self::getLineValue($line,'ADR_FA3');
			$address->postcode = self::getLineValue($line,'ADR_FCP');
			$address->city = self::getLineValue($line,'ADR_FVIL')?self::getLineValue($line,'ADR_FVIL'):' ';
			$address->phone = self::getLineValue($line,'ADR_FTEL');
			$address->other = self::getLineValue($line,'CLI_FRANCO');	
			$address->id_customer = $customer['id_customer'];
			if (self::getLineValue($line,'ADR_FPAY')) {
				$country_id = Country::getByIso(substr(self::getLineValue($line,'ADR_FPAY'),0,2));
			}else{
				$country_id = Country::getByIso('FR');
			}
			$address->id_country = $country_id;	

			if( $address->save() ){
				//add syncProduct entry
				$sia->ws_id = self::getLineValue($line,'CLI_ID').'-'.$customer['id_customer'];
				$sia->ps_id = $address->id;
				$dt = new DateTime();
				$sia->ws_date_update = $dt->format('Y-m-d H:i:s');
				$sia->sync_id = $sync->id;
				$sia->save();
			}
		}

	}
}

?>