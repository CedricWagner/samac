<?php
class DDWDateRange extends DDWControllerCore
{
	public function processAddEditForm()
	{
		$date_blocked = new TDDWBlockedDate();
		$date_blocked->id_blockeddate = Tools::getValue('id_blockeddate');
		$date_blocked->recurring = Tools::getValue('recurring_on');
		$date_blocked->start_date = Tools::getValue('start_date');
		$date_blocked->end_date = Tools::getValue('end_date');
		$date_blocked->id_carrier = (int)Tools::getValue('id_carrier');
		$date_blocked->id_shop = (int)Context::getContext()->shop->id;
		DDWModel::saveBlockedDate($date_blocked);
		$this->redirect('&id_carrier='.Tools::getValue('id_carrier').'&updatecarriers');
	}

	public function processDeleteBlockedDate()
	{
		if (Tools::getValue('id_blockeddate') == '') return false;
		$ddw_blocked_date = DDWModel::getBlockedDate(Tools::getValue('id_blockeddate'));
		$id_blockeddate = Tools::getValue('id_blockeddate');
		DDWModel::deleteBlockedDate($id_blockeddate);
		$this->redirect('&id_carrier='.$ddw_blocked_date->id_carrier.'&updatecarriers');
	}

	public function renderAddEditForm()
	{
		$this->setupHelperForm();
		$fields = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Add a date or date range to block'),
					'icon' => 'icon-list'
				),
				'input' => array(
					array(
						'name' => 'id_blockeddate',
						'type' => 'hidden'
					),
					array(
						'name' => 'id_carrier',
						'type' => 'hidden'
					),
					array(
						'type' => 'checkbox',
						'name' => 'recurring',
						'values' => array(
							'query' => array(
								array(
									'id' => 'on',
									'name' => 'recurring',
									'val' => '1',
								),
							),
							'id' => 'id',
							'name' => 'name'
						)
					),
					array(
						'type' => 'date',
						'label' => 'from',
						'name' => 'start_date',
						'required' => true
					),
					array(
						'label' => 'to',
						'type' => 'date',
						'name' => 'end_date',
						'required' => true
					)
				),
				'submit' => array(
					'title' => $this->l('Save'),
					'class' => 'btn btn-default pull-left',
					'icon' => 'icon-list',
					'name' => 'submit_',
				)
			),
		);
		$this->helper_form->currentIndex .= '&processDDWDateRange&id_carrier='.(int)Tools::getValue('id_carrier');

		if (Tools::getValue('id_blockeddate') != '')
		{
			$blocked_date = DDWModel::getBlockedDate(Tools::getValue('id_blockeddate'));
			$this->helper_form->fields_value['id_blockeddate'] = $blocked_date->id_blockeddate;
			$this->helper_form->fields_value['id_carrier'] = $blocked_date->id_carrier;
			$this->helper_form->fields_value['start_date'] = $blocked_date->start_date;
			$this->helper_form->fields_value['end_date'] = $blocked_date->end_date;
			if ($blocked_date->recurring)
				$this->helper_form->fields_value['recurring_on'] = 1;
			else
				$this->helper_form->fields_value['recurring_on'] = 0;
		}
		else
		{
			$this->helper_form->fields_value['id_blockeddate'] = -1;
			$this->helper_form->fields_value['id_carrier'] = (int)Tools::getValue('id_carrier');
			$this->helper_form->fields_value['recurring_on'] = 0;
			$this->helper_form->fields_value['start_date'] = date('Y-m-d');
			$this->helper_form->fields_value['end_date'] = date('Y-m-d');
		}
		return $this->helper_form->generateForm(array($fields));
	}

}