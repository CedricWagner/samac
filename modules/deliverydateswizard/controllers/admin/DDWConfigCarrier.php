<?php
class DDWConfigCarrierController extends DDWControllerCore
{

	/** @var TDDWCarrier DDW Carrier */
	public $ddw_carrier;

	private function generalForm()
	{
		$this->setupHelperForm();

		$fields = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('General Settings'),
					'icon' => 'icon-list'
				),
				'input' => array(
					array(
						'name' => 'id_carrier',
						'type' => 'hidden'
					),
					array(
						'type' => 'checkbox',
						'name' => 'enabled',
						'values' => array(
							'query' => array(
								array(
									'id' => 'on',
									'name' => 'enabled',
									'val' => '1',
								),
							),
							'id' => 'id',
							'name' => 'name'
						)
					),
					array(
						'type' => 'checkbox',
						'name' => 'required',
						'values' => array(
							'query' => array(
								array(
									'id' => 'on',
									'name' => 'required',
									'val' => '1',
								),
							),
							'id' => 'id',
							'name' => 'name'
						)
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
		$this->helper_form->currentIndex .= '&processDDWCarrierGeneral&id_carrier='.(int)Tools::getValue('id_carrier');

		/* Populate the form */
		if (isset($this->ddw_carrier->id_carrier))
		{
			$this->helper_form->fields_value['id_carrier'] = (int)Tools::getValue('id_carrier');
			$this->helper_form->fields_value['enabled_on'] = $this->ddw_carrier->enabled;
			$this->helper_form->fields_value['required_on'] = $this->ddw_carrier->required;
		}
		else
		{
			$this->helper_form->fields_value['id_carrier'] = (int)Tools::getValue('id_carrier');
			$this->helper_form->fields_value['enabled_on'] = 0;
			$this->helper_form->fields_value['required_on'] = 0;
		}
		return $this->helper_form->generateForm(array($fields));
	}

	public function weekdaysForm()
	{
		$this->setupHelperForm();

		$fields = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Blocked Weekdays'),
					'icon' => 'icon-list'
				),
				'input' => array(
					array(
						'name' => 'id_carrier',
						'type' => 'hidden'
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
		$this->helper_form->currentIndex .= '&processDDWCarrierWeekdays&id_carrier='.(int)Tools::getValue('id_carrier');

		/* Populate the form */
		$this->helper_form->fields_value['id_carrier'] = (int)Tools::getValue('id_carrier');

		$weekday_map = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
		for ($x = 0; $x <= 6; $x++)
		{
			$fields['form']['input'][] = array(
					'type' => 'checkbox',
					'name' => 'weekday',
					'values' => array(
						'query' => array(
							array(
								'id' => date('w', strtotime($weekday_map[$x])),
								'name' => $weekday_map[$x],
								'val' => '1',
							),
						),
						'id' => 'id',
						'name' => 'name'
					)
				);

		}

		if (isset($this->ddw_carrier->id_carrier))
		{
			$this->helper_form->fields_value['id_carrier'] = (int)Tools::getValue('id_carrier');
			if (count($this->ddw_carrier->weekdays) > 0)
			{
				foreach ($this->ddw_carrier->weekdays as $weekday)
					$this->helper_form->fields_value['weekday_'.$weekday] = '1';
			}
		}
		return $this->helper_form->generateForm(array($fields));
	}

	public function renderMinMaxDaysForm()
	{
		$this->setupHelperForm();

		$cutofftime_hours_options = array();
		$cutofftime_minutes_options = array();
		$option = array();
		for ($x = 0; $x <= 23; $x++)
		{
			$option['id_option'] = $x;
			$option['name'] = str_pad($x, 2, '0', STR_PAD_LEFT);
			$cutofftime_hours_options[] = $option;
		}

		for ($x = 0; $x <= 59; $x++)
		{
			$option['id_option'] = $x;
			$option['name'] = str_pad($x, 2, '0', STR_PAD_LEFT);
			$cutofftime_minutes_options[] = $option;
		}

		$fields = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Min / Max Days'),
					'icon' => 'icon-list'
				),
				'input' => array(
					array(
						'name' => 'id_carrier',
						'type' => 'hidden'
					),
					array(
						'label' => 'Min Days',
						'type' => 'text',
						'name' => 'min_days',
						'class' => 'fixed-width-xxl',
						'size' => 6
					),
					array(
						'label' => 'Max Days',
						'type' => 'text',
						'name' => 'max_days',
						'class' => 'fixed-width-xxl',
						'size' => 6
					),
					array(
						'type' => 'checkbox',
						'name' => 'cutofftime_enabled',
						'values' => array(
							'query' => array(
								array(
									'id' => 'on',
									'name' => 'cutofftime_enabled',
									'val' => '1',
								),
							),
							'id' => 'id',
							'name' => 'name'
						)
					),
					array(
						'type' => 'select',
						'label' => 'Cut off hours',
						'name' => 'cutofftime_hours',
						'required' => true,
						'options' => array(
							'query' => $cutofftime_hours_options,
							'id' => 'id_option',
							'name' => 'name'
						)
					),
					array(
						'type' => 'select',
						'label' => 'Cut off minutes',
						'name' => 'cutofftime_minutes',
						'required' => true,
						'options' => array(
							'query' => $cutofftime_minutes_options,
							'id' => 'id_option',
							'name' => 'name'
						)
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
		$this->helper_form->currentIndex .= '&processMinMaxForm&id_carrier='.(int)Tools::getValue('id_carrier');

		/* Populate the form */
		if (isset($this->ddw_carrier->id_carrier))
		{
			$this->helper_form->fields_value['id_carrier'] = (int)Tools::getValue('id_carrier');
			$this->helper_form->fields_value['min_days'] = $this->ddw_carrier->min_days;
			$this->helper_form->fields_value['max_days'] = $this->ddw_carrier->max_days;
			$this->helper_form->fields_value['cutofftime_enabled_on'] = $this->ddw_carrier->cutofftime_enabled;
			$this->helper_form->fields_value['cutofftime_hours'] = $this->ddw_carrier->cutofftime_hours;
			$this->helper_form->fields_value['cutofftime_minutes'] = $this->ddw_carrier->cutofftime_minutes;
		}
		else
		{
			$this->helper_form->fields_value['id_carrier'] = (int)Tools::getValue('id_carrier');
			$this->helper_form->fields_value['enabled_on'] = 0;
			$this->helper_form->fields_value['required_on'] = 0;
			$this->helper_form->fields_value['min_days'] = 0;
			$this->helper_form->fields_value['max_days'] = 0;			
			$this->helper_form->fields_value['cutofftime_enabled_on'] = 0;
			$this->helper_form->fields_value['cutofftime_hours'] = 0;
			$this->helper_form->fields_value['cutofftime_minutes'] = 0;
		}
		return $this->helper_form->generateForm(array($fields));
	}

	public function renderBlockedDatesList()
	{
		$blocked_dates = DDWModel::getBlockedDates(Tools::getValue('id_carrier'), true);

		$fields_list = array(
			'recurring' => array(
				'title' => $this->l('Recurring'),
				'width' => 40,
				'type' => 'text',
			),
			'start_date' => array(
				'title' => $this->l('From'),
				'type' => 'text',
			),
			'end_date' => array(
				'title' => $this->l('To'),
				'type' => 'text',
			),
		);

		$this->setupHelperList('Blocked dates / date ranges');

		$this->helper_list->identifier = 'id_blockeddate';
		$this->helper_list->table = 'ddw_blocked_dates';
		$this->helper_list->show_toolbar = true;
		$this->helper_list->simple_header = false;
		$this->helper_list->toolbar_btn = array(
			'new' => array(
				'desc' => $this->l('Insert Date / Date Range'),
				'href' => $this->helper_list->currentIndex.'&adddateform&id_carrier='.(int)Tools::getValue('id_carrier').'&token='.$this->helper_list->token,
			)
		);
		$return = '<br>';
		$return .= $this->helper_list->generateList($blocked_dates, $fields_list);
		return $return;
	}

	public function renderTimeSlotsList()
	{
		$time_slots = DDWModel::getTimeSlots(Tools::getValue('id_carrier'), Context::getContext()->language->id, Context::getContext()->shop->id, true);

		$fields_list = array(
			'time_slot' => array(
				'title' => $this->l('Time Slot'),
				'type' => 'text',
			),
			'position' => array(
				'title' => $this->l('Position'),
				'type' => 'text',
			)
		);

		$this->setupHelperList('Time Slots');

		$this->helper_list->identifier = 'id_timeslot';
		$this->helper_list->table = 'ddw_timeslots';
		$this->helper_list->show_toolbar = true;
		$this->helper_list->simple_header = false;

		$this->helper_list->toolbar_btn = array(
			'new' => array(
				'desc' => $this->l('Insert Time Slot'),
				'href' => $this->helper_list->currentIndex.'&addtimeslotform&id_carrier='.(int)Tools::getValue('id_carrier').'&token='.$this->helper_list->token,
			)
		);
		$return = '<br>';
		$return .= $this->helper_list->generateList($time_slots, $fields_list);
		return $return;
	}

	public function renderMain()
	{
		$this->ddw_carrier = DDWModel::getDDWCarrier(Tools::getValue('id_carrier'));
		$final_render = $this->generalForm();
		$final_render .= $this->weekdaysForm();
		$final_render .= $this->renderMinMaxDaysForm();
		$final_render .= $this->renderBlockedDatesList();
		$final_render .= $this->renderTimeSlotsList();
		return $final_render;
	}

	public function processGeneralForm()
	{
		$ddw_carrier = new TDDWCarrier();
		$ddw_carrier->id_carrier = Tools::getValue('id_carrier');
		$ddw_carrier->enabled = Tools::getValue('enabled_on');
		$ddw_carrier->required = Tools::getValue('required_on');
		DDWModel::saveGeneric($ddw_carrier);
		$this->redirect('&id_carrier='.Tools::getValue('id_carrier').'&updatecarriers');
	}

	public function processWeekdaysForm()
	{
		$ddw_carrier = new TDDWCarrier();
		$ddw_carrier->id_carrier = Tools::getValue('id_carrier');

		for ($x = 0; $x <= 6; $x++)
		{
			if (Tools::getValue('weekday_'.$x) != '')
				$ddw_carrier->weekdays[] = $x;
		}
		
		/*foreach ($_POST as $key => $value)
		{
			if (strpos($key, 'weekday_') !== false) {
				$arr_parts = explode('_', $key);
				$ddw_carrier->weekdays[] = end($arr_parts);
			}
		}*/

		DDWModel::saveWeekdays($ddw_carrier);
		$this->redirect('&id_carrier='.Tools::getValue('id_carrier').'&updatecarriers');
		return true;
	}

	public function processMinMaxForm()
	{
		$ddw_carrier = DDWModel::getDDWCarrier(Tools::getValue('id_carrier'));
		$ddw_carrier->min_days = Tools::getValue('min_days');
		$ddw_carrier->max_days = Tools::getValue('max_days');
		$ddw_carrier->cutofftime_enabled = Tools::getValue('cutofftime_enabled_on');
		$ddw_carrier->cutofftime_hours = Tools::getValue('cutofftime_hours');
		$ddw_carrier->cutofftime_minutes = Tools::getValue('cutofftime_minutes');
		DDWModel::saveGeneric($ddw_carrier);
		$this->redirect('&id_carrier='.Tools::getValue('id_carrier').'&updatecarriers');
	}

}
