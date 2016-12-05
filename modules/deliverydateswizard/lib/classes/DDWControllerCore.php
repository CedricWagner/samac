<?php
class DDWControllerCore extends Module
{
	protected $sibling;
	protected $helper_form;
	protected $helper_list;

	public function __construct($sibling)
	{
		$this->sibling = $sibling;
		parent::__construct();
	}

	/* Protected Methods */
	protected function redirect($url_params)
	{
		$url = AdminController::$currentIndex.'&configure='.$this->sibling->name.'&'.$url_params.'&token='.Tools::getAdminTokenLite('AdminModules');
		Tools::redirectAdmin($url);
	}

	protected function setupHelperForm()
	{
		$this->helper_form = new HelperForm();
		$this->helper_form->module = $this->sibling;
		$this->helper_form->identifier = $this->identifier;
		$this->helper_form->token = Tools::getAdminTokenLite('AdminModules');
		$this->helper_form->show_toolbar = false;
		$this->helper_form->submit_action = ""; // PS 1.5.4 adds submitAdd to the form action otherwise which breaks the module

		$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		foreach (Language::getLanguages(false) as $lang)
			$this->helper_form->languages[] = array(
				'id_lang' => $lang['id_lang'],
				'iso_code' => $lang['iso_code'],
				'name' => $lang['name'],
				'is_default' => ($id_lang == $lang['id_lang'] ? 1 : 0)
			);

		$this->helper_form->currentIndex = AdminController::$currentIndex.'&configure='.$this->sibling->name;
		$this->helper_form->default_form_language = $id_lang;
		$this->helper_form->allow_employee_form_lang = $id_lang;
		$this->helper_form->toolbar_scroll = true;
	}

	protected function setupHelperList($title)
	{
		$this->helper_list = new HelperList();
		$this->helper_list->shopLinkType = '';
		$this->helper_list->simple_header = true;
		$this->helper_list->actions = array('edit', 'delete');

		$this->helper_list->show_toolbar = true;
		$this->helper_list->title = $title;

		$this->helper_list->currentIndex = AdminController::$currentIndex.'&configure='.$this->sibling->name;
		$this->helper_list->token = Tools::getAdminTokenLite('AdminModules');
		return null;
	}

	protected function assignTranslations($translations_collection, $instance_smarty=null)
	{
		$id_language = Context::getContext()->language->id;
		foreach ($translations_collection as $ddw_translation)
		{
			$this->sibling->smarty->assign(array(
				$ddw_translation->name => $ddw_translation->text_collection[$id_language]
			));
		}
	}

}