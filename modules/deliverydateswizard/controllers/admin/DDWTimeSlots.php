<?php
class DDWTimeSlots extends DDWControllerCore
{

	public function renderTimeSlotForm()
	{
		$this->setupHelperForm();
		$fields = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Add a time slot'),
					'icon' => 'icon-list'
				),
				'input' => array(
					array(
						'name' => 'id_timeslot',
						'type' => 'hidden'
					),
					array(
						'name' => 'id_carrier',
						'type' => 'hidden'
					),
					array(
						'label' => 'time slot',
						'type' => 'text',
						'name' => 'time_slot',
						'lang' => true,
						'required' => true
					),
					array(
						'label' => 'position',
						'type' => 'text',
						'name' => 'position',
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
		$this->helper_form->currentIndex .= '&processDDWTimeSlotAdd&id_carrier='.(int)Tools::getValue('id_carrier');

		$timeslot = new TDDWTimeSlot();
		if (Tools::getValue('id_timeslot') != '')
			$timeslot = DDWModel::getTimeSlot(Tools::getValue('id_timeslot'), Context::getContext()->language->id);

		$languages = $this->sibling->context->controller->getLanguages();
		/* Populate the form */
		if (isset($timeslot->id_timeslot) && $timeslot->id_timeslot > -1)
		{
			$this->helper_form->fields_value['id_timeslot'] = $timeslot->id_timeslot;
			$this->helper_form->fields_value['id_carrier'] = $timeslot->id_carrier;
			$this->helper_form->fields_value['position'] = $timeslot->position;
			foreach ($languages as $language)
			{
				if (isset($timeslot->time_slots[$language{'id_lang'}]))
					$this->helper_form->fields_value['time_slot'][$language{'id_lang'}] = $timeslot->time_slots[$language{'id_lang'}];
				else
					$this->helper_form->fields_value['time_slot'][$language{'id_lang'}] = '';
			}
		}
		else
		{
			$this->helper_form->fields_value['id_timeslot'] = -1;
			$this->helper_form->fields_value['id_carrier'] = (int)Tools::getValue('id_carrier');
			$this->helper_form->fields_value['position'] = '';
			foreach ($languages as $language)
				$this->helper_form->fields_value['time_slot'][$language{'id_lang'}] = '';
		}
		return $this->helper_form->generateForm(array($fields));
	}
	
	public function processTimeSlotForm()
	{
		$time_slot = new TDDWTimeSlot();
		$time_slot->id_carrier = (int)Tools::getValue('id_carrier');
		$time_slot->id_shop = Context::getContext()->shop->id;
		$time_slot->position = (int)Tools::getValue('position');

		if ((int)Tools::getValue('id_timeslot') > -1) $time_slot->id_timeslot = (int)Tools::getValue('id_timeslot');

		$languages = $this->sibling->context->controller->getLanguages();

		foreach ($languages as $language)
			$time_slot->time_slots[$language{'id_lang'}] = Tools::getValue('time_slot_'.$language['id_lang']);

		DDWModel::saveTimeSlot($time_slot);
		$this->redirect('&id_carrier='.$time_slot->id_carrier.'&updatecarriers');
	}

	public function processDeleteTimeSlotForm()
	{
		$id_timeslot = Tools::getValue('id_timeslot');
		if ($id_timeslot == '') return false;
		$ddw_timeslot = DDWModel::getTimeSlot(Tools::getValue('id_timeslot'), Context::getContext()->language->id);
		DDWModel::deleteTimeSlot($id_timeslot);
		$this->redirect('&id_carrier='.$ddw_timeslot->id_carrier.'&updatecarriers');
	}

}