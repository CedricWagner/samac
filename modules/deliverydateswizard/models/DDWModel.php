<?php
if (!defined('_PS_VERSION_'))
	exit;

class DDWModel
{
	/** @var TDDW */
	public $ddw;

	public function load($id_carrier)
	{
		$sql = 'SELECT
					id_ddw,
					id_carrier,
					required,
					enabled,
					weekdays,
					min_days,
					max_days,
					cutofftime_enabled,
					cutofftime_hours,
					cutofftime_minutes
				FROM '._DB_PREFIX_.'ddw
				WHERE id_carrier = '.$id_carrier;
		$result = Db::getInstance()->getRow($sql);
		
		$this->ddw = new TDDW();

		if (isset($result) && is_array($result))
		{
			$this->ddw->id_ddw = $result['id_ddw'];
			$this->ddw->id_carrier = $result['id_carrier'];
			$this->ddw->enabled = (int)$result['enabled'];
			$this->ddw->required = (int)$result['required'];
			$this->ddw->min_days = (int)$result['min_days'];
			$this->ddw->max_days = (int)$result['max_days'];
			$this->ddw->cutofftime_enabled = (int)$result['cutofftime_enabled'];
			$this->ddw->cutofftime_hours = (int)$result['cutofftime_hours'];
			$this->ddw->cutofftime_minutes = (int)$result['cutofftime_minutes'];
			$this->ddw->weekdays = $result['weekdays'];

			/* Load the Blocked Dates */
			$this->ddw->datesCollection = $this->getBlockedDates($this->ddw->id_carrier, false, Context::getContext()->shop->id);
		}
	}

	/**
	 * @param integer $id_carrier
	 * @return TDDWCarrier
	 */
	public static function getDDWCarrier($id_carrier)
	{
		$sql = 'SELECT
					id_ddw,
					id_carrier,
					enabled,
					required,
					weekdays,
					min_days,
					max_days,
					cutofftime_enabled,
					cutofftime_hours,
					cutofftime_minutes
				FROM '._DB_PREFIX_.'ddw WHERE id_carrier = '.(int)$id_carrier.'
				';
		$result = Db::getInstance()->getRow($sql);

		if (isset($result) && is_array($result))
		{
			$ddw_carrier = new TDDWCarrier();
			$ddw_carrier->id_ddw = $result['id_ddw'];
			$ddw_carrier->id_carrier = $result['id_carrier'];
			$ddw_carrier->enabled = $result['enabled'];
			$ddw_carrier->required = $result['required'];
			$ddw_carrier->min_days = $result['min_days'];
			$ddw_carrier->max_days = $result['max_days'];
			$ddw_carrier->cutofftime_enabled = $result['cutofftime_enabled'];
			$ddw_carrier->cutofftime_hours = $result['cutofftime_hours'];
			$ddw_carrier->cutofftime_minutes = $result['cutofftime_minutes'];

			if ($result['weekdays'] != '')
				$ddw_carrier->weekdays = explode(',', $result['weekdays']);

			return $ddw_carrier;
		}
		else return false;
	}

	protected static function DDWCarrierCreateIfNotExists($id_carrier, $create_if_not_exists = true)
	{
		$sql = 'SELECT COUNT(*) AS total_count FROM '._DB_PREFIX_.'ddw WHERE id_carrier='.(int)$id_carrier;
		$count_exists = Db::getInstance()->getValue($sql);

		if ($count_exists == 0)
		{
			if ($create_if_not_exists)
			{
				Db::getInstance()->insert('ddw', array(
					'id_carrier' => (int)$id_carrier
				));
				return Db::getInstance()->Insert_ID();
			}
			else return -1;
		}
	}

	public static function saveGeneric(TDDWCarrier $ddw_carrier, $update_only = false)
	{
		/* Create new DDW entry */
		if (!$update_only) self::DDWCarrierCreateIfNotExists($ddw_carrier->id_carrier);

		Db::getInstance()->update('ddw', array(
				'enabled' => (int)$ddw_carrier->enabled,
				'required' => (int)$ddw_carrier->required,
				'min_days' => (int)$ddw_carrier->min_days,
				'max_days' => (int)$ddw_carrier->max_days,
				'cutofftime_enabled' => (int)$ddw_carrier->cutofftime_enabled,
				'cutofftime_hours' => (int)$ddw_carrier->cutofftime_hours,
				'cutofftime_minutes' => (int)$ddw_carrier->cutofftime_minutes,
			),
			'id_carrier='.(int)$ddw_carrier->id_carrier
		);
		return true;
	}

	public static function saveWeekdays(TDDWCarrier $ddw_carrier)
	{
		self::DDWCarrierCreateIfNotExists($ddw_carrier->id_carrier);
		Db::getInstance()->update('ddw', array(
				'weekdays' => pSQL(implode(',', $ddw_carrier->weekdays))
			),
			'id_carrier='.(int)$ddw_carrier->id_carrier
		);
	}

	public static function saveBlockedDate(TDDWBlockedDate $date_blocked)
	{
		if ($date_blocked->id_blockeddate == -1)
		{
			Db::getInstance()->insert('ddw_blocked_dates', array(
				'id_carrier' => (int)$date_blocked->id_carrier,
				'id_shop' => (int)$date_blocked->id_shop,
				'recurring' => (int)$date_blocked->recurring,
				'start_date' => pSQL($date_blocked->start_date),
				'end_date' => pSQL($date_blocked->end_date)
			));
		}
		else
		{
			Db::getInstance()->update('ddw_blocked_dates', array(
					'id_carrier' => (int)$date_blocked->id_carrier,
					'id_shop' => (int)$date_blocked->id_shop,
					'recurring' => (int)$date_blocked->recurring,
					'start_date' => pSQL($date_blocked->start_date),
					'end_date' => pSQL($date_blocked->end_date)
				),
				'id_blockeddate='.(int)$date_blocked->id_blockeddate
			);
		}
	}

	public static function deleteBlockedDate($id_blockeddate)
	{
		$sql = 'DELETE FROM '._DB_PREFIX_.'ddw_blocked_dates WHERE id_blockeddate='.(int)$id_blockeddate;
		DB::getInstance()->execute($sql);
	}


	/**
	 * @return TDDWBlockedDate
	 */
	public static function getBlockedDate($id_blockeddate, $return_raw = false)
	{
		$sql = 'SELECT
		            id_blockeddate,
		            id_carrier,
		            id_shop,
		            recurring,
		            start_date,
		            end_date
		        FROM '._DB_PREFIX_.'ddw_blocked_dates
		        WHERE id_blockeddate = '.(int)$id_blockeddate;
		$result = DB::getInstance()->getRow($sql);

		if ($result)
		{
			$blocked_date = new TDDWBlockedDate();
			$blocked_date->id_blockeddate = $result['id_blockeddate'];
			$blocked_date->id_carrier = $result['id_carrier'];
			$blocked_date->id_shop = $result['id_shop'];
			$blocked_date->recurring = $result['recurring'];
			$blocked_date->start_date = $result['start_date'];
			$blocked_date->end_date = $result['end_date'];
			return $blocked_date;
		}
		else
			return false;
	}

	/**
	 * @param integer $id_carrier
	 * @return array of TDDWBlockedDate
	 */
	public static function getBlockedDates($id_carrier, $return_raw, $id_shop = 1)
	{
		$blocked_dates = array();
		$sql = 'SELECT
					id_blockeddate,
		            id_carrier,
		            id_shop,
		            recurring,
		            start_date,
		            end_date
				FROM '._DB_PREFIX_.'ddw_blocked_dates WHERE id_carrier = '.(int)$id_carrier.'
				AND id_shop = '.(int)Context::getContext()->shop->id;
		$result = Db::getInstance()->executeS($sql);

		if ($return_raw) return $result;

		if ($result)
		{
			foreach ($result as $row)
			{
				$date_blocked = new TDDWBlockedDate();
				$date_blocked->id_blockeddate = $row['id_blockeddate'];
				$date_blocked->id_carrier = $row['id_carrier'];
				$date_blocked->id_shop = $row['id_shop'];
				$date_blocked->recurring = $row['recurring'];
				$date_blocked->start_date = $row['start_date'];
				$date_blocked->end_date = $row['end_date'];
				$blocked_dates[] = $date_blocked;
			}
		}
		return $blocked_dates;
	}

	public static function saveToCart($ddw_order_date, $ddw_order_time, $id_cart)
	{
		Db::getInstance()->update('cart', array(
				'ddw_order_date' => pSQL($ddw_order_date),
				'ddw_order_time' => pSQL($ddw_order_time)
			),
			'id_cart='.(int)$id_cart
		);
	}

	public static function getCartDDWDateTime($id_cart)
	{
		$cart_ddw = array();
		$cart_ddw['ddw_order_date'] = '';
		$cart_ddw['ddw_order_time'] = '';
		/* get the order date and time we stored in cart session */
		$sql = 'SELECT
		            ddw_order_date,
		            ddw_order_time
				FROM '._DB_PREFIX_.'cart
				WHERE id_cart = '.(int)$id_cart;
		$result = Db::getInstance()->getRow($sql);
		if (isset($result) && is_array($result))
		{
			$cart_ddw['ddw_order_date'] = $result['ddw_order_date'];
			$cart_ddw['ddw_order_time'] = $result['ddw_order_time'];
		}
		return $cart_ddw;
	}

	public static function getOrderDDWDateTime($id_order)
	{
		$order_ddw = array();
		$order_ddw['ddw_order_date'] = '';
		$order_ddw['ddw_order_time'] = '';
		/* get the order date and time we stored in cart session */
		$sql = 'SELECT
		            ddw_order_date,
		            ddw_order_time
				FROM '._DB_PREFIX_.'orders
				WHERE id_order = '.(int)$id_order;
		$result = Db::getInstance()->getRow($sql);
		if (isset($result) && is_array($result))
		{
			$order_ddw['ddw_order_date'] = $result['ddw_order_date'];
			$order_ddw['ddw_order_time'] = $result['ddw_order_time'];
		}
		return $order_ddw;
	}


	public static function saveToOrder($cart)
	{
		$ddw_order_date = '';
		$ddw_order_time = '';
		/* get the order date and time we stored in cart session */
		$sql = 'SELECT
		            ddw_order_date,
		            ddw_order_time
				FROM '._DB_PREFIX_.'cart
				WHERE secure_key = "'.$cart->secure_key.'"
				AND id_cart = '.(int)$cart->id;
		$result = Db::getInstance()->getRow($sql);

		if (isset($result) && is_array($result))
		{
			$ddw_order_date = $result['ddw_order_date'];
			$ddw_order_time = $result['ddw_order_time'];
		}
		else return false;

		$sql = 'SELECT * FROM '._DB_PREFIX_.'orders
				WHERE secure_key = "'.$cart->secure_key.'"
				AND id_cart = '.(int)$cart->id;

		Db::getInstance()->execute($sql);
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		if (count($result) > 0)
		{
			$id_order = $result[0]['id_order'];
			Db::getInstance()->update('orders', array(
					'ddw_order_date' => pSQL($ddw_order_date),
					'ddw_order_time' => pSQL($ddw_order_time)
				),
				'id_order='.(int)$id_order.' AND id_cart='.(int)$cart->id
			);
		}
	}

	public static function saveToOrderDirect($id_order, $ddw_order_date, $ddw_order_time)
	{
		DB::getInstance()->update(
				'orders',
				array(
					'ddw_order_date' => pSQL($ddw_order_date),
					'ddw_order_time' => pSQL($ddw_order_time)
				),
				'id_order='.(int)$id_order
		);
		return true;
	}

	public static function saveTimeSlot(TDDWTimeSlot $time_slot)
	{
		if ($time_slot->id_timeslot == -1)
		{
			Db::getInstance()->insert('ddw_timeslots', array(
				'id_carrier' => (int)$time_slot->id_carrier,
				'id_shop' => (int)$time_slot->id_shop,
				'position' => (int)$time_slot->position,
			));			
			$insert_id = Db::getInstance()->Insert_ID();
		}
		else
		{
			Db::getInstance()->update('ddw_timeslots', array(
					'position' => (int)$time_slot->position
				),
				'id_timeslot='.(int)$time_slot->id_timeslot
			);
			$insert_id = $time_slot->id_timeslot;
		}

		$sql = 'DELETE FROM '._DB_PREFIX_.'ddw_timeslots_lang WHERE id_timeslot='.(int)$insert_id;
		Db::getInstance()->execute($sql);

		foreach ($time_slot->time_slots as $key => $time_slot_text)
		{
			Db::getInstance()->insert('ddw_timeslots_lang', array(
				'id_timeslot' => (int)$insert_id,
				'id_lang' => (int)$key,
				'time_slot' => pSQL($time_slot_text),
			));
		}
		return true;
	}

	/**
	 * @var TDDWTimeSlot[]
	 */
	public static function getTimeSlots($id_carrier, $id_lang, $id_shop = 1, $return_raw)
	{
		$timeslots_collection = array();
		$id_lang_clause = '';
		if ($id_lang > -1) $id_lang_clause = 'AND tsl.id_lang = '.(int)$id_lang;

		$sql = 'SELECT
		            ts.id_timeslot,
		            ts.id_carrier,
		            ts.id_shop,
		            ts.position,
		            tsl.time_slot
		        FROM '._DB_PREFIX_.'ddw_timeslots AS ts
		        INNER JOIN '._DB_PREFIX_.'ddw_timeslots_lang tsl ON ts.id_timeslot = tsl.id_timeslot '.$id_lang_clause.'
		        WHERE ts.id_shop = '.(int)$id_shop.'
		        AND ts.id_carrier = '.(int)$id_carrier.'
		        ORDER BY ts.`position` ASC
				';

		$result = Db::getInstance()->executeS($sql);

		if ($return_raw) return $result;

		if ($result && is_array($result))
		{
			foreach ($result as $row)
			{
				$timeslot = new TDDWTimeSlot();
				$timeslot->id_timeslot = $row['id_timeslot'];
				$timeslot->id_carrier = $row['id_carrier'];
				$timeslot->id_shop = $row['id_shop'];
				$timeslot->position = $row['position'];

				$sql = 'SELECT
				            id_lang,
							time_slot
				        FROM '._DB_PREFIX_.'ddw_timeslots_lang
				        WHERE id_timeslot = '.(int)$timeslot->id_timeslot;
				$result2 = Db::getInstance()->executeS($sql);
				if ($result2 && is_array($result))
				{
					foreach ($result2 as $row2)
						$timeslot->time_slots[$row2['id_lang']] = $row['time_slot'];
				}
				$timeslots_collection[] = $timeslot;
			}
		}
		return $timeslots_collection;
	}

	/**
	 * @var TDDWTimeSlot
	 */
	public static function getTimeSlot($id_timeslot, $id_lang)
	{
		$sql = 'SELECT
		            ts.id_timeslot,
		            ts.id_carrier,
		            ts.id_shop,
		            ts.position,
		            tsl.time_slot
		        FROM '._DB_PREFIX_.'ddw_timeslots AS ts
		        INNER JOIN '._DB_PREFIX_.'ddw_timeslots_lang tsl ON ts.id_timeslot = tsl.id_timeslot AND tsl.id_lang = '.(int)$id_lang.'
		        WHERE ts.id_timeslot = '.(int)$id_timeslot;
		$result = Db::getInstance()->executeS($sql);

		if ($result && is_array($result))
		{
			foreach ($result as $row)
			{
				$timeslot = new TDDWTimeSlot();
				$timeslot->id_timeslot = $row['id_timeslot'];
				$timeslot->id_carrier = $row['id_carrier'];
				$timeslot->id_shop = $row['id_shop'];
				$timeslot->position = $row['position'];

				$sql = 'SELECT
				            id_lang,
							time_slot
				        FROM '._DB_PREFIX_.'ddw_timeslots_lang
				        WHERE id_timeslot = '.(int)$timeslot->id_timeslot;
				$result2 = Db::getInstance()->executeS($sql);
				if ($result2 && is_array($result))
				{
					foreach ($result2 as $row2)
						$timeslot->time_slots[$row2['id_lang']] = $row['time_slot'];
				}
			}
		}
		return $timeslot;
	}

	public static function deleteTimeSlot($id_timeslot)
	{
		$sql = 'DELETE FROM '._DB_PREFIX_.'ddw_timeslots_lang WHERE id_timeslot ='.(int)$id_timeslot;
		Db::getInstance()->execute($sql);

		$sql = 'DELETE FROM '._DB_PREFIX_.'ddw_timeslots WHERE id_timeslot ='.(int)$id_timeslot;
		Db::getInstance()->execute($sql);
		return true;
	}
	
	public static function updateCarrierID($old_id_carrier, $new_id_carrier)
	{
		DB::getInstance()->update('ddw', array('id_carrier' => (int)$new_id_carrier), 'id_carrier='.(int)$old_id_carrier);
		DB::getInstance()->update('ddw_blocked_dates', array('id_carrier' => (int)$new_id_carrier), 'id_carrier='.(int)$old_id_carrier);
		DB::getInstance()->update('ddw_timeslots', array('id_carrier' => (int)$new_id_carrier), 'id_carrier='.(int)$old_id_carrier);
	}
	

}
