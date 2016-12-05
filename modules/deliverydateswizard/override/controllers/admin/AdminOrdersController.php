<?php
class AdminOrdersController extends AdminOrdersControllerCore
{
	public function __construct()
	{
		parent::__construct();
		$add_select = '(SELECT o.ddw_order_date FROM `'._DB_PREFIX_.'orders` o WHERE o.id_order = a.id_order) as ddw_date,
					   (SELECT o.ddw_order_time FROM `'._DB_PREFIX_.'orders` o WHERE o.id_order = a.id_order) as ddw_time,';

		if (_PS_VERSION_ < 1.6)
			$this->_select = str_replace('os.`color`,', 'os.`color`,'.$add_select, $this->_select);
		else
			$this->_select = str_replace('country_lang.name as cname,', 'country_lang.name as cname,'.$add_select, $this->_select);

		$this->fields_list['ddw_date'] = array(
			'title' => $this->l('Delivery Date'),
			'align' => 'text-right',
			'type' => 'datetime',
			'filter_key' => 'ddw_date',
			'havingFilter' => true,
			'callback' => 'printDDWDate',
			'class' => 'fixed-width-xl',
			'width' => '100'
		);
	}

	public function printDDWDate($id_order, $tr)
	{
		//$return = date('Y-m-d', strtotime($tr['ddw_date'])).' - ';
		$return = Tools::displayDate($tr['ddw_date']).' - ';
		if ($tr['ddw_time'] != '') $return .= ' ('.$tr['ddw_time'].')';
		return $return;
	}

}