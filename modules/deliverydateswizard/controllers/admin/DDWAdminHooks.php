<?php
class DDWAdminHooks extends DDWControllerCore
{

	public function renderOrderDetailBlock($id_order)
	{
		$order_ddw = DDWModel::getOrderDDWDateTime($id_order);
		if ((int)$order_ddw['ddw_order_date'] > 0)
			$order_ddw['ddw_order_date'] = date('Y-m-d', strtotime($order_ddw['ddw_order_date']));
		else
			$order_ddw['ddw_order_date'] = '';
		
		/*$this->setupHelperForm();
		$fields = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Delivery Date Wizard'),
					'icon' => 'icon-list'
				),
				'input' => array(
					array(
						'name' => 'id_order',
						'type' => 'hidden',
					),
					array(
						'name' => 'ddw_order_date',
						'type' => 'date',
						'label' => 'Delivery Date (YYYY-MM-DD)',
					),
					array(
						'name' => 'ddw_order_time',
						'type' => 'text',
						'label' => 'Time slot'
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
		//$this->helper_form->currentIndex .= '&processOrderDDWUpdate';
		$this->helper_form->currentIndex = 'index.php?controller=AdminModules&configure='.$this->sibling->name.'&processOrderDDWUpdate';
		$this->helper_form->token = Tools::getAdminTokenLite('AdminModules');
		$this->helper_form->fields_value['id_order'] = Tools::getValue('id_order');
		$this->helper_form->fields_value['ddw_order_date'] = $order_ddw['ddw_order_date'];
		$this->helper_form->fields_value['ddw_order_time'] = $order_ddw['ddw_order_time'];

		return $this->helper_form->generateForm(array($fields));*/
		$this->sibling->smarty->assign(array(
			'order_ddw' => $order_ddw,
			'base_url' => _PS_BASE_URL_.__PS_BASE_URI__
		));
		return $this->sibling->display($this->sibling->file, 'views/templates/admin/order_detail_block.tpl');
	}


	public function renderHookDisplayPDFInvoice($params)
	{
		$order_invoice = $params['object'];
		if (!($order_invoice instanceof OrderInvoice))
			return;
		$ddw_order = DDWModel::getOrderDDWDateTime($order_invoice->id_order);

		if ((int)$ddw_order['ddw_order_date'] > 0)
			$return = DDWTranslationsModel::getTranslationByName('date_invoice_label').':'.date('j F Y', strtotime($ddw_order['ddw_order_date'])).'<br>';
		else
			$return = '';

		if ($ddw_order['ddw_order_time'] != '')
			$return .= DDWTranslationsModel::getTranslationByName('time_invoice_label').':'.$ddw_order['ddw_order_time'];
		return $return;
	}

	public function renderHookDisplayPDFDeliverySlip($params)
	{
		return $this->renderHookDisplayPDFInvoice($params);
	}

	public function processOrderDDWUpdate()
	{
		if (Tools::getIsset('id_order'))
			DDWModel::saveToOrderDirect(Tools::getValue('id_order'), Tools::getValue('ddw_order_date'), Tools::getValue('ddw_order_time'));
		$redirect_url = '?controller=AdminOrders&id_order='.(int)Tools::getValue('id_order').'&vieworder&token='.Tools::getAdminTokenLite('AdminOrders');
		Tools::redirectAdmin($redirect_url);
	}

}