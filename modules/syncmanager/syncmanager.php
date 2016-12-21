<?php

if (!defined('_PS_VERSION_'))
  exit;

$class_folder = dirname(__FILE__).'/classes/';
require_once($class_folder.'/Interface/iSynchronizable.php');
require_once($class_folder.'/Interface/iWaveSoftConnector.php');
require_once($class_folder.'/Entity/OrderExtra.php');
require_once($class_folder.'/Entity/Synchronization.php');
require_once($class_folder.'/Entity/Synchronizable.php');
require_once($class_folder.'/Entity/SyncProduct.php');
require_once($class_folder.'/Entity/SyncCategory.php');
require_once($class_folder.'/Entity/SyncFeature.php');
require_once($class_folder.'/Entity/SyncCompany.php');
require_once($class_folder.'/Entity/SyncCustomer.php');
require_once($class_folder.'/Entity/SyncPrice.php');
require_once($class_folder.'/Entity/SyncShippingAddress.php');
require_once($class_folder.'/Entity/SyncInvoiceAddress.php');
require_once($class_folder.'/Entity/SyncDoc.php');
require_once($class_folder.'/Entity/SyncDocLine.php');
require_once($class_folder.'/Entity/SyncDocExtra.php');
require_once($class_folder.'/Entity/TestBddConnector.php');

/**
* 
*/
class SyncManager extends Module
{
	
	function __construct()
	{
		$this->name = 'syncmanager';
	    $this->tab = 'administration';
	    $this->version = '1.0';
	    $this->author = 'MKDN Groupe';
	    $this->need_instance = 0;
	    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6');
	    $this->dependencies = array();
	    $this->bootstrap = true;
	 
	    parent::__construct();
	 
	    $this->displayName = $this->l('Sync Manager');
	    $this->description = $this->l('Permet l\'affichage et la gestion des synchronisations avec WaveSoft');
	 
	    $this->confirmUninstall = $this->l('La suppression de ce module rendra impossible la synchronisation avec WaveSoft, êtes-vous certain(e) ?');

	 
	}

	public function install()
	{
		// create main sync table
	    Db::getInstance()->execute('
	    	CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'synchronizations (
			  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			  date DATETIME NULL,
			  method VARCHAR(12) NULL,
			  state VARCHAR(10) NULL,
			  PRIMARY KEY(id)
			);		
    	');

    	// create sync products
	    Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'sync_products (
			  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			  sync_id INTEGER UNSIGNED NOT NULL,
			  ws_id INTEGER UNSIGNED NULL,
			  ps_id INTEGER UNSIGNED NULL,
			  ws_date_update DATETIME NULL,
			  action VARCHAR(1) NULL,
			  PRIMARY KEY(id),
			  INDEX sync_products_FKIndex1(sync_id)
			);
    	');

    	// create sync categories
	    Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'sync_categories (
			  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			  sync_id INTEGER UNSIGNED NOT NULL,
			  ws_id VARCHAR(55) NULL,
			  ps_id INTEGER UNSIGNED NULL,
			  ws_date_update DATETIME NULL,
			  action VARCHAR(1) NULL,
			  PRIMARY KEY(id),
			  INDEX sync_categories_FKIndex1(sync_id)
			);		
    	');

    	// create sync features
	    Db::getInstance()->execute('
			CREATE TABLE  IF NOT EXISTS '._DB_PREFIX_.'sync_features (
			  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			  sync_id INTEGER UNSIGNED NOT NULL,
			  ws_id VARCHAR(55) NULL,
			  ps_id INTEGER UNSIGNED NULL,
			  ps_id_feature INTEGER UNSIGNED NULL,
			  ws_date_update DATETIME NULL,
			  action VARCHAR(1) NULL,
			  PRIMARY KEY(id),
			  INDEX sync_features_FKIndex1(sync_id)
			);
    	');

    	// create sync companies
	    Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'sync_customer_companies (
			  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			  sync_id INTEGER UNSIGNED NOT NULL,
			  ps_id INTEGER UNSIGNED NULL,
			  ws_id INTEGER UNSIGNED NULL,
			  ws_date_update DATETIME NULL,
			  action VARCHAR(1) NULL,
			  PRIMARY KEY(id),
			  INDEX sync_customer_societies_FKIndex1(sync_id)
			);
    	');

    	// create sync companies
	    Db::getInstance()->execute('
			CREATE TABLE  IF NOT EXISTS '._DB_PREFIX_.'sync_customers(
				id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT ,
				sync_id INTEGER UNSIGNED NOT NULL ,
				ws_id INTEGER UNSIGNED NULL ,
				ps_id INTEGER UNSIGNED NULL ,
				ws_date_update DATETIME NULL ,
				ACTION VARCHAR( 1 ) NULL ,
				PRIMARY KEY ( id ) ,
				INDEX sync_customers_FKIndex1( sync_id )
			);
    	');

    	// create sync companies
	    Db::getInstance()->execute('
			CREATE TABLE  IF NOT EXISTS '._DB_PREFIX_.'sync_prices (
			  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			  sync_id INTEGER UNSIGNED NOT NULL,
			  ws_id VARCHAR(12) NULL,
			  ps_id INTEGER UNSIGNED NULL,
			  ws_date_update DATETIME NULL,
			  action VARCHAR(1) NULL,
			  PRIMARY KEY(id),
			  INDEX sync_prices_FKIndex1(sync_id)
			);
    	');

    	// create sync shipping adress
	    Db::getInstance()->execute('
			CREATE TABLE  IF NOT EXISTS '._DB_PREFIX_.'sync_shipping_adresses (
			  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			  sync_id INTEGER UNSIGNED NOT NULL,
			  ws_id VARCHAR(12) NULL,
			  ps_id INTEGER UNSIGNED NULL,
			  ws_date_update DATETIME NULL,
			  action VARCHAR(1) NULL,
			  PRIMARY KEY(id),
			  INDEX sync_adresses_FKIndex1(sync_id)
			);
    	');

    	// create sync shipping adress
	    Db::getInstance()->execute('
			CREATE TABLE  IF NOT EXISTS '._DB_PREFIX_.'sync_invoice_adresses (
			  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			  sync_id INTEGER UNSIGNED NOT NULL,
			  ws_id VARCHAR(12) NULL,
			  ps_id INTEGER UNSIGNED NULL,
			  ws_date_update DATETIME NULL,
			  action VARCHAR(1) NULL,
			  PRIMARY KEY(id),
			  INDEX sync_adresses_FKIndex1(sync_id)
			);
    	');

    	// create sync docs (=orders)
	    Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'sync_documents (
			  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			  sync_id INTEGER UNSIGNED NOT NULL,
			  ps_id INTEGER UNSIGNED NULL,
			  ws_id VARCHAR(31) NULL,
			  ws_date_update DATETIME NULL,
			  action VARCHAR(1) NULL,
			  PRIMARY KEY(id),
			  INDEX sync_documents_FKIndex1(sync_id)
			);
    	');

    	// create sync doc lines
	    Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'sync_document_lines (
			  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			  sync_id INTEGER UNSIGNED NOT NULL,
			  ps_id INTEGER UNSIGNED NULL,
			  ws_id VARCHAR(15) NULL,
			  ws_date_update DATETIME NULL,
			  action VARCHAR(1) NULL,
			  PRIMARY KEY(id),
			  INDEX sync_document_lines_FKIndex1(sync_id)
			);
    	');

    	// create order extra table
	    Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'order_extra (
			  id_order_extra INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			  id_order INTEGER UNSIGNED NULL,
			  id_order_detail INTEGER UNSIGNED NULL,
			  product_quantity INTEGER UNSIGNED NULL,
			  tracking_code VARCHAR(32) NULL,
			  ws_num_order VARCHAR(32) NULL,
			  ws_num_delivery VARCHAR(32) NULL,
			  ws_num_invoice VARCHAR(32) NULL,
			  delivery_date DATETIME NULL,
			  PRIMARY KEY(id_order_extra)
			);
    	');

    	// create sync doc extra
	    Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'sync_document_extra (
			  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			  sync_id INTEGER UNSIGNED NOT NULL,
			  ps_id INTEGER UNSIGNED NULL,
			  ws_id VARCHAR(32) NULL,
			  ws_date_update DATETIME NULL,
			  action VARCHAR(1) NULL,
			  PRIMARY KEY(id),
			  INDEX sync_document_extra_FKIndex1(sync_id)
			);
    	');

	    if(!Configuration::get('ID_STATE_PAYMENT_OK')){
	    	// create a new payment method : VALIDATE
		    Db::getInstance()->execute("
		    	INSERT INTO `samac`.`ps_order_state` (
					`id_order_state` ,
					`invoice` ,
					`send_email` ,
					`module_name` ,
					`color` ,
					`unremovable` ,
					`hidden` ,
					`logable` ,
					`delivery` ,
					`shipped` ,
					`paid` ,
					`pdf_invoice` ,
					`pdf_delivery` ,
					`deleted`
				)
				VALUES (
					NULL , '1', '0', 'sync_manager', '#46c300', '1', '0', '0', '0', '0', '1', '1', '0', '0'
				);

	    	");

	    	$idStatePaymentOK = Db::getInstance()->Insert_ID();

	    	Configuration::updateValue('ID_STATE_PAYMENT_OK', (int) $idStatePaymentOK);  

	    	// create a new payment method
		    Db::getInstance()->execute("
				INSERT INTO `samac`.`ps_order_state_lang` (
					`id_order_state` ,
					`id_lang` ,
					`name` ,
					`template`
				)
				VALUES (
					".$idStatePaymentOK.", '1', 'Paiement validé par SAMAC', ''
				);
	    	");
		}

	    if(!Configuration::get('ID_STATE_PAYMENT_WAITING')){

	    	// create a new payment method : WAITING
		    Db::getInstance()->execute("
		    	INSERT INTO `samac`.`ps_order_state` (
					`id_order_state` ,
					`invoice` ,
					`send_email` ,
					`module_name` ,
					`color` ,
					`unremovable` ,
					`hidden` ,
					`logable` ,
					`delivery` ,
					`shipped` ,
					`paid` ,
					`pdf_invoice` ,
					`pdf_delivery` ,
					`deleted`
					)
				VALUES (
					NULL , '1', '0', 'sync_manager', '#ecf700', '1', '0', '0', '0', '0', '0', '1', '0', '0'
				);

	    	");

	    	$idStatePaymentWaiting = Db::getInstance()->Insert_ID();

	    	Configuration::updateValue('ID_STATE_PAYMENT_WAITING', (int) $idStatePaymentWaiting);  

	    	// create a new payment method
		    Db::getInstance()->execute("
				INSERT INTO `samac`.`ps_order_state_lang` (
					`id_order_state` ,
					`id_lang` ,
					`name` ,
					`template`
					)
				VALUES (
					".$idStatePaymentWaiting.", '1', 'En attente de paiement', ''
				);
	    	");
	    }

	    if (!parent::install() || !$this->registerHook('dashboardZoneOne')|| !$this->registerHook('adminOrder')){
	        return false;
	    }
	    return true;
	}

	public function uninstall()
	{
	  return parent::uninstall();
	}

	public function hookDashboardZoneOne($params)
	{
	    $this->context->smarty->assign(array(
	        'dateLastSync' => Tools::displayDate('2016-08-12')));
	    return $this->display(__FILE__, 'dashboard_zone_one.tpl');
	}

	public function hookAdminOrder($params)
	{
		$this->context->smarty->assign('module_dir', $this->_path);
		$lstOrderExtra = OrderExtra::getByOrderId($params['id_order']);
		$this->context->smarty->assign('lstOrderExtra', $lstOrderExtra);
		return $this->context->smarty->fetch($this->local_path.'views/templates/hook/admin_order.tpl');
	}

	/**
	* Load the configuration form
	*/
	public function getContent()
	{
		/**
		* If values have been submitted in the form, process.
		*/
		$this->_postProcess();

		$this->context->smarty->assign('module_dir', $this->_path);

		$lastSyncs = Synchronization::getLastSynchronizations();
		$this->context->smarty->assign('lastSyncs', $lastSyncs);

		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');


		return $output.$this->renderForm();
	}

	public function getConfigForm()
	{
        $fields_form = array(
                'form' => array(
                        'id_form' => 'sync_manager_form',
                        'legend' => array(
                    		'title'=>'Lancer la synchronisation manuellement',
                        ),
                        'submit' => array(
                                'title' => $this->l('Start synchronization'),
                                'class' => 'btn btn-default submit_sync'
                        )
                ),
        );
                
        return $fields_form;
	}

	/**
	* Create the form that will be displayed in the configuration of your module.
	*/
	protected function renderForm()
	{
		$helper = new HelperForm();


		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$helper->module = $this;
		$helper->default_form_language = $this->context->language->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);


		$helper->identifier = $this->identifier;
		$helper->submit_action = 'proceedSync';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
		.'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');


		$helper->tpl_vars = array(
		'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
		'languages' => $this->context->controller->getLanguages(),
		'id_language' => $this->context->language->id,
		);

		return $helper->generateForm(array($this->getConfigForm()));
	}

	/**
	* Set values for the inputs.
	*/
	protected function getConfigFormValues()
	{
		return array(
			// 'txtWarning' => 'Attention'
		);
	}

	/**
	* Save form data.
	*/
	protected function _postProcess()
	{
		if (Tools::isSubmit('proceedSync')){
			//init db connector
			$db = new TestBddConnector();

			// create sync
			$sync = new Synchronization();
			$dt = new DateTime();
			$sync->date = $dt->format('Y-m-d H:i:s');
			$sync->method = 'MANUAL';
			$sync->state = 'PEND';
			$sync->save();
			//get products

			// try {
				//get date
				// --devonly--
				$dateLastSync = '2015-11-23';

				//categories
				$catLines = $db->getDistinctLines('EXT_WEB_ART','ART_DATEUPDATE',$dateLastSync,'ART_ASF');
				foreach ($catLines as $cl) {
					SyncCategory::proceedLineSync($cl,$sync);
				}
				//features
				//--families
				$featLines = $db->getDistinctLines('EXT_WEB_ART','ART_DATEUPDATE',$dateLastSync,'FAM_DESIGNATION');
				$feature = self::getFeatureByName("Famille");
				foreach ($featLines as $fl) {
					$fl['feature'] = $feature;
					SyncFeature::proceedLineSync($fl,$sync);
				}
				//--categories
				$featLines = $db->getDistinctLines('EXT_WEB_ART','ART_DATEUPDATE',$dateLastSync,'ART_CAT');
				$feature = self::getFeatureByName("Catégorie");
				foreach ($featLines as $fl) {
					$fl['feature'] = $feature;
					SyncFeature::proceedLineSync($fl,$sync);
				}
				//--natures
				$featLines = $db->getDistinctLines('EXT_WEB_ART','ART_DATEUPDATE',$dateLastSync,'ART_NAT');
				$feature = self::getFeatureByName("Nature");
				foreach ($featLines as $fl) {
					$fl['feature'] = $feature;
					SyncFeature::proceedLineSync($fl,$sync);
				}
				//--collection
				$featLines = $db->getDistinctLines('EXT_WEB_ART','ART_DATEUPDATE',$dateLastSync,'ART_COL');
				$feature = self::getFeatureByName("Collection");
				foreach ($featLines as $fl) {
					$fl['feature'] = $feature;
					SyncFeature::proceedLineSync($fl,$sync);
				}
				//products
				$prodLines = $db->getLines('EXT_WEB_ART','ART_DATEUPDATE',$dateLastSync);
				foreach ($prodLines as $pl) {
					SyncProduct::proceedLineSync($pl,$sync);
				}
				//companies
				$compLines = $db->getLines('EXT_WEB_CLI','CLI_DATEUPDATE',$dateLastSync);
				foreach ($compLines as $compLine) {
					SyncCompany::proceedLineSync($compLine,$sync);
				}
				//contacts
				$cusLines = $db->getLines('EXT_WEB_CON','CON_DATEUPDATE',$dateLastSync);
				foreach ($cusLines as $cusLine) {
					SyncCustomer::proceedLineSync($cusLine,$sync);
				}
				//prices
				$priceLines = $db->getLines('EXT_WEB_TARIF','ART_DATEUPDATE',$dateLastSync);
				foreach ($priceLines as $priceLine) {
					SyncPrice::proceedLineSync($priceLine,$sync);
				}
				//shipping addresses
				$saLines = $db->getLines('EXT_WEB_ADRLIV','ADR_DATEUPDATE',$dateLastSync);
				foreach ($saLines as $saLine) {
					SyncShippingAddress::proceedLineSync($saLine,$sync);
				}
				//invoice addresses
				$iaLines = $db->getLines('EXT_WEB_CLI','ADR_DATEUPDATE',$dateLastSync);
				foreach ($iaLines as $iaLine) {
					SyncInvoiceAddress::proceedLineSync($iaLine,$sync);
				}
				//orders
				$docLines = $db->getDocLines($dateLastSync);
				foreach ($docLines as $docLine) {
					SyncDoc::proceedLineSync($docLine,$sync);
				}
				//order lines
				$docLLines = $db->getLines('EXT_WEB_LDOC','LDOC_DATEUPDATE',$dateLastSync);
				foreach ($docLLines as $docLLine) {
					SyncDocLine::proceedLineSync($docLLine,$sync);
				}
				//order extra
				//getDocExtraLines : specific method that only gets lines from an edited order
				$docExtraLines = $db->getDocExtraLines();
				foreach ($docExtraLines as $docExtraLine) {
					//delete older entries
					SyncDocExtra::deleteOrderExtra($docExtraLine);
				}
				foreach ($docExtraLines as $docExtraLine) {
					//add new ones
					SyncDocExtra::proceedLineSync($docExtraLine,$sync);
				}
				//notify changes
				$db->notifyChanges();

				
				$sync->state = 'DONE';
				$sync->save();
				Logger::addLog('Synchronisation terminée !',1,null,'Synchronization',$sync->id);
			// } catch (Exception $e) {
				// $sync->state = 'FAIL';
				// $sync->save();
				// Logger::addLog('Erreur lors de la synchronisation : '.$e->getMessage(),3,null,'Synchronization',$sync->id);
			// }
		}
	}

	public static function getFeatureByName($name){
		$id_feature = (Db::getInstance()->getValue('SELECT id_feature FROM ps_feature_lang WHERE name LIKE "'.$name.'" AND id_lang = 1'));
		if ($id_feature) {
			$feature = new Feature($id_feature);
			return $feature;
		}else{
			$feature = new Feature();
			$feature->name[1] = $name;
			$feature->save();
			return $feature;
		}
	}

}