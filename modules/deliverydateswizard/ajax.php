<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include_once(_PS_MODULE_DIR_.'/deliverydateswizard/lib/bootstrap.php');


$controller_front = new DDWControllerFront();

switch (Tools::getValue('action'))
{
	case 'get_blocked_dates' :
		print Tools::jsonEncode($controller_front->get_blocked_dates());
		break;
	case 'get_last_ddw_cart' :
		print Tools::jsonEncode($controller_front->getLastDDWCart());
		break;
	case 'update_ddw_cart' :
		$controller_front->update_ddw_cart();
		break;
	case 'update_ddw_order_detail':
		DDWModel::saveToOrderDirect(Tools::getValue('id_order'), Tools::getValue('ddw_order_date'), Tools::getValue('ddw_order_time'));
		break;		
}
?>