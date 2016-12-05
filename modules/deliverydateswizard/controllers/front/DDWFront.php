<?php
class DDWControllerFront extends DDWControllerCore
{
	/** @var DDWModel */
	public $ddw_model;

	public function __construct(&$sibling = null)
	{
		parent::__construct($sibling);
		if ($sibling !== null)
		{
			$this->sibling = &$sibling;
			if (!empty($this->sibling->context->controller))
			{
				$this->sibling->context->controller->addJS($sibling->_path.'views/js/front/ddw.js');
				$this->sibling->context->controller->addCSS($this->sibling->_path.'views/css/front/deliverydateswizard.css');
			}
		}
	}

	/**
	 * @var $blocked_dates Array Of TDDWBlockedDate
	 * @var $date timestamp
	 * @return boolean
	 */
	private function isDateBlocked($blocked_dates, $date)
	{
		$blocked = false;

		/* Weekdays Blocked check */
		$current_week_day = date('w', $date);
		/*print '<pre>';
		print_r($this->ddw_model->ddw->weekdays);
		print '</pre>';
		print $current_week_day."\n";*/
		if (in_array($current_week_day, explode(',', $this->ddw_model->ddw->weekdays))) return true;
		if (!is_array($blocked_dates)) return false;

		foreach ($blocked_dates as $blocked_date)
		{
			if ($blocked_date->recurring == 0)
			{
				$y = date('Y', strtotime($blocked_date->start_date));
				$m = date('m', strtotime($blocked_date->start_date));
				$d = date('d', strtotime($blocked_date->start_date));
				$blocked_date->date_end = date('Y-m-d H:i:s', strtotime("$y-$m-$d 23:59:59"));
			}
			$timestamp_start = strtotime($blocked_date->start_date);
			$timestamp_end = strtotime($blocked_date->end_date);

			if ($date >= $timestamp_start && $date <= $timestamp_end)
				$blocked = true;

			/* Recurring block check */
			if ($blocked_date->recurring == 1)
			{
				$recurring_timestamp_start = date('Y-m-d H:i:s', strtotime(
					date('Y').'-'.date('m', $timestamp_start).'-'.date('d', $timestamp_start).' 00:00:00'
				));
				$recurring_timestamp_end = date('Y-m-d H:i:s', strtotime(
					date('Y').'-'.date('m', $timestamp_end).'-'.date('d', $timestamp_end).' 23:59:00'
				));
				if ($date >= $recurring_timestamp_start && $date <= $recurring_timestamp_end) $blocked = true;
			}
		}
		return $blocked;
	}

	public function get_blocked_dates()
	{
		$id_carrier = (int)Tools::getValue('id_carrier');
		$min_days = 0;
		$start_date = date('Y-m-d');
		$calendar_blocked_dates = array(); //of TDDWCalendarBlockedDates

		$this->ddw_model = new DDWModel();
		$this->ddw_model->load($id_carrier);

		$min_days = $this->ddw_model->ddw->min_days;
		$blocked_dates = $this->ddw_model->getBlockedDates($id_carrier, false, $this->context->shop->id);

		$today_is_blocked = $this->isDateBlocked($blocked_dates, time());

		/* Determine if cut off time requires Min Days to be blocked from today onwards  */
		if ($this->ddw_model->ddw->cutofftime_enabled == 1 && !$today_is_blocked)
		{
			$hours = date('H');
			$minutes = date('i');
			if ($hours > $this->ddw_model->ddw->cutofftime_hours)
				$min_days ++;
			elseif ($hours == $this->ddw_model->ddw->cutofftime_hours && $minutes > $this->ddw_model->ddw->cutofftime_minutes)
				$min_days ++;
		}

		/* If any of the days within min days falls on a block date, increment min days accordingly */
		//$today_is_blocked = false;
		$j = $min_days;
		for ($i = 0; $i < $j; $i++)
		{
			$loop_date = strtotime("+$i day", strtotime($start_date));
			$today = date('Y-m-d');
			$loop_date_compare = date('Y-m-d', $loop_date);

			/* if today is blocked and cut off time, do not add today to min days offset */
			if ($today == $loop_date_compare && $today_is_blocked)
				continue;
			else
				if ($this->isDateBlocked($blocked_dates, $loop_date)) $min_days++;
		}

		/* Block all days up to min_days from order date (today) */
		for ($i = 0; $i < $min_days; $i++)
		{
			$loop_date = strtotime("+$i day", strtotime($start_date));
			$calendarBlockedDate = new TDDWCalendarBlockedDate();
			$calendarBlockedDate->date = date('Y-m-d', $loop_date);
			$calendarBlockedDate->blocked = true;
			$calendar_blocked_dates[] = $calendarBlockedDate;
		}
		
		/* Fix bug which caused last day of max date to become selectable when it fell within a blocked date range */
		$last_max_day_blocked =  $this->isDateBlocked($blocked_dates, strtotime('+'.$this->ddw_model->ddw->max_days.' days'));
		if ($last_max_day_blocked)
		{
			$calendarBlockedDate = new TDDWCalendarBlockedDate();
			$calendarBlockedDate->date = date('Y-m-d', strtotime('+'.$this->ddw_model->ddw->max_days.' days'));
			$calendarBlockedDate->blocked = true;
			$calendar_blocked_dates[] = $calendarBlockedDate;
		}
		

		/* Loop through dates */
		if ($this->ddw_model->ddw->max_days == 0) $this->ddw_model->ddw->max_days = 365;
		for ($i = 0; $i < $this->ddw_model->ddw->max_days; $i++)
		{
			$loop_date = strtotime("+$i day", strtotime($start_date));
			if ($this->isDateBlocked($blocked_dates, $loop_date))
			{
				$calendarBlockedDate = new TDDWCalendarBlockedDate();
				$calendarBlockedDate->date = date('Y-m-d', $loop_date);
				$calendarBlockedDate->blocked = true;
				$calendar_blocked_dates[] = $calendarBlockedDate;
			} 
		}

		/* If enabled/disabled */
		if ($this->ddw_model->ddw->enabled != 1)
			$calendar_blocked_dates = array();

		/* Return time slots for the selected carrier */
		$timeslots = DDWModel::getTimeSlots(Tools::getValue('id_carrier'), $this->context->language->id, Context::getContext()->shop->id, false);
		
		/* Create return data */
		$return = array();
		$return['min_date'] = date('Y-m-d');
		$return['max_date'] = date('Y-m-d', strtotime('+'.$this->ddw_model->ddw->max_days.' days'));
		$return['calendar_blocked_dates'] = $calendar_blocked_dates;
		$return['timeslots'] = $timeslots;

		$return['enabled'] = $this->ddw_model->ddw->enabled;
		$return['required'] = $this->ddw_model->ddw->required;

		/* get first available day for delivery, allowing the calendar to change months if necessary */
		$start_date = date('Y-m-d');
		$haystack = array();
		foreach ($calendar_blocked_dates as $date_obj)
			$haystack[] = $date_obj->date;

		$loop_date = $start_date;
		for ($i = 0; $i < 9999; $i++) {
			$loop_date = date('Y-m-d', strtotime("+$i day", strtotime($start_date)));
			if (!in_array($loop_date, $haystack))
				break;
		}
		$return['defaults']['calendar_default_day'] = date('d', strtotime($loop_date));
		$return['defaults']['calendar_default_month'] = date('m', strtotime($loop_date));
		$return['defaults']['calendar_default_year'] = date('Y', strtotime($loop_date));
		return $return;
	}

	public function update_ddw_cart()
	{
		if (Context::getContext()->cart->id != '')
			DDWModel::saveToCart(Tools::getValue('ddw_date'), Tools::getValue('ddw_time'), Context::getContext()->cart->id);
	}

	public function getLastDDWCart()
	{
		if (Context::getContext()->cart->id != '')
			return DDWModel::getCartDDWDateTime(Context::getContext()->cart->id);
	}

	public function renderFrontWidget()
	{
		$ddw_translations = DDWTranslationsModel::getTranslations(Context::getContext()->shop->id, false);
		$this->assignTranslations($ddw_translations);
		$this->sibling->smarty->assign(array(
			'controller_name' => Context::getContext()->controller->php_self
		));
		return $this->sibling->display($this->sibling->file, 'views/templates/front/widget.tpl');
	}
}