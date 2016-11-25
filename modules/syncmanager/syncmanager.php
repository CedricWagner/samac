<?php

if (!defined('_PS_VERSION_'))
  exit;

$class_folder = dirname(__FILE__).'/classes/';
require_once($class_folder.'/Entity/Synchronization.php');

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
	    Db::getInstance()->execute('
	    	CREATE TABLE IF NOT EXISTS synchronizations (
			  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			  date DATETIME NULL,
			  method VARCHAR(12) NULL,
			  state VARCHAR(10) NULL,
			  PRIMARY KEY(id)
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
	    $this->context->smarty->assign(array(
	        'dateLastSync' => Tools::displayDate('2016-08-12')));
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

		$lastSyncs = array();
		$result = Db::getInstance()->executeS('SELECT id FROM '._DB_PREFIX_.'synchronizations ORDER BY date DESC LIMIT 10');
		foreach ($result as $aSync) {
			$lastSyncs[] = new Synchronization($aSync['id']);
		}

		$this->context->smarty->assign('lastSyncs', $lastSyncs);

		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');


		return $output.$this->renderForm();
	}

	public function getConfigForm()
	{
        $fields_form = array(
                'form' => array(
                        'id_form' => 'sync_manager_form',
                        'input' => array(
                        	array(
                        		'type'=>'label',
                        		'label'=>'Lancer la synchronisation manuellement',
                        		'name'=>'txtWarning',
                        		'is_bool'=>false,
                        	),
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

		}
	}

}