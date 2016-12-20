<?php

if (!defined('_PS_VERSION_'))
  exit;

$class_folder = dirname(__FILE__).'/classes/';
require_once($class_folder.'Entity/Notification.php');
require_once($class_folder.'Entity/NotificationCustomer.php');


/**
* 
*/
class NotifCenter extends Module
{
	
	function __construct()
	{
		$this->name = 'notifcenter';
	    $this->tab = 'administration';
	    $this->version = '1.0';
	    $this->author = 'MKDN Groupe';
	    $this->need_instance = 0;
	    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6');
	    $this->dependencies = array();
	    $this->bootstrap = true;
	 
	    parent::__construct();
	 
	    $this->displayName = $this->l('Notif Center');
	    $this->description = $this->l('Permet l\'émission et l\'affichage de notifications');
	 
	    $this->confirmUninstall = $this->l('La suppression de ce module désactivera les notifications, confirmer ?');

	 
	}

	public function install()
	{
		// create main notif table
	    Db::getInstance()->execute('
	    	CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'notifications (
			  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			  content TEXT NULL,
			  level VARCHAR(1) NULL,
			  source VARCHAR(1) NULL,
			  date DATETIME NULL,
			  PRIMARY KEY(id)
			);		
    	');
		// create notif - customers table
	    Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'notification_customers (
			  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			  notif_id INTEGER UNSIGNED NOT NULL,
			  customer_id INTEGER UNSIGNED NULL,
			  is_seen TINYINT(1) UNSIGNED NULL,
			  PRIMARY KEY(id),
			  INDEX notification_customers_FKIndex1(notif_id)
			);	
    	');

	    if (!parent::install() || !$this->registerHook('dashboardZoneOne')){
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
	    $this->context->smarty->assign(array());
	    return $this->display(__FILE__, 'dashboard_zone_one.tpl');
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
		$lstNotifications = Notification::getLastNotifications();
		$this->context->smarty->assign('notifications', $lstNotifications);

		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');




		return $this->renderForm().$output;
		// return $output.$this->renderForm();
	}

	public function getConfigForm()
	{

		$customers = Customer::getCustomers(true);
		$aCustomers = array();
		foreach ($customers as $customer) {
			$aCustomers[] = array('id_option' => $customer['id_customer'],'name' => $customer['firstname'].' '.$customer['lastname']);
		}

        $fields_form = array(
                'form' => array(
                        'id_form' => 'add_notif_form',
                        'legend' => array(
                        		'title' => 'Nouvelle notification',
                        	),
                        'input' => array(
                        	array(
                        		'type'=>'textarea',
                        		'label'=>'Contenu',
                        		'name'=>'txtContent',
                        		'desc'=>'Le contenu de la notification qui sera affichée aux utilisateurs',
                        		'rows'=>8,
                        		'required'=>true,
                        		'is_bool'=>false,
                        	),
                        	array(
                        		'type'=>'radio',
                        		'label'=>'Cible',
                        		'name'=>'cbTarget',
                        		'desc'=>'La cible de votre notification',
                        		'values'=>array(
                        				array(
                        					'id'=>'target-all',
                        					'value'=>0,
                        					'label'=>'Collective',
                        				),
                        				array(
                        					'id'=>'target-ind',
                        					'value'=>1,
                        					'label'=>'Individuelle',
                        				),
                        			),
                        	),
                        	array(
                        		'type'=>'checkbox',
                        		'label'=>'Utilisateurs',
                        		'name'=>'cbCustomers',
                        		'desc'=>'Les utilisateurs qui recevront la notification (si individuelle)',
                        		'values'=>array(
                        				'query' => $aCustomers,
                        				'id' => 'id_option',
                        				'name' => 'name',
                        			),
                        	),
                        ),
                        'submit' => array(
                                'title' => $this->l('Enregistrer'),
                                'class' => 'btn btn-default'
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
			'txtContent' => 'Contenu de la notification',
			'cbTarget' => 'Cible'
		);
	}

	/**
	* Save form data.
	*/
	protected function _postProcess()
	{
		if (Tools::isSubmit('proceedSync')){
			
			$notification = new Notification();
			$notification->content = Tools::getValue('txtContent');
			$notification->date = (new DateTime())->format('Y-m-d H:i:s');
			if (Tools::getValue('cbTarget') == 0) {
				//collective
				$notification->level = 'C';
			}else{
				//individuel
				$notification->level = 'I';
			}

			$notification->source = 'C';

			if($notification->save()){
				if($notification->level == 'I'){
					$customers = Customer::getCustomers(true);
					foreach ($customers as $customer) {
						if(Tools::getValue('cbCustomers_'.$customer['id_customer'])){
							$nc = new NotificationCustomer();
							$nc->customer_id = $customer['id_customer'];
							$nc->notif_id = $notification->id;
							$nc->save();
						}
					}
				}
				$this->adminDisplayInformation('Notification envoyée !');
			}

		}
	}

}