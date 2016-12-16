<?php
/**
* 
*/
class SyncDoc extends Synchronizable
{
	const TABLE_NAME = 'sync_documents';

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

		//get last product sync
		$ws_id = self::getLineValue($line,'DOC_CODE');
		$sql = 'SELECT id FROM '._DB_PREFIX_.$table_name.' WHERE ws_id = "'.$ws_id.'" ORDER BY ws_date_update DESC';
		$id = Db::getInstance()->getValue($sql);

		//get customer
		$sql = 'SELECT ps_id FROM '._DB_PREFIX_.SyncCustomer::TABLE_NAME.' WHERE ws_id = "'.(int)self::getLineValue($line,'CON_ID').'" ORDER BY ws_date_update DESC';
		$customer = new Customer((int) Db::getInstance()->getValue($sql));

		// get shipping addresses
		$sql = 'SELECT ps_id FROM '._DB_PREFIX_.SyncShippingAddress::TABLE_NAME.' WHERE ws_id = "'.(int)self::getLineValue($line,'ADR_ID').'-'.$customer->id.'" ORDER BY ws_date_update DESC';
		$shippingAddress = new Address((int) Db::getInstance()->getValue($sql));
		
		// get shipping addresses
		$sql = 'SELECT ps_id FROM '._DB_PREFIX_.SyncInvoiceAddress::TABLE_NAME.' WHERE ws_id = "'.self::getLineValue($line,'CLI_ID').'-'.$customer->id.'" ORDER BY ws_date_update DESC';
		$invoiceAddress = new Address((int) Db::getInstance()->getValue($sql));

		// get carrier
		$carrier = Carrier::getCarrierByReference(1);

		//get currency
		$currency = Currency::getDefaultCurrency();

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

		$order->id_customer = $customer->id;
		$order->id_address_invoice = $invoiceAddress->id;
		$order->id_address_delivery = $shippingAddress->id;
		$order->id_carrier = $carrier->id;
		$order->reference = self::getLineValue($line,'DOC_CODE');
		$order->id_currency = $currency->id;

		$order->payment = 'Wavesoft';
		$order->conversion_rate = 1;
		$order->module = 'sync_manager';
		$order->recyclable = 0;
		$order->gift = 0;
		$order->gift_message = '';
		$total = self::getLineValue($line,'TOTAL');
		// $quantity = self::getLineValue($line,'QTE');
		if (in_array(self::getLineValue($line,'DOC_STAT'), array('L','F'))) {
			$order->total_products = (int) $total;
			$order->total_paid = 0;
			$order->total_paid_real = 0;
			$order->total_paid_tax_excl = (float) $total;
			$order->total_paid_tax_incl = (float) $total;
		}else{
			$order->total_products = 0;
			$order->total_paid = 0;
			$order->total_paid_real = 0;
			$order->total_paid_tax_excl = 0;
			$order->total_paid_tax_incl = 0;
		}
		$order->total_products_wt = (float) $total;
		$order->total_shipping = 0;
		$order->total_shipping_tax_excl = 0;
		$order->total_shipping_tax_incl = 0;
		$order->carrier_tax_rate = 0;
		$order->carrier_tax_rate = 0;
		if($sd->action == self::ACTION_ADD){
			$order->secure_key = $customer->secure_key;
			// create cart
			$cart = new Cart();
			$cart->id_currency = $currency->id;
			$cart->save();
			$order->id_cart = $cart->id;
		}


		if( $order->save() ){
	
			switch (self::getLineValue($line,'DOC_STAT')) {
				case 'X':
					// unprocessed PS order
					break;
				case 'W':
					// order saved in wavesoft
					$idStateWaiting = Configuration::get('ID_STATE_PAYMENT_WAITING');
					if(!$order->getCurrentState() != $idStateWaiting){
						$order->setCurrentState($idStateWaiting,1);
					}
					break;
				case 'V':
					// order saved in wavesoft
					$idStateWaiting = Configuration::get('ID_STATE_PAYMENT_WAITING');
					if(!$order->getCurrentState() != $idStateWaiting){
						$order->setCurrentState($idStateWaiting,1);
					}
					break;
				case 'C':
					// order saved in wavesoft
					$idStateOK = Configuration::get('ID_STATE_PAYMENT_OK');
					if(!$order->getCurrentState() != $idStateOK){
						$order->setCurrentState($idStateOK,1);
					}
					break;
				case 'L':
					// one or more deliveries
					$idStateDelivered = 5;
					if(!$order->getCurrentState() != $idStateDelivered){
						$order->setCurrentState($idStateDelivered,1);
					}
					break;
				case 'F':
					// one or more invoices
					$idStateOK = Configuration::get('ID_STATE_PAYMENT_OK');
					if($order->getCurrentState() != $idStateOK){
						$order->setCurrentState($idStateOK,1);
					}
					break;
				default:
					break;
			}

			//add syncDoc entry
			$sd->ws_id = $line['DOC_CODE'];
			$sd->ps_id = $order->id;
			$sd->ws_date_update = $line['DOC_DATEUPDATE'];
			$sd->sync_id = $sync->id;
			$sd->save();
		}
	}
}

?>