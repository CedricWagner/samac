<?php

class DDWTranslations extends DDWControllerCore
{

	public function renderList()
	{
		$translations = DDWTranslationsModel::getTranslations(Context::getContext()->shop->id, true);

		$fields_list = array(
			'name' => array(
				'title' => $this->l('Name'),
				'type' => 'text',
			),
			'type' => array(
				'title' => $this->l('Type'),
				'type' => 'text',
			)
		);
		$this->setupHelperList('Translations');

		$this->helper_list->identifier = 'id_translation';
		$this->helper_list->table = 'ddw_translations';
		$this->helper_list->show_toolbar = true;
		$this->helper_list->actions = array('edit');

		$return = '<br>';
		$return .= $this->helper_list->generateList($translations, $fields_list);
		return $return;
	}

	public function renderEditForm()
	{
		$ddw_translation = DDWTranslationsModel::getTranslation((int)Tools::getValue('id_translation'));

		if ($ddw_translation->type == 'text') $type = 'text';
			else $type = 'textarea';

		$this->setupHelperForm();
		$fields = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Edit Translation'),
					'icon' => 'icon-list'
				),
				'input' => array(
					array(
						'name' => 'id_translation',
						'type' => 'hidden'
					),
					array(
						'label' => $ddw_translation->name,
						'type' => $type,
						'autoload_rte' => true,
						'name' => 'text',
						'lang' => true,
						'required' => true
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
					'class' => 'btn btn-default pull-left',
					'icon' => 'icon-list',
					'name' => 'submit_',
				)
			),
		);
		$this->helper_form->currentIndex .= '&processTranslationEdit&id_translation='.(int)Tools::getValue('id_translation');

		if ($ddw_translation && $ddw_translation->id_translation != -1)
		{
			$this->helper_form->fields_value['id_translation'] = $ddw_translation->id_translation;
			$languages = $this->sibling->context->controller->getLanguages();

			foreach ($languages as $language)
				$this->helper_form->fields_value['text'][$language['id_lang']] = $ddw_translation->text_collection[$language['id_lang']];
		}
		return $this->helper_form->generateForm(array($fields));
	}

	public function processTranslationEditForm()
	{
		$ddw_translation = new TDDWTranslation();
		$ddw_translation->id_translation = Tools::getValue('id_translation');

		$languages = $this->sibling->context->controller->getLanguages();

		foreach ($languages as $language)
		{
			if (Tools::getIsset('text_'.$language['id_lang']))
				$ddw_translation->text_collection[$language['id_lang']] = Tools::getValue('text_'.$language['id_lang']);
			else
				$ddw_translation->text_collection[$language['id_lang']] = '';
		}
		DDWTranslationsModel::saveTranslationLang($ddw_translation);
	}

}
