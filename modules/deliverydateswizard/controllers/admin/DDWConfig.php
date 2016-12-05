<?php

class DDWConfigController extends DDWControllerCore
{
	private function renderCarrierList()
	{
		$fields = array(
			'id_carrier' => array(
				'title' => $this->l('ID'),
				'width' => 140,
				'type' => 'text',
			),
			'name' => array(
				'title' => $this->l('Name'),
				'width' => 140,
				'type' => 'text',
			)
		);

		$helper = new HelperList();
		$helper->shopLinkType = '';
		$helper->simple_header = false;
		$helper->token = Tools::getAdminTokenLite('AdminModules');

		$helper->actions = array('edit');

		$helper->identifier = 'id_carrier';
		$helper->show_toolbar = true;
		$helper->title = 'Carriers';
		$helper->table = 'carriers';

		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->sibling->name;
		$carrier_list = CarrierCore::getCarriers(Context::getContext()->language->id, false, false, false, null, CarrierCore::ALL_CARRIERS);

		$return = $helper->generateList($carrier_list, $fields);
		return $return;
	}

	public function renderMain()
	{
		$rendered = $this->renderCarrierList();
		$rendered .= $this->sibling->controller_translations->renderList();
		return $rendered;
	}

}