<?php

class TDDWBlockedDate
{
	public $id_blockeddate = -1;
	public $id_carrier = -1;
	public $id_shop = -1;
	public $recurring = false;
	public $start_date = "";
	public $end_date = "";
}

class TDDWCalendarBlockedDate {
	public
		$date = "",
		$blocked = false;
}

class TDDWTimeSlot
{
	public $id_timeslot = -1;
	public $id_carrier = -1;
	public $id_shop = -1;
	public $position = -1;
	public $time_slots = array(); //Array of strings
}

class TDDW
{
	public $id_ddw = -1;
	public $id_carrier = -1;
	public $required = 0;
	public $enabled = 0;
	public $min_days = 0;
	public $max_days = 0;
	public $cutofftime_enabled = false;
	public $cutofftime_hours = 0;
	public $cutofftime_minutes = 0;
	public $weekdays = "";

	/** @var TDDWDate[] */
	public $datesCollection = array();

	/** @var DDW_Text[] */
	public $translations;

	/** @var DDW_Time_Slot[] */
	public $delivery_times = array();
}

class TDDWCarrier
{
	public $id_ddw = -1;
	public $id_carrier = -1;
	public $enabled = false;
	public $required = false;
	public $weekdays = array(); //array of integers
	public $min_days = -1;
	public $max_days = -1;
	public $cutofftime_enabled = 0;
	public $cutofftime_hours = 0;
	public $cutofftime_minutes = 0;
}



class TDDWDate {
	public function formatDateString($strDate, $time="00:00:00") {
		//$objDate = DateTime::createFromFormat('Y-m-d H:i:s', $strDate.' '.$time);
		//return $objDate->format('Y-m-d H:i:s');
		$objDate   = date('Y-m-d', strtotime($strDate))." $time";
		return $objDate;
	}

	public function unformatDateString($strDateTime) {
		$objDate   = date('Y-m-d', strtotime($strDateTime));
		return $objDate;
		//$objDate = DateTime::createFromFormat('Y-m-d H:i:s', $strDateTime);
		//return $objDate->format('Y-m-d');
	}
}



