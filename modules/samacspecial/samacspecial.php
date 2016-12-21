<?php

if (!defined('_PS_VERSION_'))
  exit;

$class_folder = dirname(__FILE__).'/classes/';


/**
* 
*/
class SamacSpecial extends Module
{
	
	function __construct()
	{
		$this->name = 'samacspecial';
	    $this->tab = 'administration';
	    $this->version = '1.0';
	    $this->author = 'MKDN Groupe';
	    $this->need_instance = 0;
	    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6');
	    $this->dependencies = array();
	    $this->bootstrap = true;
	 
	    parent::__construct();
	 
	    $this->displayName = $this->l('Samac');
	    $this->description = $this->l('Modifie Prestashop et surcharge certaines fonctions de base');
	 
	    $this->confirmUninstall = $this->l('La suppression de ce module désactivera les fonctionnalités spécifiques à SAMAC (peut causer des dysfonctionnements), confirmer ?');

	 
	}

	public function install()
	{
	    if (!parent::install()){
	        return false;
	    }
	    return true;
	}

	public function uninstall()
	{
	  return parent::uninstall();
	}


}